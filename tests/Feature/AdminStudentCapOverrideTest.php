<?php

use App\Models\Setting;
use App\Models\StudentJourney;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Pacing\WeeklyRollover;

it('caps a student weekly target at her per-student override, below the global cap', function () {
    // Global cap is generous...
    Setting::put('weekly_module_cap', 8);

    // ...but this student has a lower per-student override.
    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-ac03@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'weekly_module_cap_override' => 3,
    ]);

    // On-pace fresh journey so WT-03 re-pace never raises the effective cap.
    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => now()->toDateString(),
        'exam_date' => now()->addWeeks(30)->toDateString(),
    ]);

    // Frontier: 10 non-mastered modules — more than either cap could place.
    collect(range(1, 10))->each(fn (int $i) => SyllabusModule::create([
        'subject' => 'Math',
        'topic' => "Number: Frontier Topic {$i}",
        'sea_section' => 'Number',
        'sequence_order' => $i,
        'pacing_week' => 1,
    ]));

    app(WeeklyRollover::class)->runFor($student);

    $thisWeekStart = now()->startOfWeek()->toDateString();

    // The per-student override (3) binds, not the global cap (8).
    expect(WeeklyTarget::where('student_id', $student->id)
        ->where('week_start_date', $thisWeekStart)
        ->count())->toBe(3);
})->group('scenario:AC-03');
