<?php

use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Pacing\WeeklyRollover;
use Illuminate\Support\Carbon;

it('carries unmastered modules forward first and fills future weeks to cap', function () {
    $student = User::create([
        'name' => 'Kesh',
        'email' => 'kesh@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'weekly_module_cap_override' => 3,
    ]);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => now()->subWeeks(3),
        'exam_date' => now()->addWeeks(20),
    ]);

    $now = Carbon::parse('2026-07-08'); // Wednesday
    $thisWeek = $now->copy()->startOfWeek()->toDateString();          // 2026-07-06
    $lastWeek = $now->copy()->subWeek()->startOfWeek()->toDateString();  // 2026-06-29
    $nextWeek = $now->copy()->addWeek()->startOfWeek()->toDateString();  // 2026-07-13

    // 5 modules targeted last week; 1 mastered, 4 left unmastered (ids 2..5 carry).
    $lastWeekModules = collect(range(1, 5))->map(fn (int $i) => SyllabusModule::create([
        'subject' => 'Math',
        'topic' => "Number: Carried {$i}",
        'sea_section' => 'Number',
        'sequence_order' => $i,
        'pacing_week' => 1,
    ]));

    foreach ($lastWeekModules as $module) {
        WeeklyTarget::create([
            'student_id' => $student->id,
            'module_id' => $module->id,
            'week_start_date' => $lastWeek,
            'is_completed' => false,
        ]);
    }

    StudentProgress::create([
        'student_id' => $student->id,
        'module_id' => $lastWeekModules[0]->id,
        'status' => 'mastered',
    ]);

    // Frontier modules, ordered by pacing_week then sequence_order.
    $frontier = collect(range(1, 4))->map(fn (int $i) => SyllabusModule::create([
        'subject' => 'Math',
        'topic' => "Number: Frontier {$i}",
        'sea_section' => 'Number',
        'sequence_order' => 100 + $i,
        'pacing_week' => 2,
    ]));

    app(WeeklyRollover::class)->runFor($student, $now);

    $carriedIds = $lastWeekModules->slice(1)->pluck('id')->values(); // 4 carried
    $frontierIds = $frontier->pluck('id')->values();

    $thisWeekTargets = WeeklyTarget::where('student_id', $student->id)
        ->where('week_start_date', $thisWeek)->pluck('module_id');
    $nextWeekTargets = WeeklyTarget::where('student_id', $student->id)
        ->where('week_start_date', $nextWeek)->pluck('module_id');

    // This week: full at cap, all carried, no frontier (carry displaces frontier).
    expect($thisWeekTargets)->toHaveCount(3);
    expect($thisWeekTargets->every(fn ($id) => $carriedIds->contains($id)))->toBeTrue();
    expect($thisWeekTargets->intersect($frontierIds))->toBeEmpty();

    // Next week: filled to cap (X) — the 1 overflow carried module, then frontier fill.
    expect($nextWeekTargets)->toHaveCount(3);

    // The overflow carried module (the one not placed this week) is present next week.
    $overflowId = $carriedIds->reject(fn ($id) => $thisWeekTargets->contains($id))->first();
    expect($nextWeekTargets->contains($overflowId))->toBeTrue();

    // Remaining next-week slots are frontier, in priority order.
    expect($nextWeekTargets->intersect($frontierIds))->toHaveCount(2);

    // Cap never exceeded in any week.
    WeeklyTarget::where('student_id', $student->id)
        ->where('week_start_date', '>=', $thisWeek)
        ->get()->groupBy('week_start_date')
        ->each(fn ($rows) => expect($rows->count())->toBeLessThanOrEqual(3));

    // Mastered module never reappears.
    $allNew = $thisWeekTargets->merge($nextWeekTargets);
    expect($allNew->contains($lastWeekModules[0]->id))->toBeFalse();
})->group('scenario:WT-02');
