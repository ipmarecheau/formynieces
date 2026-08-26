<?php

use App\Models\DailyPlan;
use App\Models\StudentProgress;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Motivation\DailyPlanComposer;
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

/**
 * Give the student a current-week target and set whether its module is mastered,
 * so isOnPace() reads true (mastered) or false (behind).
 */
function seSetPace(User $student, bool $onPace, ?Carbon $on = null): void
{
    $on ??= Carbon::today();
    $module = SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Number: Pace '.uniqid(), 'sea_section' => 'Number',
        'sequence_order' => 1, 'pacing_week' => 1,
    ]);
    WeeklyTarget::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'week_start_date' => $on->copy()->startOfWeek()->toDateString(),
        'is_completed' => $onPace,
    ]);
    if ($onPace) {
        StudentProgress::create([
            'student_id' => $student->id,
            'module_id' => $module->id,
            'status' => 'mastered',
            'mastered_at' => $on,
        ]);
    }
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

it('advances a thread sub-streak without advancing the master off one thread', function () {
    $student = seEconomyStudent();
    $today = Carbon::parse('2026-08-18'); // Tuesday — no writing

    DailyPlan::create([
        'student_id' => $student->id,
        'date' => $today->toDateString(),
        'is_writing_day' => false,
        'duties' => ['vocabulary' => false, 'reading' => false, 'map' => false],
    ]);

    $sub = seEconomy()->completeThread($student->id, 'reading', $today);

    expect($sub->type)->toBe('reading')
        ->and($sub->count)->toBe(1)
        // The whole minimum is not met, so the master must not have advanced.
        ->and(StudentStreak::where('student_id', $student->id)->where('type', 'voyage')->exists())->toBeFalse();
})->group('scenario:SE-02');

it('spends starter protection to hold streaks, then resets kindly once used up', function () {
    $student = seEconomyStudent();

    expect(seEconomy()->registerMiss($student->id))->toBe('starter')
        ->and(seEconomy()->starterProtectionRemaining($student->id))->toBe(2)
        ->and(seEconomy()->registerMiss($student->id))->toBe('starter')
        ->and(seEconomy()->registerMiss($student->id))->toBe('starter')
        ->and(seEconomy()->starterProtectionRemaining($student->id))->toBe(0)
        // Protection exhausted — the next miss is a kind reset, never punished.
        ->and(seEconomy()->registerMiss($student->id))->toBe('reset');
})->group('scenario:SE-03');

it('reads on pace when this week\'s target is mastered', function () {
    $student = seEconomyStudent();
    seSetPace($student, onPace: true);

    expect(seEconomy()->isOnPace($student->id))->toBeTrue();
})->group('scenario:SE-04');

it('stands the weekend down and allows Shore Leave when she is on pace', function () {
    $student = seEconomyStudent();
    $saturday = Carbon::parse('2026-08-22'); // Saturday
    seSetPace($student, onPace: true, on: $saturday);
    seEconomy()->grantReward($student->id, 'shore_leave', 'ahead');

    $plan = app(DailyPlanComposer::class)->forDay($student->id, $saturday);

    expect($plan->requiredDuties())->toBe([]) // weekend rest, streak protected
        ->and(seEconomy()->useShoreLeave($student->id, 'reading', $saturday))->toBeTrue();
})->group('scenario:SE-05');

it('keeps the weekend working, kindly, when she is behind pace', function () {
    $student = seEconomyStudent();
    $saturday = Carbon::parse('2026-08-22'); // Saturday
    seSetPace($student, onPace: false, on: $saturday);

    $plan = app(DailyPlanComposer::class)->forDay($student->id, $saturday);

    // Behind pace → bounded catch-up duties, but never a writing duty on a weekend.
    expect($plan->requiredDuties())->not->toBe([])
        ->and($plan->requiredDuties())->not->toContain('writing');
})->group('scenario:SE-06');

it('excuses one duty with Shore Leave without counting it as progress', function () {
    $student = seEconomyStudent();
    $today = Carbon::parse('2026-08-18');
    seSetPace($student, onPace: true, on: $today);
    seEconomy()->grantReward($student->id, 'shore_leave', 'ahead');

    DailyPlan::create([
        'student_id' => $student->id,
        'date' => $today->toDateString(),
        'is_writing_day' => false,
        'duties' => ['vocabulary' => true, 'reading' => false, 'map' => true],
    ]);

    expect(seEconomy()->useShoreLeave($student->id, 'reading', $today))->toBeTrue();

    $plan = DailyPlan::where('student_id', $student->id)->where('date', $today->toDateString())->first();
    // The duty reads as excused (streak held) but was never real progress.
    expect($plan->duties['reading'])->toBe('excused')
        ->and($plan->isMinimumMet())->toBeTrue()
        ->and(seEconomy()->balance($student->id, 'shore_leave'))->toBe(0);
})->group('scenario:SE-07');

it('refuses Shore Leave when she is behind pace', function () {
    $student = seEconomyStudent();
    seSetPace($student, onPace: false);
    seEconomy()->grantReward($student->id, 'shore_leave', 'ahead');

    expect(seEconomy()->useShoreLeave($student->id, 'reading'))->toBeFalse()
        // The reward is not spent when it could not be used.
        ->and(seEconomy()->balance($student->id, 'shore_leave'))->toBe(1);
})->group('scenario:SE-07');

it('freezes every streak for a day with an Anchor, even when behind pace', function () {
    $student = seEconomyStudent();
    $today = Carbon::parse('2026-08-18');
    seSetPace($student, onPace: false, on: $today); // behind — Anchor still allowed
    seEconomy()->grantReward($student->id, 'anchor', 'guardian');

    expect(seEconomy()->useAnchor($student->id, $today))->toBeTrue()
        ->and(seEconomy()->isFrozenOn($student->id, $today))->toBeTrue()
        // A missed day while frozen holds the streaks rather than resetting.
        ->and(seEconomy()->registerMiss($student->id, $today))->toBe('frozen')
        // The freeze is for ONE day only — the next day is no longer frozen.
        ->and(seEconomy()->isFrozenOn($student->id, $today->copy()->addDay()))->toBeFalse();
})->group('scenario:SE-08');

it('banks one day ahead when accelerating a subject, capped at one', function () {
    $student = seEconomyStudent();

    expect(seEconomy()->accelerate($student->id, 'reading'))->toBeTrue()
        ->and(seEconomy()->bankedDays($student->id, 'reading'))->toBe(1)
        // Cannot bank beyond one day ahead without a Tailwind.
        ->and(seEconomy()->accelerate($student->id, 'reading'))->toBeFalse()
        ->and(seEconomy()->bankedDays($student->id, 'reading'))->toBe(1);
})->group('scenario:SE-09');

it('raises the banking limit to two days ahead with a Tailwind', function () {
    $student = seEconomyStudent();
    seEconomy()->grantReward($student->id, 'tailwind', 'ahead');
    seEconomy()->accelerate($student->id, 'reading'); // banked to 1

    expect(seEconomy()->useTailwind($student->id, 'reading'))->toBeTrue()
        ->and(seEconomy()->bankedDays($student->id, 'reading'))->toBe(2)
        ->and(seEconomy()->balance($student->id, 'tailwind'))->toBe(0);
})->group('scenario:SE-10');

it('revives a just-reset streak with a Lifebuoy, only once', function () {
    $student = seEconomyStudent();
    $today = Carbon::parse('2026-08-18');
    // A master streak that reset today from 6.
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'voyage',
        'count' => 0,
        'previous_count' => 6,
        'last_activity_date' => $today->toDateString(),
        'restarted_at' => $today->toDateString(),
    ]);
    seEconomy()->grantReward($student->id, 'lifebuoy', 'milestone');

    expect(seEconomy()->useLifebuoy($student->id, 'voyage', $today))->toBeTrue();

    $streak = StudentStreak::where('student_id', $student->id)->where('type', 'voyage')->first();
    expect($streak->count)->toBe(6)
        // The same reset can never be rescued twice.
        ->and(seEconomy()->useLifebuoy($student->id, 'voyage', $today))->toBeFalse();
})->group('scenario:SE-11');

it('refuses a Lifebuoy once the reset is more than a day old', function () {
    $student = seEconomyStudent();
    $resetDay = Carbon::parse('2026-08-18');
    // A master streak that reset on the 18th from 6.
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'voyage',
        'count' => 0,
        'previous_count' => 6,
        'last_activity_date' => $resetDay->toDateString(),
        'restarted_at' => $resetDay->toDateString(),
    ]);
    seEconomy()->grantReward($student->id, 'lifebuoy', 'milestone');

    // Two days later the "just reset" window has passed — the rescue is refused,
    // and crucially the Lifebuoy is NOT spent.
    expect(seEconomy()->useLifebuoy($student->id, 'voyage', $resetDay->copy()->addDays(2)))->toBeFalse()
        ->and(seEconomy()->balance($student->id, 'lifebuoy'))->toBe(1);

    $streak = StudentStreak::where('student_id', $student->id)->where('type', 'voyage')->first();
    expect($streak->count)->toBe(0); // unchanged — no revival
})->group('scenario:SE-11');

it('restarts the master streak from zero, kindly, when no protection remains', function () {
    $student = seEconomyStudent();
    // Exhaust the three starter days first.
    seEconomy()->registerMiss($student->id);
    seEconomy()->registerMiss($student->id);
    seEconomy()->registerMiss($student->id);

    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'voyage',
        'count' => 5,
        'last_activity_date' => Carbon::yesterday()->toDateString(),
    ]);

    expect(seEconomy()->registerMiss($student->id))->toBe('reset');

    $streak = StudentStreak::where('student_id', $student->id)->where('type', 'voyage')->first();
    // Never-negative: restarts at zero, and the lost count is kept for a Lifebuoy.
    expect($streak->count)->toBe(0)
        ->and($streak->previous_count)->toBe(5);
})->group('scenario:SE-12');
