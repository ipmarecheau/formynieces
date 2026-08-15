<?php

use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Motivation\DailyPlanComposer;
use App\Services\Motivation\StreakEconomyService;
use App\Models\StudentStreak;
use Illuminate\Support\Carbon;

function dpcStudent(): User
{
    return User::factory()->create(['role' => 'student']);
}

function dpc(): DailyPlanComposer
{
    return app(DailyPlanComposer::class);
}

it('lists vocabulary, reading and map on a plain weekday, no writing', function () {
    $student = dpcStudent();
    $tuesday = Carbon::parse('2026-08-18');

    $plan = dpc()->forDay($student->id, $tuesday);

    expect($plan->is_writing_day)->toBeFalse()
        ->and(array_keys($plan->duties))->toEqualCanonicalizing(['vocabulary', 'reading', 'map'])
        ->and($plan->duties)->not->toHaveKey('writing');
})->group('scenario:CO-02');

it('adds a writing duty on Monday, Wednesday and Friday only', function () {
    $student = dpcStudent();

    $wednesday = dpc()->forDay($student->id, Carbon::parse('2026-08-19'));
    $friday = dpc()->forDay($student->id, Carbon::parse('2026-08-21'));
    $tuesday = dpc()->forDay($student->id, Carbon::parse('2026-08-18'));

    expect($wednesday->is_writing_day)->toBeTrue()
        ->and($wednesday->duties)->toHaveKey('writing')
        ->and($friday->duties)->toHaveKey('writing')
        ->and($tuesday->duties)->not->toHaveKey('writing');
})->group('scenario:CO-03');

it('stands the weekend down to rest when she is on pace', function () {
    $student = dpcStudent();
    $saturday = Carbon::parse('2026-08-22'); // no weekly target => on pace

    $plan = dpc()->forDay($student->id, $saturday);

    expect($plan->is_writing_day)->toBeFalse()
        ->and($plan->duties)->toBe([]); // shore leave — nothing required
})->group('scenario:CO-08');

it('offers bounded catch-up on the weekend when she has fallen behind, never writing', function () {
    $student = dpcStudent();
    $module = SyllabusModule::factory()->create();

    // An unmet target for the current week (Monday 2026-08-17) => behind.
    WeeklyTarget::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'week_start_date' => '2026-08-17',
        'is_completed' => false,
    ]);

    $saturday = Carbon::parse('2026-08-22');
    $plan = dpc()->forDay($student->id, $saturday);

    expect(array_keys($plan->duties))->toEqualCanonicalizing(['vocabulary', 'reading', 'map'])
        ->and($plan->duties)->not->toHaveKey('writing');
})->group('scenario:CO-09');

it('completing every duty closes the day and extends the Voyage streak', function () {
    $student = dpcStudent();
    $wednesday = Carbon::parse('2026-08-19');

    dpc()->forDay($student->id, $wednesday);
    foreach (['vocabulary', 'reading', 'map', 'writing'] as $duty) {
        dpc()->markDuty($student->id, $duty, $wednesday);
    }

    $completed = app(StreakEconomyService::class)
        ->completeDailyMinimumIfMet($student->id, $wednesday);

    expect($completed)->toBeTrue()
        ->and(StudentStreak::where('student_id', $student->id)->where('type', 'voyage')->value('count'))->toBe(1);
})->group('scenario:CO-07');
