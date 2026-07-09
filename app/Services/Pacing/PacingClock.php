<?php

namespace App\Services\Pacing;

use App\Models\StudentJourney;
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

        $weeksElapsed = $start->diffInWeeks($now);

        return $weeksElapsed + 1;
    }

    public function weeksToExam(User $student, ?Carbon $now = null): int
    {
        $now = $now ?? Carbon::today();
        $exam = $this->journey($student)->exam_date->copy()->startOfDay();

        return $now->diffInWeeks($exam);
    }

    private function journey(User $student): StudentJourney
    {
        return StudentJourney::where('student_id', $student->id)->firstOrFail();
    }
}
