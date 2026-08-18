<?php

namespace App\Services\Motivation;

use App\Models\DailyPlan;
use App\Models\PracticeAttempt;
use App\Models\WeeklyTarget;
use App\Services\SchoolJournal\SchoolEvidenceService;
use Illuminate\Support\Carbon;

/**
 * DailyPlanComposer — builds the day's Captain's Brief: the minimum duties a
 * student must cover today, as done/not-done flags (CO-02..09).
 *
 * Weekdays: vocabulary + reading + map progress, plus writing on Mon/Wed/Fri.
 * Weekends: shore leave (nothing required) when she is on pace, or a bounded,
 * kind catch-up (no writing) when she has fallen behind this week's target.
 *
 * The map thread is satisfied live by any practice activity today; the reading,
 * vocabulary, and writing threads carry a flag their engines flip as they land
 * (and can be flipped directly via markDuty in the meantime).
 */
class DailyPlanComposer
{
    /** The threads always required on a study day. The Morning Tide is one duty
     *  covering reading + vocabulary (DR/DV presented as a single ritual). */
    private const WEEKDAY_DUTIES = ['morning_tide', 'map'];

    /** ISO weekdays that carry a writing duty: Monday, Wednesday, Friday. */
    private const WRITING_DAYS = [1, 3, 5];

    /**
     * Compose (or refresh) the day's plan for a student, persisting it.
     */
    public function forDay(int $studentId, ?Carbon $on = null): DailyPlan
    {
        $on ??= Carbon::today();

        $isWritingDay = in_array($on->dayOfWeekIso, self::WRITING_DAYS, true);
        $required = $this->requiredDuties($studentId, $on, $isWritingDay);

        $plan = DailyPlan::firstOrNew([
            'student_id' => $studentId,
            'date' => $on->toDateString(),
        ]);

        $existing = $plan->duties ?? [];
        $duties = [];
        foreach ($required as $key) {
            $duties[$key] = $existing[$key] ?? false;
        }

        // The map thread checks off live as soon as she does any practice today.
        if (array_key_exists('map', $duties) && $this->hasMapActivity($studentId, $on)) {
            $duties['map'] = true;
        }

        $plan->is_writing_day = $isWritingDay;
        $plan->duties = $duties;
        $plan->focus_hint = $this->schoolFocusHint($studentId); // SJ-05 — gentle, never a gate
        $plan->save();

        return $plan->fresh();
    }

    /**
     * SJ-05 — a strand the school flagged weak becomes today's gentle focus:
     * one kind sentence in the Captain's Brief, never an extra duty, never a
     * mention of marks or grades (the child's world stays clean, SJ-06).
     */
    private function schoolFocusHint(int $studentId): ?string
    {
        $focus = app(SchoolEvidenceService::class)->weakestFocus($studentId);

        if ($focus === null) {
            return null;
        }

        [$label, $isTopic] = $focus;
        $what = $isTopic ? "{$label} (straight from her school papers)" : $label;

        return "Smooth suggests visiting {$what} today — a little practice there would feel great. Just a suggestion, Captain!";
    }

    /**
     * Mark a single duty done for the day (used by a thread's engine, or the
     * sidebar for threads whose engines are not yet built).
     */
    public function markDuty(int $studentId, string $duty, ?Carbon $on = null): DailyPlan
    {
        $on ??= Carbon::today();
        $plan = $this->forDay($studentId, $on);

        $duties = $plan->duties;
        if (array_key_exists($duty, $duties)) {
            $duties[$duty] = true;
            $plan->duties = $duties;
            $plan->save();
        }

        return $plan->fresh();
    }

    /**
     * The duty keys required today.
     *
     * @return list<string>
     */
    private function requiredDuties(int $studentId, Carbon $on, bool $isWritingDay): array
    {
        if ($on->isWeekend()) {
            // Shore leave when on pace (CO-08); bounded catch-up when behind (CO-09).
            // Weekends never carry a writing duty (WR-06).
            return $this->isOnPace($studentId, $on) ? [] : self::WEEKDAY_DUTIES;
        }

        $duties = self::WEEKDAY_DUTIES;
        if ($isWritingDay) {
            $duties[] = 'writing';
        }

        return $duties;
    }

    /**
     * On pace = every module in this week's target is mastered (SE-04). No target
     * this week means nothing is due, which is on pace. Guardian-layer truth; the
     * child only ever meets it as weekend rest, never as a deficit.
     */
    private function isOnPace(int $studentId, Carbon $on): bool
    {
        $weekStart = $on->copy()->startOfWeek()->toDateString();

        $targets = WeeklyTarget::query()
            ->where('student_id', $studentId)
            ->where('week_start_date', $weekStart)
            ->get();

        if ($targets->isEmpty()) {
            return true;
        }

        foreach ($targets as $target) {
            if ($target->state() !== 'completed') {
                return false;
            }
        }

        return true;
    }

    private function hasMapActivity(int $studentId, Carbon $on): bool
    {
        return PracticeAttempt::query()
            ->where('student_id', $studentId)
            ->whereDate('created_at', $on->toDateString())
            ->exists();
    }
}
