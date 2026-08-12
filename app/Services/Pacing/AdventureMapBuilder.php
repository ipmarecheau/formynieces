<?php

declare(strict_types=1);

namespace App\Services\Pacing;

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Support\VoyageIslands;

/**
 * AdventureMapBuilder — the syllabus adventure map, the Voyage (AM).
 *
 * A trail of 13 painted islands. The full syllabus (curriculum order) is
 * balance-chunked across the island stops, and islands are conquered in order:
 * island N unlocks once island N-1 is fully mastered. Island state is earned,
 * never scheduled, and the map carries no pace, percentage, or deficit
 * information — that lives only in the guardian's exam-agent views.
 */
final class AdventureMapBuilder
{
    /**
     * The Voyage overworld — the 13 painted islands of the sea map.
     *
     * The full syllabus (ordered by sequence_order) is balance-chunked across
     * the 13 island stops, so every island holds a contiguous run of ~7 modules
     * and the trail follows the true curriculum order. Islands are gated kindly
     * and sequentially: island N unlocks once island N-1 is fully conquered.
     * A "conquered" module is one directly mastered.
     *
     * @return array<int, array{
     *     slug:string, name:string, icon:string, x:float, y:float,
     *     conquered:int, total:int, state:string, current:bool,
     *     levels: array<int, array{id:int, topic:string, subject:string, mastered:bool, review:bool}>
     * }>
     */
    public function buildVoyage(User $student): array
    {
        $modules = SyllabusModule::orderBy('sequence_order')->get(['id', 'subject', 'topic']);
        $progressByModule = StudentProgress::where('student_id', $student->id)->get()->keyBy('module_id');

        $chunks = $this->balanceChunk($modules->all(), VoyageIslands::COUNT);
        $definitions = VoyageIslands::all();

        $islands = [];
        $previousFullyConquered = true; // island 0 is always reachable
        $currentAssigned = false;

        foreach ($definitions as $index => $definition) {
            $chunk = $chunks[$index] ?? [];

            $levels = [];
            $conquered = 0;
            foreach ($chunk as $module) {
                $progress = $progressByModule[$module->id] ?? null;
                $mastered = ($progress?->status) === 'mastered';
                $conquered += $mastered ? 1 : 0;
                $levels[] = [
                    'id' => $module->id,
                    'topic' => $module->topic,
                    'subject' => $module->subject,
                    'mastered' => $mastered,
                    'review' => $progress?->isDueForReview() ?? false,
                ];
            }

            $total = count($levels);
            $fullyConquered = $total > 0 && $conquered === $total;

            if (! $previousFullyConquered) {
                $state = 'locked';
            } elseif ($fullyConquered) {
                $state = 'mastered';
            } else {
                $state = 'playable';
            }

            // The "current" island is the first reachable, not-yet-finished stop:
            // where the boat sits and the bright trail ends.
            $current = false;
            if (! $currentAssigned && $state === 'playable') {
                $current = true;
                $currentAssigned = true;
            }

            $islands[] = [
                ...$definition,
                'conquered' => $conquered,
                'total' => $total,
                'state' => $state,
                'current' => $current,
                'levels' => $levels,
            ];

            $previousFullyConquered = $fullyConquered;
        }

        // Every island conquered: anchor the boat on the final island.
        if (! $currentAssigned && $islands !== []) {
            $islands[array_key_last($islands)]['current'] = true;
        }

        return $islands;
    }

    /**
     * Split a list into $groups contiguous chunks as evenly as possible, the
     * larger chunks first (90 across 13 -> twelve 7s then one 6).
     *
     * @template T
     *
     * @param  array<int, T>  $items
     * @return array<int, array<int, T>>
     */
    private function balanceChunk(array $items, int $groups): array
    {
        $total = count($items);
        $base = intdiv($total, $groups);
        $remainder = $total % $groups;

        $chunks = [];
        $offset = 0;
        for ($i = 0; $i < $groups; $i++) {
            $size = $base + ($i < $remainder ? 1 : 0);
            $chunks[] = array_slice($items, $offset, $size);
            $offset += $size;
        }

        return $chunks;
    }
}
