<?php

declare(strict_types=1);

namespace App\Services\Pacing;

use App\Models\StudentPause;
use App\Models\StudentStreak;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * PauseService — a guardian pauses or resumes a student.
 *
 * Each pause is recorded as a span in student_pauses (the audit log), and the
 * fast `users.paused_at` flag marks "paused right now". While paused,
 * WeeklyRollover generates no targets and leaves streaks untouched. The pacing
 * clock excludes total paused time, so journey_start is never rewritten and a
 * pause never counts against the student (WT-05). On resume, frozen day-streaks
 * are bridged so the next activity extends them (ML-03).
 */
final class PauseService
{
    /** Day-based streaks that a pause freezes (the weekly pace streak is frozen by the rollover skip). */
    private const DAILY_STREAK_TYPES = ['practice', 'login', 'mastery'];

    public function pause(User $student): void
    {
        if ($student->isPaused()) {
            return;
        }

        $now = Carbon::now();

        StudentPause::create([
            'student_id' => $student->id,
            'paused_at' => $now,
            'resumed_at' => null,
        ]);

        $student->forceFill(['paused_at' => $now])->save();
    }

    public function resume(User $student): void
    {
        if (! $student->isPaused()) {
            return;
        }

        // Close the open pause span in the audit log.
        StudentPause::where('student_id', $student->id)
            ->whereNull('resumed_at')
            ->update(['resumed_at' => Carbon::now()]);

        // ML-03: bridge each frozen day-streak to yesterday so the next activity
        // extends it instead of counting the pause as a gap.
        StudentStreak::where('student_id', $student->id)
            ->whereIn('type', self::DAILY_STREAK_TYPES)
            ->where('count', '>', 0)
            ->update(['last_activity_date' => Carbon::now()->startOfDay()->subDay()]);

        $student->forceFill(['paused_at' => null])->save();
    }
}
