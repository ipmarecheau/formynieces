<?php

use App\Models\DailyPlan;
use App\Models\StudentStreak;
use App\Models\User;
use App\Services\Motivation\StreakEconomyService;
use Illuminate\Support\Carbon;

function seEconomyStudent(): User
{
    return User::factory()->create(['role' => 'student']);
}

function seEconomy(): StreakEconomyService
{
    return app(StreakEconomyService::class);
}

it('grants a reward earned by getting ahead of pace', function () {
    $student = seEconomyStudent();

    seEconomy()->grantReward($student->id, 'shore_leave', 'ahead');

    expect(seEconomy()->balance($student->id, 'shore_leave'))->toBe(1);
})->group('scenario:SE-13');

it('grants a reward at a milestone', function () {
    $student = seEconomyStudent();

    seEconomy()->grantReward($student->id, 'lifebuoy', 'milestone');
    seEconomy()->grantReward($student->id, 'lifebuoy', 'milestone');

    expect(seEconomy()->balance($student->id, 'lifebuoy'))->toBe(2);
})->group('scenario:SE-14');

it('lets a guardian-granted reward appear in the Captain\'s Locker', function () {
    $student = seEconomyStudent();

    $reward = seEconomy()->grantReward($student->id, 'anchor', 'guardian');

    expect($reward->source)->toBe('guardian')
        ->and(seEconomy()->balance($student->id, 'anchor'))->toBe(1);
})->group('scenario:SE-15');

it('spends a reward from the Locker and refuses when none are held', function () {
    $student = seEconomyStudent();
    seEconomy()->grantReward($student->id, 'tailwind', 'xp');

    expect(seEconomy()->spendReward($student->id, 'tailwind'))->toBeTrue()
        ->and(seEconomy()->balance($student->id, 'tailwind'))->toBe(0)
        ->and(seEconomy()->spendReward($student->id, 'tailwind'))->toBeFalse();
})->group('scenario:SL-06');

it('extends the master Voyage streak when the full daily minimum is met', function () {
    $student = seEconomyStudent();
    $today = Carbon::parse('2026-08-17'); // a Monday

    DailyPlan::create([
        'student_id' => $student->id,
        'date' => $today->toDateString(),
        'is_writing_day' => true,
        'duties' => ['vocabulary' => true, 'reading' => true, 'map' => true, 'writing' => true],
    ]);

    $result = seEconomy()->completeDailyMinimumIfMet($student->id, $today);

    expect($result)->toBeTrue();

    $streak = StudentStreak::where('student_id', $student->id)
        ->where('type', 'voyage')->first();

    expect($streak)->not->toBeNull()
        ->and($streak->count)->toBe(1);
})->group('scenario:SE-01');

it('does not complete the day while a required duty is still open', function () {
    $student = seEconomyStudent();
    $today = Carbon::parse('2026-08-18'); // a Tuesday — no writing

    DailyPlan::create([
        'student_id' => $student->id,
        'date' => $today->toDateString(),
        'is_writing_day' => false,
        'duties' => ['vocabulary' => true, 'reading' => false, 'map' => true],
    ]);

    $result = seEconomy()->completeDailyMinimumIfMet($student->id, $today);

    expect($result)->toBeFalse()
        ->and(StudentStreak::where('student_id', $student->id)->where('type', 'voyage')->exists())->toBeFalse();
})->group('scenario:SE-01');

it('rejects an unknown reward type', function () {
    $student = seEconomyStudent();

    seEconomy()->grantReward($student->id, 'not_a_reward', 'ahead');
})->throws(InvalidArgumentException::class);
