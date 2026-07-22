<?php

namespace App\Services\Motivation;

use App\Models\StudentStreak;
use Illuminate\Support\Carbon;

/**
 * StreakService — streak accounting for student activities.
 *
 * Each (student, type) pair has its own streak row. recordActivity() decides
 * whether a given calendar day extends, repeats, or resets the running count:
 *  - same day as last activity  → leave count (idempotent, no double-count)
 *  - the day immediately after  → extend by 1 (consecutive)
 *  - a gap, or first ever day   → start fresh at 1
 *
 * recordWeeklyActivity() is the weekly analogue keyed by a week-start date:
 *  - same week as last activity → leave count (idempotent)
 *  - the week immediately prior → extend by 1 (consecutive)
 *  - a gap, or first ever week  → start fresh at 1
 */
class StreakService
{
    public function recordActivity(int $studentId, string $type, ?Carbon $on = null): StudentStreak
    {
        $on ??= Carbon::today();

        $streak = StudentStreak::firstOrNew([
            'student_id' => $studentId,
            'type' => $type,
        ]);
        $streak->count ??= 0;

        $last = $streak->last_activity_date;

        $restartOccurred = false;

        if ($last !== null && $last->isSameDay($on)) {
            // Already counted today — idempotent.
        } elseif ($last !== null && $last->isSameDay($on->copy()->subDay())) {
            // Consecutive day — extend the streak.
            $streak->count += 1;
        } else {
            // A gap, or first ever activity — start a fresh streak.
            $streak->count = 1;
            // A genuine return after a break only when there was prior activity;
            // a first-ever day is not a "welcome back".
            $restartOccurred = $last !== null;
        }

        $streak->last_activity_date = $on;
        $streak->restarted_at = $restartOccurred ? $on : null;
        $streak->save();

        return $streak->fresh();
    }

    /**
     * The weekly analogue of recordActivity(), keyed by a week-start date.
     * Used by the pace-streak rollover to count consecutive on-pace weeks.
     */
    public function recordWeeklyActivity(int $studentId, string $type, Carbon $weekStart): StudentStreak
    {
        $streak = StudentStreak::firstOrNew([
            'student_id' => $studentId,
            'type' => $type,
        ]);
        $streak->count ??= 0;

        $last = $streak->last_activity_date;

        if ($last !== null && $last->isSameDay($weekStart)) {
            // Already credited this week — idempotent.
        } elseif ($last !== null && $last->isSameDay($weekStart->copy()->subWeek())) {
            // Consecutive week — extend the streak.
            $streak->count += 1;
        } else {
            // A gap, or first ever credited week — start a fresh streak at 1.
            $streak->count = 1;
        }

        $streak->last_activity_date = $weekStart;
        $streak->save();

        return $streak->fresh();
    }

    /**
     * Reset a streak's count to 0 (e.g. when a week's target was not met).
     */
    public function resetStreak(int $studentId, string $type): void
    {
        StudentStreak::updateOrCreate(
            [
                'student_id' => $studentId,
                'type' => $type,
            ],
            ['count' => 0],
        );
    }
}
