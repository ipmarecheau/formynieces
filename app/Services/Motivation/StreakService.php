<?php

namespace App\Services\Motivation;

use App\Models\StudentStreak;
use Illuminate\Support\Carbon;

/**
 * StreakService — day-streak accounting for student activities.
 *
 * Each (student, type) pair has its own streak row. recordActivity() decides
 * whether a given calendar day extends, repeats, or resets the running count:
 *  - same day as last activity  → leave count (idempotent, no double-count)
 *  - the day immediately after  → extend by 1 (consecutive)
 *  - a gap, or first ever day   → start fresh at 1
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
}
