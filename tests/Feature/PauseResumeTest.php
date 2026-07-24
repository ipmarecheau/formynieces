<?php

use App\Models\StudentJourney;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Motivation\StreakService;
use App\Services\Pacing\PauseService;
use App\Services\Pacing\WeeklyRollover;

/**
 * Pause / resume — a guardian can pause a student. While paused, no weekly
 * targets are generated and streaks are frozen (WT-04). On resume, pacing is
 * shifted forward so no weeks are "missed" (WT-05), and a frozen streak
 * continues where it left off on the next activity (ML-03).
 */
function makePausableStudent(?Carbon\Carbon $journeyStart = null): User
{
    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'pause-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => $journeyStart ?? today()->subWeeks(4),
        'exam_date' => today()->addWeeks(26),
    ]);

    return $student;
}

it('freezes a streak across a pause so it resumes where it left off', function () {
    $student = makePausableStudent();

    // A 6-day practice streak whose last activity was the day she was paused.
    $pausedAt = now()->subDays(5)->startOfDay();
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'practice',
        'count' => 6,
        'last_activity_date' => $pausedAt,
    ]);
    $student->forceFill(['paused_at' => $pausedAt])->save();

    app(PauseService::class)->resume($student);

    // She completes a learning activity on the day she is resumed.
    app(StreakService::class)->recordActivity($student->id, 'practice');

    $streak = StudentStreak::where('student_id', $student->id)->where('type', 'practice')->first();
    expect($streak->count)->toBe(7);
})->group('scenario:ML-03');

it('generates no weekly targets and does not reset the streak while paused', function () {
    $student = makePausableStudent(today()->startOfWeek());

    // Not-started modules that would otherwise be targeted this week.
    foreach (range(1, 3) as $i) {
        SyllabusModule::create(['subject' => 'Math', 'topic' => "M{$i}: x", 'sea_section' => 'Section I', 'sequence_order' => $i, 'pacing_week' => 1]);
    }

    // An on-pace streak that must not be reset by a rollover while paused.
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'pace_weeks',
        'count' => 3,
        'last_activity_date' => today()->subWeek()->startOfWeek(),
    ]);

    app(PauseService::class)->pause($student);
    app(WeeklyRollover::class)->runFor($student->refresh());

    $currentWeek = now()->startOfWeek()->toDateString();
    expect(WeeklyTarget::where('student_id', $student->id)->where('week_start_date', $currentWeek)->exists())->toBeFalse()
        ->and(StudentStreak::where('student_id', $student->id)->where('type', 'pace_weeks')->first()->count)->toBe(3);
})->group('scenario:WT-04');

it('re-paces from the resume date by shifting the journey forward by the pause duration', function () {
    $originalStart = today()->subWeeks(6);
    $student = makePausableStudent($originalStart);

    // Paused two weeks ago, resumed today.
    $student->forceFill(['paused_at' => now()->subWeeks(2)->startOfDay()])->save();

    app(PauseService::class)->resume($student->refresh());

    $journey = StudentJourney::where('student_id', $student->id)->first();

    expect($journey->journey_start->toDateString())->toBe($originalStart->copy()->addDays(14)->toDateString())
        ->and($student->refresh()->paused_at)->toBeNull();
})->group('scenario:WT-05');

it('lets a guardian pause and resume her own student from the Parent Portal', function () {
    $guardian = User::create([
        'name' => 'Guardian', 'email' => 'pause-g-'.uniqid().'@formynieces.com',
        'password' => bcrypt('secret'), 'role' => 'guardian',
    ]);
    $guardian->forceFill(['email_verified_at' => now()])->save();

    $student = makePausableStudent();
    $student->forceFill(['parent_id' => $guardian->id])->save();

    // The portal offers a Pause control for an active student.
    $this->actingAs($guardian)->get(route('dashboard'))->assertOk()->assertSee('Pause');

    $this->actingAs($guardian)->post(route('guardian.pause', $student))->assertRedirect();
    expect($student->refresh()->isPaused())->toBeTrue();

    $this->actingAs($guardian)->post(route('guardian.resume', $student))->assertRedirect();
    expect($student->refresh()->isPaused())->toBeFalse();
})->group('scenario:WT-04');

it('forbids pausing a child that is not the guardian’s own', function () {
    $student = makePausableStudent();

    $outsider = User::create([
        'name' => 'Outsider', 'email' => 'pause-out-'.uniqid().'@formynieces.com',
        'password' => bcrypt('secret'), 'role' => 'guardian',
    ]);
    $outsider->forceFill(['email_verified_at' => now()])->save();

    $this->actingAs($outsider)->post(route('guardian.pause', $student))->assertForbidden();
    expect($student->refresh()->isPaused())->toBeFalse();
})->group('scenario:WT-04');
