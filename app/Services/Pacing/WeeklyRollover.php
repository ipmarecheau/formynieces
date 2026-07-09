<?php

namespace App\Services\Pacing;

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WeeklyRollover
{
    public function __construct(
        private readonly CapResolver $capResolver,
        private readonly PacingClock $pacingClock,
    ) {
    }

    public function runFor(User $student, ?Carbon $now = null): Collection
    {
        $now = $now ?? Carbon::today();
        $weekStart = $now->copy()->startOfWeek();
        $cap = $this->capResolver->resolve($student);

        $masteredModuleIds = StudentProgress::where('student_id', $student->id)
            ->where('status', 'mastered')
            ->pluck('module_id');

        // Modules already placed in this week or any future week — these keep
        // their slot and their placement, and are excluded from re-selection.
        $alreadyPlaced = WeeklyTarget::where('student_id', $student->id)
            ->where('week_start_date', '>=', $weekStart->toDateString())
            ->pluck('module_id');

        $excludeIds = $masteredModuleIds->merge($alreadyPlaced)->unique();

        // Priority queue: carried (unmastered, from weeks before this one,
        // oldest first) then frontier (lowest pacing_week). Carry always wins.
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

        $this->placeAcrossWeeks($student, $queue, $weekStart, $cap, $now);

        return WeeklyTarget::where('student_id', $student->id)
            ->where('week_start_date', $weekStart->toDateString())
            ->get();
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

        // Seed per-week occupancy from what's already on the books.
        $week = $weekStart->copy();
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

            // If every week to the exam is full, the module simply isn't placed
            // this run — it re-surfaces as carry next Sunday.
            if (! $placed) {
                break;
            }
        }
    }
}
