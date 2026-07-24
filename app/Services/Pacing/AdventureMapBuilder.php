<?php

declare(strict_types=1);

namespace App\Services\Pacing;

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;

/**
 * AdventureMapBuilder — the week-based adventure map (AM).
 *
 * One stop per content pacing week (1…max), each in a state of
 * completed / current / upcoming / locked, derived purely from the student's
 * position on the pacing clock — never from pace-vs-expected. The map is the
 * motivational layer: always kind, always moving forward. Pace deficits live
 * only in the guardian's exam-agent views, never here.
 */
final class AdventureMapBuilder
{
    public function __construct(
        private PacingClock $clock,
    ) {}

    /**
     * @return array<int, array{week:int, state:string, modules:array<int, array{id:int, topic:string, subject:string, status:string}>}>
     */
    public function build(User $student): array
    {
        $currentWeek = $this->clock->currentPacingWeek($student);
        $lastWeek = (int) SyllabusModule::max('pacing_week');

        $status = StudentProgress::where('student_id', $student->id)
            ->pluck('status', 'module_id');

        $modulesByWeek = SyllabusModule::orderBy('sequence_order')
            ->get(['id', 'subject', 'topic', 'pacing_week'])
            ->groupBy('pacing_week');

        $stops = [];

        for ($week = 1; $week <= $lastWeek; $week++) {
            $stops[] = [
                'week' => $week,
                'state' => $this->stateFor($week, $currentWeek),
                'modules' => ($modulesByWeek[$week] ?? collect())
                    ->map(fn (SyllabusModule $module): array => [
                        'id' => $module->id,
                        'topic' => $module->topic,
                        'subject' => $module->subject,
                        'status' => $status[$module->id] ?? 'not_started',
                    ])
                    ->values()
                    ->all(),
            ];
        }

        return $stops;
    }

    /**
     * Position-only state. Past weeks are always "visited" (completed), never
     * failed; the next week is peekable (upcoming); further weeks stay locked
     * so the trail feels calm rather than overwhelming.
     */
    private function stateFor(int $week, int $currentWeek): string
    {
        return match (true) {
            $week < $currentWeek => 'completed',
            $week === $currentWeek => 'current',
            $week === $currentWeek + 1 => 'upcoming',
            default => 'locked',
        };
    }
}
