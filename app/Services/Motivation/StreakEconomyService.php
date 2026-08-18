<?php

namespace App\Services\Motivation;

use App\Models\DailyPlan;
use App\Models\StreakBank;
use App\Models\StreakReward;
use App\Models\StreakShield;
use App\Models\StudentStreak;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * StreakEconomyService — the machinery above the raw StreakService.
 *
 * Owns the Captain's Locker (protective rewards), the master Voyage streak that
 * sits over the per-thread sub-streaks, the "on pace" reading that decides how
 * much flexibility she has earned, and the reward mechanics that protect or push
 * a streak (Shore Leave, Anchor, Tailwind, Lifebuoy) plus subject banking.
 *
 * Honest + never-negative: rewards protect a streak but never fabricate progress,
 * so pace stays true; a lost streak is only ever restarted kindly, never punished.
 */
class StreakEconomyService
{
    /** The master streak type: the whole day's minimum completed (CO-07/SE-01). */
    public const MASTER_STREAK = 'voyage';

    /** The per-thread sub-streaks that feed the master (SE-02). */
    public const SUB_STREAKS = ['reading', 'vocabulary', 'writing', 'map'];

    /** Starter protection days a new voyager begins with (SE-03). */
    public const STARTER_PROTECTION_DAYS = 3;

    public function __construct(
        private StreakService $streaks,
        private DailyPlanComposer $planComposer,
    ) {}

    /**
     * Grant one reward of a type, recording how it was earned (SE-13/14/15).
     * Sources: ahead | milestone | guardian | xp.
     */
    public function grantReward(int $studentId, string $type, string $source): StreakReward
    {
        $this->assertType($type);

        $reward = StreakReward::firstOrNew([
            'student_id' => $studentId,
            'type' => $type,
        ]);
        $reward->quantity = ($reward->quantity ?? 0) + 1;
        $reward->source = $source;
        $reward->save();

        return $reward->fresh();
    }

    /**
     * How many of a reward the student currently holds (SL-05).
     */
    public function balance(int $studentId, string $type): int
    {
        return (int) (StreakReward::query()
            ->where('student_id', $studentId)
            ->where('type', $type)
            ->value('quantity') ?? 0);
    }

    /**
     * Spend one reward from the Locker (SL-06). Returns false if none held.
     */
    public function spendReward(int $studentId, string $type): bool
    {
        $reward = StreakReward::query()
            ->where('student_id', $studentId)
            ->where('type', $type)
            ->first();

        if ($reward === null || $reward->quantity < 1) {
            return false;
        }

        $reward->decrement('quantity');

        return true;
    }

    /**
     * Complete the day when the full minimum is met, extending the master Voyage
     * streak (CO-07/SE-01). No-op (returns false) when the minimum is not yet met.
     */
    public function completeDailyMinimumIfMet(int $studentId, ?Carbon $on = null): bool
    {
        $on ??= Carbon::today();
        $plan = $this->planFor($studentId, $on);

        if (! $plan->isMinimumMet()) {
            return false;
        }

        if ($plan->completed_at === null) {
            $plan->completed_at = $on;
            $plan->save();
        }

        $this->streaks->recordActivity($studentId, self::MASTER_STREAK, $on);

        return true;
    }

    /**
     * Complete one thread of the daily minimum (SE-02). The thread's own
     * sub-streak advances; the master Voyage streak only reflects completing the
     * whole minimum, not any single thread.
     */
    public function completeThread(int $studentId, string $thread, ?Carbon $on = null): StudentStreak
    {
        if (! in_array($thread, self::SUB_STREAKS, true)) {
            throw new InvalidArgumentException("Unknown daily thread: {$thread}");
        }

        $on ??= Carbon::today();

        $subStreak = $this->streaks->recordActivity($studentId, $thread, $on);

        // The master only grows once the whole minimum is met — never off one thread.
        $this->completeDailyMinimumIfMet($studentId, $on);

        return $subStreak;
    }

    /**
     * Whether the student is on pace this day — every module in this week's target
     * mastered (SE-04). Delegates to the same guardian-layer truth the daily plan
     * reads; the child only ever meets it as kind flexibility, never a deficit.
     */
    public function isOnPace(int $studentId, ?Carbon $on = null): bool
    {
        return $this->planComposer->isOnPace($studentId, $on ?? Carbon::today());
    }

    /**
     * Register a missed day and decide, kindly, what happens to her streaks
     * (SE-03/08/12). Returns the outcome:
     *   'frozen'  — an Anchor had frozen the day, so every streak is held (SE-08)
     *   'starter' — a starter protection day was spent to hold her streaks (SE-03)
     *   'reset'   — no protection left, so the master streak restarts kindly (SE-12)
     */
    public function registerMiss(int $studentId, ?Carbon $on = null): string
    {
        $on ??= Carbon::today();
        $shield = $this->shieldFor($studentId);

        if ($shield->frozen_on !== null && $shield->frozen_on->isSameDay($on)) {
            return 'frozen';
        }

        if ($shield->starter_protection_remaining > 0) {
            $shield->decrement('starter_protection_remaining');

            return 'starter';
        }

        // Never-negative kind reset: snapshot the lost count for a possible Lifebuoy,
        // then restart the master streak from zero (SE-12).
        $master = StudentStreak::query()
            ->where('student_id', $studentId)
            ->where('type', self::MASTER_STREAK)
            ->first();

        if ($master !== null && $master->count > 0) {
            $master->previous_count = $master->count;
            $master->restarted_at = $on;
            $master->count = 0;
            $master->save();
        }

        return 'reset';
    }

    /**
     * How many starter protection days she has left (SE-03).
     */
    public function starterProtectionRemaining(int $studentId): int
    {
        return $this->shieldFor($studentId)->starter_protection_remaining;
    }

    /**
     * Shore Leave: excuse one of today's duties without breaking the streak
     * (SE-07). Only available when she is on pace and holds the reward. The
     * excused duty is not counted as progress, so her pace stays honest. Returns
     * false when she is behind pace or holds no Shore Leave.
     */
    public function useShoreLeave(int $studentId, string $duty, ?Carbon $on = null): bool
    {
        $on ??= Carbon::today();

        if (! $this->isOnPace($studentId, $on)) {
            return false;
        }

        if (! $this->spendReward($studentId, 'shore_leave')) {
            return false;
        }

        $plan = $this->planFor($studentId, $on);
        $duties = $plan->duties ?? [];
        // 'excused' is truthy, so the minimum reads as met (the streak is held),
        // but it is never real progress toward mastery or pace.
        $duties[$duty] = 'excused';
        $plan->duties = $duties;
        $plan->save();

        return true;
    }

    /**
     * Anchor: freeze every streak — master and sub — for a day she cannot sail
     * (SE-08). May be used even when she is behind pace. Returns false when she
     * holds no Anchor.
     */
    public function useAnchor(int $studentId, ?Carbon $on = null): bool
    {
        $on ??= Carbon::today();

        if (! $this->spendReward($studentId, 'anchor')) {
            return false;
        }

        $shield = $this->shieldFor($studentId);
        $shield->frozen_on = $on;
        $shield->save();

        return true;
    }

    /**
     * Whether every streak is frozen on the given day (SE-08).
     */
    public function isFrozenOn(int $studentId, ?Carbon $on = null): bool
    {
        $on ??= Carbon::today();
        $frozen = $this->shieldFor($studentId)->frozen_on;

        return $frozen !== null && $frozen->isSameDay($on);
    }

    /**
     * Accelerate a subject: bank one day ahead on it (SE-09). Banks at most one
     * day ahead per subject; use a Tailwind to reach two (SE-10). Returns false
     * when the subject is already at the normal one-day limit.
     */
    public function accelerate(int $studentId, string $subject): bool
    {
        $bank = $this->bankFor($studentId, $subject);

        if ($bank->days_ahead >= 1) {
            return false;
        }

        $bank->increment('days_ahead');

        return true;
    }

    /**
     * Tailwind: raise the banking limit to two days ahead on a subject (SE-10).
     * Consumes the reward and banks a further day (up to two). Returns false when
     * she holds no Tailwind or the subject is already two days ahead.
     */
    public function useTailwind(int $studentId, string $subject): bool
    {
        $bank = $this->bankFor($studentId, $subject);

        if ($bank->days_ahead >= 2) {
            return false;
        }

        if (! $this->spendReward($studentId, 'tailwind')) {
            return false;
        }

        $bank->increment('days_ahead');

        return true;
    }

    /**
     * How many days ahead a subject is currently banked (SE-09/10).
     */
    public function bankedDays(int $studentId, string $subject): int
    {
        return $this->bankFor($studentId, $subject)->days_ahead;
    }

    /**
     * Lifebuoy: revive a streak that has just reset (SE-11). Only works when the
     * streak reset within the last day and she holds a Lifebuoy; restores the
     * count it had, and a given reset can be rescued only once. Returns false
     * otherwise.
     */
    public function useLifebuoy(int $studentId, string $type = self::MASTER_STREAK, ?Carbon $on = null): bool
    {
        $on ??= Carbon::today();

        $streak = StudentStreak::query()
            ->where('student_id', $studentId)
            ->where('type', $type)
            ->first();

        if ($streak === null
            || $streak->previous_count === null
            || $streak->restarted_at === null
            || $streak->restarted_at->lt($on->copy()->subDay())) {
            return false;
        }

        if (! $this->spendReward($studentId, 'lifebuoy')) {
            return false;
        }

        $streak->count = $streak->previous_count;
        // Clear the snapshot so the same reset can never be rescued twice.
        $streak->previous_count = null;
        $streak->restarted_at = null;
        $streak->save();

        return true;
    }

    private function shieldFor(int $studentId): StreakShield
    {
        return StreakShield::firstOrCreate(
            ['student_id' => $studentId],
            ['starter_protection_remaining' => self::STARTER_PROTECTION_DAYS],
        );
    }

    private function bankFor(int $studentId, string $subject): StreakBank
    {
        return StreakBank::firstOrCreate(
            ['student_id' => $studentId, 'subject' => $subject],
            ['days_ahead' => 0],
        );
    }

    private function planFor(int $studentId, Carbon $on): DailyPlan
    {
        return DailyPlan::firstOrCreate(
            ['student_id' => $studentId, 'date' => $on->toDateString()],
            ['is_writing_day' => false, 'duties' => []],
        );
    }

    private function assertType(string $type): void
    {
        if (! in_array($type, StreakReward::TYPES, true)) {
            throw new InvalidArgumentException("Unknown reward type: {$type}");
        }
    }
}
