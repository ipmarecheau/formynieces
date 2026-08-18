<?php

namespace App\Services\Motivation;

use App\Models\PracticeAttempt;
use Illuminate\Support\Carbon;

/**
 * WritingGate (CO-05 / WR-07 / AM-11) — on a writing day, opening a NEW level
 * waits until the day's writing is done. It is a same-day nudge, never a wall:
 * the map stays explorable and already-started levels remain playable, and with
 * no prompt this week the gate lifts entirely (WR-08).
 */
class WritingGate
{
    public function __construct(private DailyPlanComposer $plans) {}

    /**
     * True when opening this not-yet-started module must wait for today's writing.
     */
    public function blocksNewLevel(int $studentId, int $moduleId, ?Carbon $on = null): bool
    {
        $on ??= Carbon::today();

        $plan = $this->plans->forDay($studentId, $on);

        if (! $plan->is_writing_day) {
            return false;
        }

        $duties = $plan->duties ?? [];

        // No writing duty today (e.g. no prompt this week, WR-08) → no gate.
        if (! array_key_exists('writing', $duties)) {
            return false;
        }

        // Writing already done → the level opens normally.
        if ($duties['writing'] === true) {
            return false;
        }

        // Only NEW levels are gated; an already-started level stays playable.
        $alreadyStarted = PracticeAttempt::where('student_id', $studentId)
            ->where('module_id', $moduleId)
            ->exists();

        return ! $alreadyStarted;
    }
}
