<?php

use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Pacing\WeeklyRollover;

it('generates a fresh target from the frontier when everything was mastered', function () {
    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'weekly_module_cap_override' => 3,
    ]);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => now()->subWeeks(2),
        'exam_date' => now()->addWeeks(28),
    ]);

    // Last week's target: 3 modules at pacing_week 1, all mastered.
    $mastered = collect(range(1, 3))->map(fn (int $i) => SyllabusModule::create([
        'subject' => 'Math',
        'topic' => "Number: Mastered Topic {$i}",
        'sea_section' => 'Number',
        'sequence_order' => $i,
        'pacing_week' => 1,
    ]));

    $lastWeekStart = now()->subWeek()->startOfWeek()->toDateString();

    foreach ($mastered as $module) {
        StudentProgress::create([
            'student_id' => $student->id,
            'module_id' => $module->id,
            'status' => 'mastered',
        ]);

        WeeklyTarget::create([
            'student_id' => $student->id,
            'module_id' => $module->id,
            'week_start_date' => $lastWeekStart,
            'is_completed' => true,
        ]);
    }

    // Frontier: 5 not-yet-mastered modules at pacing_week 2.
    $frontier = collect(range(1, 5))->map(fn (int $i) => SyllabusModule::create([
        'subject' => 'Math',
        'topic' => "Number: Frontier Topic {$i}",
        'sea_section' => 'Number',
        'sequence_order' => 10 + $i,
        'pacing_week' => 2,
    ]));

    $targets = app(WeeklyRollover::class)->runFor($student);

    // Cap respected.
    expect($targets)->toHaveCount(3);

    $newModuleIds = $targets->pluck('module_id')->sort()->values();
    $frontierIds = $frontier->pluck('id')->sort()->values();
    $masteredIds = $mastered->pluck('id');

    // New target is drawn only from the frontier...
    expect($newModuleIds->diff($frontierIds))->toBeEmpty();

    // ...and nothing carried forward from last week's (mastered) target.
    expect($newModuleIds->intersect($masteredIds))->toBeEmpty();

    $thisWeekStart = now()->startOfWeek()->toDateString();

    expect(WeeklyTarget::where('student_id', $student->id)
        ->where('week_start_date', $thisWeekStart)
        ->count())->toBe(3);
})->group('scenario:WT-01');
