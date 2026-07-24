<?php

namespace App\Services\Pacing;

use App\Models\StudentJourney;
use App\Models\StudentPause;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Two per-student clocks, both anchored at onboarding:
 *
 *   currentPacingWeek() = whole weeks since journey_start + 1  (child-facing;
 *                         drives which modules the Sunday rollover serves)
 *   weeksToExam()       = whole weeks from today to exam_date  (dashboard;
 *                         feeds the WT-03 lag math against required_pace)
 *
 * required_pace (3 modules/week) is NOT computed here — this class only reports
 * position on the calendar, never lag. Lag lands in WT-03.
 */
class PacingClock
{
    public function currentPacingWeek(User $student, ?Carbon $now = null): int
    {
        $now = $now ?? Carbon::today();
        $start = $this->journey($student)->journey_start->copy()->startOfDay();

        // Paused time is excluded so a pause never counts against the student:
        // journey_start stays a historical fact; the clock discounts the gap.
        $elapsedDays = (int) $start->diffInDays($now);
        $activeDays = max(0, $elapsedDays - $this->pausedDays($student, $now));

        return intdiv($activeDays, 7) + 1;
    }

    /**
     * Total days this student has spent paused, from the pause audit log. An
     * still-open pause counts up to $now, so the clock freezes while paused.
     */
    private function pausedDays(User $student, Carbon $now): int
    {
        return (int) StudentPause::where('student_id', $student->id)->get()
            ->sum(fn (StudentPause $pause): int => (int) $pause->paused_at->copy()->startOfDay()
                ->diffInDays(($pause->resumed_at ?? $now)->copy()->startOfDay()));
    }

    public function weeksToExam(User $student, ?Carbon $now = null): int
    {
        $now = $now ?? Carbon::today();
        $exam = $this->journey($student)->exam_date->copy()->startOfDay();

        return (int) $now->diffInWeeks($exam);
    }

    private function journey(User $student): StudentJourney
    {
        return StudentJourney::where('student_id', $student->id)->firstOrFail();
    }
}
