<?php

declare(strict_types=1);

namespace App\Services\Pacing;

use App\Models\StudentJourney;
use App\Models\StudentStreak;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * PauseService — a guardian pauses or resumes a student.
 *
 * While paused (`users.paused_at` set), WeeklyRollover generates no targets and
 * leaves streaks untouched. Resuming re-paces the journey forward by the paused
 * duration so no weeks are "missed" (WT-05), and bridges frozen day-streaks so
 * the next activity extends them rather than resetting (ML-03).
 */
final class PauseService
{
    /** Day-based streaks that a pause freezes (the weekly pace streak is frozen by the rollover skip). */
    private const DAILY_STREAK_TYPES = ['practice', 'login', 'mastery'];

    public function pause(User $student): void
    {
        $student->forceFill(['paused_at' => Carbon::now()])->save();
    }

    public function resume(User $student): void
    {
        if ($student->paused_at === null) {
            return;
        }

        $pausedDays = $student->paused_at->copy()->startOfDay()
            ->diffInDays(Carbon::now()->startOfDay());

        // WT-05: shift the journey forward by the paused span so the current
        // pacing week lands where it was at pause — no missed weeks.
        if ($pausedDays > 0) {
            $journey = StudentJourney::where('student_id', $student->id)->first();
            if ($journey !== null) {
                $journey->journey_start = $journey->journey_start->copy()->addDays($pausedDays);
                $journey->save();
            }
        }

        // ML-03: bridge each frozen day-streak to yesterday so the next activity
        // extends it instead of counting the pause as a gap.
        StudentStreak::where('student_id', $student->id)
            ->whereIn('type', self::DAILY_STREAK_TYPES)
            ->where('count', '>', 0)
            ->update(['last_activity_date' => Carbon::now()->startOfDay()->subDay()]);

        $student->forceFill(['paused_at' => null])->save();
    }
}
