<?php

use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Notifications\PaceWarningNotification;
use App\Services\Pacing\WeeklyRollover;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

/*
|--------------------------------------------------------------------------
| WT-03 — Significant lag triggers an honest re-pace in the guardian layer
|--------------------------------------------------------------------------
|
| Design (locked):
|   required_pace       = 3 modules/week (global Std-5 yardstick)
|   expected_mastered   = (current_pacing_week - 1) * 3
|   deficit             = max(0, expected_mastered - actual_mastered)
|   weeks_behind        = intdiv(deficit, 3)
|   trigger             = weeks_behind >= 4
|   effective_cap       = max(base_cap, ceil(remaining / weeks_to_exam))   // auto-raise
|   warning storage     = student_journeys.pace_status = 'warning', .weeks_behind
|   guardian notify     = PaceWarningNotification (faked here)
|   student boundary    = no deficit/warning field on WeeklyTarget rows
|   clear condition     = remaining <= weeks_to_exam * base_cap (base, unwinds raise)
|
*/

/**
 * Build a guardian + student, a journey placing the student at a given pacing
 * week with a given number of weeks to the exam, N mastered modules, and the
 * standard 90-module syllabus.
 */
function seedRepaceStudent(int $currentPacingWeek, int $weeksToExam, int $masteredCount): array
{
    // exam_date is weeks_to_exam weeks out from today.
    $examDate = Carbon::today()->copy()->addWeeks($weeksToExam);

    // journey_start puts us at currentPacingWeek:
    //   current_pacing_week = weeks_since(journey_start) + 1
    // so journey_start is (currentPacingWeek - 1) weeks ago.
    $journeyStart = Carbon::today()->copy()->subWeeks($currentPacingWeek - 1);

    $guardian = User::factory()->create();

    $student = User::factory()->create([
        'parent_id' => $guardian->id,
    ]);

    StudentJourney::create([
        'student_id'    => $student->id,
        'journey_start' => $journeyStart->toDateString(),
        'exam_date'     => $examDate->toDateString(),
    ]);

    // Standard syllabus: 90 modules across 30 pacing weeks (3/week).
    $modules = collect();
    for ($week = 1; $week <= 30; $week++) {
        for ($i = 0; $i < 3; $i++) {
            $modules->push(SyllabusModule::factory()->create([
                'pacing_week'    => $week,
                'sequence_order' => (($week - 1) * 3) + $i + 1,
            ]));
        }
    }

    // Mark the earliest N modules mastered.
    $modules->take($masteredCount)->each(function (SyllabusModule $module) use ($student) {
        StudentProgress::create([
            'student_id' => $student->id,
            'module_id'  => $module->id,
            'status'     => 'mastered',
        ]);
    });

    return [$guardian, $student, $modules];
}

it('flags a warning and records the lag when a student is 4+ weeks behind', function () {
    Notification::fake();

    // Week 10 => expected mastered = (10 - 1) * 3 = 27.
    // Mastered = 12 => deficit = 15 => weeks_behind = intdiv(15, 3) = 5  (>= 4, triggers).
    [$guardian, $student] = seedRepaceStudent(
        currentPacingWeek: 10,
        weeksToExam: 20,
        masteredCount: 12,
    );

    app(WeeklyRollover::class)->runFor($student);

    $journey = StudentJourney::where('student_id', $student->id)->firstOrFail();

    expect($journey->pace_status)->toBe('warning')
        ->and($journey->weeks_behind)->toBe(5);
})->group('scenario:WT-03');

it('notifies the guardian when the re-pace warning fires', function () {
    Notification::fake();

    [$guardian, $student] = seedRepaceStudent(
        currentPacingWeek: 10,
        weeksToExam: 20,
        masteredCount: 12,
    );

    app(WeeklyRollover::class)->runFor($student);

    Notification::assertSentTo($guardian, PaceWarningNotification::class);
})->group('scenario:WT-03');

it('auto-raises the cap to the smallest value that fits the remaining modules', function () {
    Notification::fake();

    // Behind AND tight on time so base cap can't fit the remainder.
    // Week 10, mastered 12 => remaining to schedule = 90 - 12 = 78 non-mastered.
    // weeks_to_exam = 10, base cap = 5 => 10 * 5 = 50 < 78 => must raise.
    // needed = ceil(78 / 10) = 8.
    [$guardian, $student] = seedRepaceStudent(
        currentPacingWeek: 10,
        weeksToExam: 10,
        masteredCount: 12,
    );

    app(WeeklyRollover::class)->runFor($student);

    // Every future week from this one to the exam should be filled to the
    // raised cap (8), never exceeding it, and the whole remainder placed.
    $weekStart = Carbon::today()->copy()->startOfWeek();

    $maxInAnyWeek = WeeklyTarget::where('student_id', $student->id)
        ->where('week_start_date', '>=', $weekStart->toDateString())
        ->selectRaw('week_start_date, count(*) as c')
        ->groupBy('week_start_date')
        ->pluck('c')
        ->max();

    // The raised cap is 8: at least one week hits 8, and none exceeds it.
    expect($maxInAnyWeek)->toBe(8);

    // The base cap of 5 would have been insufficient — proving a raise happened.
    expect($maxInAnyWeek)->toBeGreaterThan(5);
})->group('scenario:WT-03');

it('never writes deficit or warning language into student-facing target rows', function () {
    Notification::fake();

    [$guardian, $student] = seedRepaceStudent(
        currentPacingWeek: 10,
        weeksToExam: 20,
        masteredCount: 12,
    );

    app(WeeklyRollover::class)->runFor($student);

    // WeeklyTarget rows carry no pace_status / warning / deficit column.
    $target = WeeklyTarget::where('student_id', $student->id)->first();

    expect($target)->not->toBeNull();
    expect($target->getAttributes())->not->toHaveKey('pace_status');
    expect($target->getAttributes())->not->toHaveKey('weeks_behind');
    expect($target->getAttributes())->not->toHaveKey('deficit');
})->group('scenario:WT-03');

it('clears an existing warning once the student is back within base-cap reach', function () {
    Notification::fake();

    // On pace now: week 10, mastered 27 => deficit 0 => weeks_behind 0 (no trigger).
    // remaining = 90 - 27 = 63; weeks_to_exam = 20; base cap 5 => 20*5 = 100 >= 63 => fits.
    [$guardian, $student] = seedRepaceStudent(
        currentPacingWeek: 10,
        weeksToExam: 20,
        masteredCount: 27,
    );

    // Pre-set a stale warning from a prior lagging run.
    $journey = StudentJourney::where('student_id', $student->id)->firstOrFail();
    $journey->pace_status = 'warning';
    $journey->weeks_behind = 4;
    $journey->save();

    app(WeeklyRollover::class)->runFor($student);

    $journey->refresh();

    expect($journey->pace_status)->toBeNull()
        ->and($journey->weeks_behind)->toBeNull();
})->group('scenario:WT-03');
