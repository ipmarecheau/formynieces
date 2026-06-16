<?php

namespace App\Services\Diagnostic;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * SessionPlanner — step 2a of the diagnostic engine.
 *
 * Builds the item_plan for a new session: an ordered list of SLOTS, each a
 * (subject, strand, difficulty) target. Slots are resolved to concrete anchor
 * ids at WALK TIME (by the item walk, step 2b), so difficulty can adapt to the
 * student's performance — this is decision D2 (hybrid: fixed sequence, late
 * binding) from the build plan.
 *
 * Allocation (per diagnostic.feature "plans items per the 50/30/20 weighting"):
 *   Math   ~15 anchors across the four strands, Number heaviest.
 *   ELA    even Section I (mechanics) / Section II (comprehension) split.
 *   Writing  a small set across its four concept modules.
 *
 * Every slot STARTS at medium difficulty (the middle rung of the climb/descend
 * ladder). The walk steps a strand harder on a correct answer, easier on a
 * wrong one. Because some strands lack a given difficulty tier (e.g. Percent
 * has only a hard anchor), slot resolution uses NEAREST-available difficulty —
 * see resolveSlot().
 *
 * The planner is deterministic given the same inputs (and an optional shuffle
 * seed), so tests can assert an exact plan.
 */
class SessionPlanner
{
    public const DIFFICULTY_EASY = 1;

    public const DIFFICULTY_MEDIUM = 2;

    public const DIFFICULTY_HARD = 3;

    /**
     * Math strand allocation — totals 15, Number heaviest. Keys are strands as
     * stored in anchor_questions.strand; values are the slot count per strand.
     * "Percent" and "Problem Solving" are thin in the bank, so Number/Geometry/
     * Measurement carry the weight, matching the real SEA emphasis.
     */
    private const MATH_ALLOCATION = [
        'Number'          => 5,
        'Fractions'       => 2,
        'Decimals'        => 1,
        'Percent'         => 1,
        'Problem Solving' => 1,
        'Geometry'        => 2,
        'Measurement'     => 2,
        'Statistics'      => 1,
    ]; // = 15

    /**
     * ELA allocation — even Section I / Section II split (6 + 6 = 12). Section I
     * strands: Spelling, Punctuation, Capitalisation, Grammar. Section II:
     * Comprehension, Poetry, Media.
     */
    private const ELA_SECTION_I = [
        'Spelling'       => 2,
        'Punctuation'    => 1,
        'Capitalisation' => 1,
        'Grammar'        => 2,
    ]; // = 6

    private const ELA_SECTION_II = [
        'Comprehension' => 2,
        'Poetry'        => 2,
        'Media'         => 2,
    ]; // = 6

    /** Writing — one slot per concept module (4). */
    private const WRITING_SLOTS = 4;

    /**
     * Build the ordered slot plan. Returns an array of slot arrays, each:
     *   ['subject' => 'Math', 'strand' => 'Number', 'difficulty' => 2]
     *
     * Order: Math block, then ELA (Section I then II), then Writing — strands
     * interleaved within each block so the child isn't asked many near-identical
     * items in a row. (Interleaving is cosmetic ordering; allocation is fixed.)
     *
     * @return array<int, array{subject:string, strand:string, difficulty:int}>
     */
    public function buildPlan(): array
    {
        $math = $this->slotsFor('Math', self::MATH_ALLOCATION);
        $elaI = $this->slotsFor('ELA', self::ELA_SECTION_I);
        $elaII = $this->slotsFor('ELA', self::ELA_SECTION_II);
        $writing = $this->writingSlots();

        return [
            ...$this->interleave($math),
            ...$this->interleave($elaI),
            ...$this->interleave($elaII),
            ...$writing,
        ];
    }

    /**
     * Persist a freshly built plan onto a session row and return it.
     * Stores the slot list as JSON in diagnostic_sessions.item_plan and resets
     * the cursor. Does not change status (the lifecycle layer owns that).
     */
    public function planForSession(int $sessionId): array
    {
        $plan = $this->buildPlan();

        DB::table('diagnostic_sessions')
            ->where('id', $sessionId)
            ->update([
                'item_plan'    => json_encode($plan, JSON_UNESCAPED_UNICODE),
                'current_item' => 0,
                'updated_at'   => now(),
            ]);

        return $plan;
    }

    /**
     * Resolve a slot to a concrete anchor id at walk time, choosing the anchor
     * at the requested difficulty if available, else the NEAREST available
     * difficulty in that (subject, strand). Excludes anchor ids already used in
     * this session so a child never sees the same item twice.
     *
     * @param  array{subject:string, strand:string, difficulty:int}  $slot
     * @param  array<int>  $usedAnchorIds
     */
    public function resolveSlot(array $slot, array $usedAnchorIds = []): ?int
    {
        $candidates = DB::table('anchor_questions')
            ->where('subject', $slot['subject'])
            ->where('strand', $slot['strand'])
            ->where('is_active', true)
            ->when($usedAnchorIds !== [], fn ($q) => $q->whereNotIn('id', $usedAnchorIds))
            ->get(['id', 'difficulty']);

        if ($candidates->isEmpty()) {
            return null;
        }

        $target = $slot['difficulty'];

        // Order by distance to the target difficulty, then by difficulty asc as
        // a stable tiebreak, then id for determinism.
        return $candidates
            ->sortBy([
                fn ($a, $b) => abs($a->difficulty - $target) <=> abs($b->difficulty - $target),
                fn ($a, $b) => $a->difficulty <=> $b->difficulty,
                fn ($a, $b) => $a->id <=> $b->id,
            ])
            ->first()
            ->id;
    }

    /**
     * Produce medium-difficulty slots for an allocation map.
     *
     * @param  array<string,int>  $allocation  strand => count
     * @return Collection<int, array{subject:string, strand:string, difficulty:int}>
     */
    private function slotsFor(string $subject, array $allocation): Collection
    {
        $slots = collect();
        foreach ($allocation as $strand => $count) {
            for ($i = 0; $i < $count; $i++) {
                $slots->push([
                    'subject'    => $subject,
                    'strand'     => $strand,
                    'difficulty' => self::DIFFICULTY_MEDIUM,
                ]);
            }
        }

        return $slots;
    }

    /** One Writing slot per concept module's strand label (all 'Writing'). */
    private function writingSlots(): array
    {
        $slots = [];
        for ($i = 0; $i < self::WRITING_SLOTS; $i++) {
            $slots[] = [
                'subject'    => 'Writing',
                'strand'     => 'Writing',
                'difficulty' => self::DIFFICULTY_MEDIUM,
            ];
        }

        return $slots;
    }

    /**
     * Round-robin interleave slots by strand so identical strands aren't
     * adjacent. Deterministic: preserves first-seen strand order.
     *
     * @param  Collection<int, array{subject:string, strand:string, difficulty:int}>  $slots
     * @return array<int, array{subject:string, strand:string, difficulty:int}>
     */
    private function interleave(Collection $slots): array
    {
        $byStrand = $slots->groupBy('strand');
        $queues = $byStrand->map(fn ($g) => $g->values()->all())->all();

        $result = [];
        $remaining = array_sum(array_map('count', $queues));
        $strandOrder = array_keys($queues);

        while ($remaining > 0) {
            foreach ($strandOrder as $strand) {
                if (! empty($queues[$strand])) {
                    $result[] = array_shift($queues[$strand]);
                    $remaining--;
                }
            }
        }

        return $result;
    }
}
