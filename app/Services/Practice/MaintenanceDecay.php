<?php

declare(strict_types=1);

namespace App\Services\Practice;

use App\Models\StudentProgress;
use Illuminate\Support\Carbon;

/**
 * MaintenanceDecay — the weekly review that lets a mastered competency slip to
 * review when it was not maintained (LL-17).
 *
 * A mastered level's two-week window comes due; it then has a five-day grace. If
 * the grace passes without a re-mastery (three D5 first-try-correct, CompetencyCheck::
 * gradeMaintenance), the level decays to "mastered_review": it is no longer counted
 * as mastered, so it becomes eligible for a future weekly target again, and answering
 * three D5 first-try-correct restores it to "mastered".
 */
class MaintenanceDecay
{
    /**
     * Demote every mastered level whose grace has fully passed. Returns the number
     * of levels decayed.
     */
    public function run(?Carbon $asOf = null): int
    {
        $asOf ??= Carbon::now();

        $decayed = 0;

        StudentProgress::query()
            ->where('status', 'mastered')
            ->whereNotNull('mastered_at')
            ->each(function (StudentProgress $progress) use ($asOf, &$decayed): void {
                if ($progress->hasDecayed($asOf)) {
                    $progress->status = 'mastered_review';
                    $progress->save();
                    $decayed++;
                }
            });

        return $decayed;
    }
}
