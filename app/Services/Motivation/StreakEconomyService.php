<?php

namespace App\Services\Motivation;

use App\Models\DailyPlan;
use App\Models\StreakReward;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * StreakEconomyService — the machinery above the raw StreakService.
 *
 * Owns the Captain's Locker (protective rewards) and the master Voyage streak
 * that sits over the per-thread sub-streaks. Reuses StreakService for the actual
 * streak accounting; adds reward inventory (grant/spend) and the daily-minimum
 * completion hook that feeds the master streak.
 *
 * Honest + never-negative: rewards protect a streak but never fabricate progress.
 * Pace-gating (Shore Leave eligibility, weekend rest) and freeze/revive mechanics
 * land in a later increment once StreakService gains freeze support.
 */
class StreakEconomyService
{
    /** The master streak type: the whole day's minimum completed (CO-07/SE-01). */
    public const MASTER_STREAK = 'voyage';

    public function __construct(private StreakService $streaks) {}

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
