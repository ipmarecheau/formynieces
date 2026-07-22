<?php

namespace App\Services\Pacing;

use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Notifications\PaceWarningNotification;
use App\Services\Motivation\StreakService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WeeklyRollover
{
    private const REQUIRED_PACE = 3;   // Std-5 yardstick: modules/week, global

    private const LAG_TRIGGER = 4;   // weeks behind that trips a warning

    public function __construct(
        private readonly CapResolver $capResolver,
        private readonly PacingClock $pacingClock,
        private readonly StreakService $streaks,
    ) {}

    public function runFor(User $student, ?Carbon $now = null): Collection
    {
        $now = $now ?? Carbon::today();
        $weekStart = $now->copy()->startOfWeek();

        // --- ML-06: advance or break the on-pace (weekly) streak --------------
        // Evaluate the just-completed week: if every target landed in it was
        // met, extend the pace streak by one; otherwise the streak resets to 0.
        // This is a gentle, student-facing celebration — never surfaced to the
        // guardian as a judgement metric.
        $priorWeekStart = $weekStart->copy()->subWeek();
        $priorTargets = WeeklyTarget::where('student_id', $student->id)
            ->where('week_start_date', $priorWeekStart->toDateString())->get();
        $onPace = $priorTargets->isNotEmpty() && $priorTargets->every(fn ($t) => (bool) $t->is_completed);
        if ($onPace) {
            $this->streaks->recordWeeklyActivity($student->id, 'pace_weeks', $priorWeekStart);
        } else {
            $this->streaks->resetStreak($student->id, 'pace_weeks');
        }

        $baseCap = $this->capResolver->resolve($student);

        $masteredModuleIds = StudentProgress::where('student_id', $student->id)
            ->where('status', 'mastered')
            ->pluck('module_id');

        $alreadyPlaced = WeeklyTarget::where('student_id', $student->id)
            ->where('week_start_date', '>=', $weekStart->toDateString())
            ->pluck('module_id');

        $excludeIds = $masteredModuleIds->merge($alreadyPlaced)->unique();

        $carriedIds = WeeklyTarget::where('student_id', $student->id)
            ->where('week_start_date', '<', $weekStart->toDateString())
            ->whereNotIn('module_id', $excludeIds)
            ->orderBy('week_start_date')
            ->orderBy('module_id')
            ->pluck('module_id')
            ->unique()
            ->values();

        $queue = $carriedIds->concat(
            SyllabusModule::whereNotIn('id', $excludeIds->merge($carriedIds))
                ->orderBy('pacing_week')
                ->orderBy('sequence_order')
                ->pluck('id')
        );

        // --- WT-03: honest re-pace (guardian layer only) ------------------
        $effectiveCap = $this->rePace($student, $queue, $masteredModuleIds->count(), $baseCap, $now);

        $this->placeAcrossWeeks($student, $queue, $weekStart, $effectiveCap, $now);

        return WeeklyTarget::where('student_id', $student->id)
            ->where('week_start_date', $weekStart->toDateString())
            ->get();
    }

    /**
     * Compute the lag, and — if the student is significantly behind — flip the
     * journey to a warning, auto-raise the cap to fit the remaining work, and
     * notify the guardian. If the student is on pace and a stale warning exists,
     * clear it once the remainder fits within base cap again. Returns the
     * effective cap to place with. Never surfaces anything to the student.
     */
    private function rePace(
        User $student,
        Collection $queue,
        int $masteredCount,
        int $baseCap,
        Carbon $now,
    ): int {
        $journey = StudentJourney::where('student_id', $student->id)->first();

        if ($journey === null) {
            return $baseCap;
        }

        $currentWeek = $this->pacingClock->currentPacingWeek($student, $now);
        $weeksToExam = max(1, $this->pacingClock->weeksToExam($student, $now));

        $expectedMastered = ($currentWeek - 1) * self::REQUIRED_PACE;
        $deficit = max(0, $expectedMastered - $masteredCount);
        $weeksBehind = intdiv($deficit, self::REQUIRED_PACE);

        $remaining = $queue->count();

        // --- AC-04: flag for admin cap review when feasible pace > cap ----
        $neededCap = (int) ceil($remaining / $weeksToExam);
        $journey->cap_review_required = $neededCap > $baseCap;
        $journey->required_pace = $neededCap > $baseCap ? $neededCap : null;

        if ($weeksBehind >= self::LAG_TRIGGER) {
            $neededCap = (int) ceil($remaining / $weeksToExam);
            $effectiveCap = max($baseCap, $neededCap);

            $journey->pace_status = 'warning';
            $journey->weeks_behind = $weeksBehind;
            $journey->save();

            if ($student->parent_id !== null) {
                $guardian = User::find($student->parent_id);
                $guardian?->notify(new PaceWarningNotification($student, $weeksBehind));
            }

            return $effectiveCap;
        }

        // On pace: unwind a stale warning once the remainder fits at base cap.
        if ($journey->pace_status === 'warning'
            && $remaining <= $weeksToExam * $baseCap) {
            $journey->pace_status = null;
            $journey->weeks_behind = null;
        }

        // AC-04: persist the cap-review flag whenever a journey exists, including
        // the on-pace path that previously skipped save() entirely.
        $journey->save();

        return $baseCap;
    }

    /**
     * Walk forward from $weekStart, placing each queued module into the earliest
     * week that has room under $cap. Rows already sitting in a week count against
     * its cap (no double-booking). Never schedules past the exam.
     */
    private function placeAcrossWeeks(
        User $student,
        Collection $queue,
        Carbon $weekStart,
        int $cap,
        Carbon $now,
    ): void {
        if ($cap < 1 || $queue->isEmpty()) {
            return;
        }

        $weeksToExam = max(1, $this->pacingClock->weeksToExam($student, $now));

        $counts = [];

        foreach ($queue as $moduleId) {
            $placed = false;

            for ($offset = 0; $offset < $weeksToExam; $offset++) {
                $target = $weekStart->copy()->addWeeks($offset);
                $key = $target->toDateString();

                if (! array_key_exists($key, $counts)) {
                    $counts[$key] = WeeklyTarget::where('student_id', $student->id)
                        ->where('week_start_date', $key)
                        ->count();
                }

                if ($counts[$key] < $cap) {
                    WeeklyTarget::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'module_id' => $moduleId,
                            'week_start_date' => $key,
                        ],
                        ['is_completed' => false],
                    );
                    $counts[$key]++;
                    $placed = true;
                    break;
                }
            }

            if (! $placed) {
                break;
            }
        }
    }
}
