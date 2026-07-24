<?php

declare(strict_types=1);

namespace App\Services\Pacing;

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Support\IslandTaxonomy;
use Illuminate\Support\Facades\DB;

/**
 * AdventureMapBuilder — the syllabus adventure map (AM).
 *
 * A world of islands (strand-families), each holding a chain of levels — one
 * per syllabus module, ordered by depth in the prerequisite graph. A level's
 * state is earned, never scheduled:
 *   - mastered:  directly earned (student_progress status = mastered)
 *   - playable:  either engaged already (needs_work / inferred_mastered), or
 *                not yet started but every prerequisite is held
 *   - locked:    not yet started and at least one prerequisite is not held
 * "Held" (satisfies a prerequisite) = mastered OR inferred_mastered. A level
 * currently in this week's WeeklyTarget is flagged 'suggested' — a badge, never
 * a gate; every other unlocked level stays fully playable. The map never
 * carries pace, percentage, or deficit information — that lives only in the
 * guardian's exam-agent views.
 */
final class AdventureMapBuilder
{
    private const HELD_STATUSES = ['mastered', 'inferred_mastered'];

    /**
     * @return array<string, array{icon:string, levels: array<int, array{id:int, topic:string, subject:string, strand:string, state:string, suggested:bool}>}>
     *                                                                                                                                                         Keyed by island name.
     */
    public function build(User $student): array
    {
        $modules = SyllabusModule::orderBy('sequence_order')->get(['id', 'subject', 'topic', 'sequence_order']);

        $prereqsByModule = [];
        foreach (DB::table('module_prerequisites')->get(['module_id', 'prerequisite_module_id']) as $edge) {
            $prereqsByModule[$edge->module_id][] = $edge->prerequisite_module_id;
        }

        $depth = $this->computeDepths($modules->pluck('id')->all(), $prereqsByModule);

        $statusByModule = StudentProgress::where('student_id', $student->id)->pluck('status', 'module_id');
        $held = array_flip(
            $statusByModule->filter(fn ($status) => in_array($status, self::HELD_STATUSES, true))->keys()->all()
        );

        $suggested = array_flip(
            WeeklyTarget::where('student_id', $student->id)
                ->where('week_start_date', now()->startOfWeek()->toDateString())
                ->pluck('module_id')->all()
        );

        $islands = [];

        foreach ($modules as $module) {
            $strand = $this->strandFromTopic($module->topic);
            [$islandName, $icon] = IslandTaxonomy::resolve($strand, $module->subject);

            $islands[$islandName]['icon'] = $icon;
            $islands[$islandName]['levels'][] = [
                'id' => $module->id,
                'topic' => $module->topic,
                'subject' => $module->subject,
                'strand' => $strand,
                'state' => $this->stateFor(
                    $statusByModule[$module->id] ?? 'not_started',
                    $prereqsByModule[$module->id] ?? [],
                    $held,
                ),
                'suggested' => isset($suggested[$module->id]),
                '_depth' => $depth[$module->id] ?? 0,
            ];
        }

        foreach ($islands as $name => $island) {
            usort($island['levels'], fn (array $a, array $b): int => [$a['_depth'], $a['id']] <=> [$b['_depth'], $b['id']]);
            $islands[$name]['levels'] = array_map(function (array $level): array {
                unset($level['_depth']);

                return $level;
            }, $island['levels']);
        }

        return $islands;
    }

    /**
     * @param  array<int>  $modulePrereqs
     * @param  array<int, true>  $held
     */
    private function stateFor(string $status, array $modulePrereqs, array $held): string
    {
        if ($status === 'mastered') {
            return 'mastered';
        }
        if ($status === 'needs_work' || $status === 'inferred_mastered') {
            // Already engaged, or the diagnostic implied she can do it — open,
            // not locked, though not yet earned.
            return 'playable';
        }

        foreach ($modulePrereqs as $prereqId) {
            if (! isset($held[$prereqId])) {
                return 'locked';
            }
        }

        return 'playable';
    }

    /**
     * Longest-path depth from an entry point (no prerequisites), used only to
     * order each island's level chain along the true dependency structure.
     * Self-guarding against a cyclic edge (should never occur in a real
     * curriculum graph) so a bad edge degrades to a shallow depth rather than
     * recursing forever.
     *
     * @param  array<int>  $moduleIds
     * @param  array<int, array<int>>  $prereqsByModule
     * @return array<int, int>
     */
    private function computeDepths(array $moduleIds, array $prereqsByModule): array
    {
        $depth = [];
        $inProgress = [];

        $resolve = function (int $id) use (&$resolve, &$depth, &$inProgress, $prereqsByModule): int {
            if (isset($depth[$id])) {
                return $depth[$id];
            }
            if (isset($inProgress[$id])) {
                return 0; // cycle guard
            }
            $inProgress[$id] = true;

            $max = 0;
            foreach ($prereqsByModule[$id] ?? [] as $prereqId) {
                $max = max($max, 1 + $resolve($prereqId));
            }

            unset($inProgress[$id]);

            return $depth[$id] = $max;
        };

        foreach ($moduleIds as $id) {
            $resolve($id);
        }

        return $depth;
    }

    private function strandFromTopic(string $topic): string
    {
        return str_contains($topic, ':') ? trim(strstr($topic, ':', true)) : $topic;
    }
}
