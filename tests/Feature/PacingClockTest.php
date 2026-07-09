<?php

use App\Models\User;
use App\Models\StudentJourney;
use App\Services\Pacing\PacingClock;

/**
 * @scenario:WT-00 — the pacing clock derives the current week and weeks to exam.
 *
 * Two clocks, both per student, both anchored at onboarding:
 *   current_pacing_week = weeks_since(journey_start) + 1   (child-facing)
 *   weeks_to_exam       = whole weeks between today and exam_date (dashboard)
 *
 * required_pace (3 modules/week) is NOT exercised here — that is WT-03.
 */
it('derives current pacing week and weeks to exam from the journey dates', function () {
    $student = User::create([
        'name' => 'Clock Kid',
        'email' => 'clockkid@students.formynieces.com',
        'password' => bcrypt('secret-secret'),
    ]);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => now()->subWeeks(4)->toDateString(),
        'exam_date' => now()->addWeeks(26)->toDateString(),
    ]);

    $clock = app(PacingClock::class);

    expect($clock->currentPacingWeek($student))->toBe(5);
    expect($clock->weeksToExam($student))->toBe(26);
})->group('scenario:WT-00');

it('keeps a same-day starter on pacing week one', function () {
    $student = User::create([
        'name' => 'New Kid',
        'email' => 'newkid@students.formynieces.com',
        'password' => bcrypt('secret-secret'),
    ]);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => now()->toDateString(),
        'exam_date' => now()->addWeeks(30)->toDateString(),
    ]);

    $clock = app(PacingClock::class);

    expect($clock->currentPacingWeek($student))->toBe(1);
})->group('scenario:WT-00');
