<?php

namespace App\Services\Diagnostic;

use Illuminate\Support\Facades\DB;

/**
 * ItemWalk — step 2b of the diagnostic engine.
 *
 * Drives the adaptive loop over a session's item_plan: resolves the current
 * slot to a concrete anchor at the strand's CURRENT difficulty, presents it,
 * records the answer into diagnostic_responses, steps the strand's difficulty
 * (climb on correct, descend on wrong), and advances the cursor.
 *
 * DIFFICULTY LADDER (decision D4):
 *   - Three rungs: easy(1) medium(2) hard(3). Each strand starts at medium.
 *   - Correct  -> that strand steps one rung harder (capped at hard).
 *   - Wrong    -> that strand steps one rung easier (floored at easy).
 *   - The next slot in the SAME strand is resolved at the strand's new rung.
 *   - Difficulty is tracked PER STRAND, in the session's item_plan meta, so a
 *     strong Number performance doesn't make Fractions harder.
 *
 * SLOT RESOLUTION uses SessionPlanner::resolveSlot with nearest-available
 * difficulty and exclusion of already-used anchors. If a slot cannot resolve
 * (strand exhausted), the walk SKIPS it and advances — no error, no repeat.
 *
 * This service is the bridge: it produces the response rows that
 * MasteryInference later reads at session completion. It does NOT itself write
 * student_progress — that is the lifecycle layer's job (step 2e).
 */
class ItemWalk
{
    public function __construct(
        private SessionPlanner $planner,
    ) {}

    /**
     * Return the current question to present, resolving the current slot to a
     * concrete anchor at the strand's current difficulty. Returns null when the
     * plan is exhausted (the session is ready to complete).
     *
     * @return array{
     *     anchor_id:int, prompt:string, options:array<string>,
     *     strand:string, subject:string, difficulty:int,
     *     item_number:int, total_items:int
     * }|null
     */
    public function currentQuestion(int $sessionId): ?array
    {
        $session = $this->session($sessionId);
        $plan = $this->plan($session);
        $cursor = (int) $session->current_item;

        $used = $this->usedAnchorIds($sessionId);
        $strandLevels = $this->strandLevels($session);

        // Advance past any slots that cannot resolve (exhausted strands).
        while ($cursor < count($plan)) {
            $slot = $plan[$cursor];
            $slot['difficulty'] = $strandLevels[$this->strandKey($slot)] ?? $slot['difficulty'];

            $anchorId = $this->planner->resolveSlot($slot, $used);

            if ($anchorId !== null) {
                $anchor = DB::table('anchor_questions')->find($anchorId);

                return [
                    'anchor_id'   => $anchorId,
                    'prompt'      => $anchor->prompt,
                    'options'     => json_decode($anchor->options, true),
                    'strand'      => $anchor->strand,
                    'subject'     => $anchor->subject,
                    'difficulty'  => (int) $anchor->difficulty,
                    'item_number' => $cursor + 1,
                    'total_items' => count($plan),
                ];
            }

            // Unresolvable slot — skip it and persist the advanced cursor.
            $cursor++;
            DB::table('diagnostic_sessions')->where('id', $sessionId)
                ->update(['current_item' => $cursor, 'updated_at' => now()]);
        }

        return null; // plan exhausted
    }

    /**
     * Record an answer for the current item, step the strand's difficulty, and
     * advance the cursor. Returns the recorded response summary.
     *
     * @return array{is_correct:bool, misconception:?string, advanced_to:int}
     */
    public function submitAnswer(int $sessionId, int $anchorId, int $chosenIndex): array
    {
        $anchor = DB::table('anchor_questions')->find($anchorId);
        if ($anchor === null) {
            throw new \RuntimeException("Anchor {$anchorId} not found.");
        }

        $isCorrect = (int) $anchor->correct_index === $chosenIndex;

        // The chosen distractor's misconception (null when correct or unmapped).
        $misconception = null;
        if (! $isCorrect) {
            $notes = json_decode($anchor->distractor_notes, true);
            $misconception = $notes['misconceptions'][(string) $chosenIndex]
                ?? $notes['misconceptions'][$chosenIndex]
                ?? null;
        }

        DB::table('diagnostic_responses')->insert([
            'diagnostic_session_id' => $sessionId,
            'anchor_question_id'    => $anchorId,
            'chosen_index'          => $chosenIndex,
            'is_correct'            => $isCorrect,
            'misconception'         => $misconception,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        // The strand's difficulty rung is DERIVED from response history (see
        // strandLevels), so recording the response above is all that's needed to
        // update it — no separate level store to write. Advance the cursor.
        $session = $this->session($sessionId);
        $cursor = (int) $session->current_item + 1;

        DB::table('diagnostic_sessions')->where('id', $sessionId)->update([
            'current_item' => $cursor,
            'updated_at'   => now(),
        ]);

        return [
            'is_correct'    => $isCorrect,
            'misconception' => $misconception,
            'advanced_to'   => $cursor,
        ];
    }

    /** True when an encouragement interstitial is due (every 8th answered item). */
    public function interstitialDue(int $sessionId): bool
    {
        $answered = DB::table('diagnostic_responses')
            ->where('diagnostic_session_id', $sessionId)
            ->count();

        return $answered > 0 && $answered % 8 === 0;
    }

    // ---- internals ----

    private function session(int $sessionId): object
    {
        $s = DB::table('diagnostic_sessions')->find($sessionId);
        if ($s === null) {
            throw new \RuntimeException("Diagnostic session {$sessionId} not found.");
        }

        return $s;
    }

    /** @return array<int, array{subject:string, strand:string, difficulty:int}> */
    private function plan(object $session): array
    {
        return json_decode($session->item_plan ?? '[]', true) ?: [];
    }

    /**
     * Per-strand current difficulty, DERIVED from the session's response
     * history. Keeping this state in the responses (rather than a separate
     * column) means it is always consistent and resume works for free: replay
     * the answers, stepping each strand's rung up on a correct and down on a
     * wrong, starting from medium.
     *
     * @return array<string,int>  "subject|strand" => current difficulty rung
     */
    private function strandLevels(object $session): array
    {
        $levels = [];

        $responses = DB::table('diagnostic_responses as r')
            ->join('anchor_questions as a', 'a.id', '=', 'r.anchor_question_id')
            ->where('r.diagnostic_session_id', $session->id)
            ->orderBy('r.id')
            ->get(['a.subject', 'a.strand', 'r.is_correct']);

        foreach ($responses as $resp) {
            $key = $resp->subject . '|' . $resp->strand;
            $current = $levels[$key] ?? SessionPlanner::DIFFICULTY_MEDIUM;
            $levels[$key] = $resp->is_correct
                ? min(SessionPlanner::DIFFICULTY_HARD, $current + 1)
                : max(SessionPlanner::DIFFICULTY_EASY, $current - 1);
        }

        return $levels;
    }

    private function strandKey(array $slot): string
    {
        return $slot['subject'] . '|' . $slot['strand'];
    }

    /** @return array<int> */
    private function usedAnchorIds(int $sessionId): array
    {
        return DB::table('diagnostic_responses')
            ->where('diagnostic_session_id', $sessionId)
            ->pluck('anchor_question_id')
            ->all();
    }
}
