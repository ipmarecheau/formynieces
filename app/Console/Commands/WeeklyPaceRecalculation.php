<?php

namespace App\Console\Commands;

use App\Models\StudentJourney;
use App\Models\User;
use App\Services\Pacing\WeeklyRollover;
use Illuminate\Console\Command;

/**
 * WT-03 — the once-a-week recalculation of every active student's pace and
 * progress. Runs the weekly rollover for each student who has an onboarded
 * journey, refreshing weeks_behind / pace_status / required_pace and stamping
 * pace_recalculated_at (shown to the guardian as "Progress updated"). Paused
 * students are skipped by the rollover itself.
 */
class WeeklyPaceRecalculation extends Command
{
    protected $signature = 'pace:weekly-recalculation';

    protected $description = 'Recalculate pace and progress for every active student (weekly).';

    public function handle(WeeklyRollover $rollover): int
    {
        $studentIds = StudentJourney::query()->pluck('student_id');

        $count = 0;
        foreach (User::whereIn('id', $studentIds)->where('role', 'student')->cursor() as $student) {
            $rollover->runFor($student);
            $count++;
        }

        $this->info("Pace/progress recalculated for {$count} student(s).");

        return self::SUCCESS;
    }
}
