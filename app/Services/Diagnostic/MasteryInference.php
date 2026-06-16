<?php

namespace App\Services\Diagnostic;

use Illuminate\Support\Collection;

/**
 * MasteryInference — the conservative propagation core of the diagnostic engine.
 *
 * Given a student's diagnostic RESPONSES (one per answered anchor) and the
 * prerequisite graph, this service derives the mastery map: a status per module.
 * It is a PURE function of its inputs — no DB writes, no session state — so it is
 * trivially testable and order-independent. The session layer calls deriveMap()
 * at completion and persists the result into student_progress.
 *
 * DESIGN DECISIONS (locked, see diagnostic_engine_build_plan.md):
 *   D3  Only a CORRECT answer on a MEDIUM-or-HARDER anchor (difficulty >= 2)
 *       propagates inferred mastery to prerequisites. An easy correct answer
 *       still marks its own module mastered, but does not infer downward
 *       (too weak to rule out a lucky guess).
 *   D6  Statuses: not_started | mastered | inferred_mastered | needs_work.
 *       - mastered:          directly tested, answered correctly
 *       - inferred_mastered: not directly tested, implied via the graph
 *       - needs_work:        directly tested, answered incorrectly
 *       - not_started:       never touched by the diagnostic
 *   Walk-back: a WRONG answer on a harder anchor un-marks inferred mastery on
 *       the modules that anchor requires (its prerequisite closure). Direct
 *       'mastered' from an easier correct answer is NOT un-marked — only
 *       inference is walked back. This is the conservative rule: contradicting
 *       evidence higher up removes the *guess*, not the *fact*.
 *   Writing firewall: propagation never passes THROUGH a writing node (69-72).
 *       Writing modules are mastered only by their own anchors; they are never
 *       a conduit in either direction.
 *
 * Determinism: deriveMap() recomputes the entire map from the full response set,
 * so the order responses arrive in cannot corrupt the result.
 */
class MasteryInference
{
    private const WRITING_NODES = [69, 70, 71, 72];

    private const DIFFICULTY_MEDIUM = 2;

    public const STATUS_NOT_STARTED = 'not_started';

    public const STATUS_MASTERED = 'mastered';

    public const STATUS_INFERRED = 'inferred_mastered';

    public const STATUS_NEEDS_WORK = 'needs_work';

    /**
     * @param  array<int, array<int>>  $prerequisites  module_id => [prerequisite_module_id, ...]
     *         The directed graph "B requires A". Build once from module_prerequisites.
     */
    public function __construct(
        private array $prerequisites,
    ) {}

    /**
     * Derive the full mastery map from a set of responses.
     *
     * @param  Collection<int, object>  $responses  Each item must expose:
     *         ->module_id (int, the anchor's target module),
     *         ->difficulty (int 1-3),
     *         ->is_correct (bool).
     *         (The session layer joins anchor_questions + pivot to produce these.)
     * @return array<int, string>  module_id => status, only for modules the
     *         diagnostic touched. Untouched modules are absent (caller treats as
     *         not_started).
     */
    public function deriveMap(Collection $responses): array
    {
        $direct = [];     // module_id => bool correct  (directly tested)
        $inferred = [];   // module_id => true          (implied mastered)
        $contradicted = []; // module_id => true        (un-marked by walk-back)

        // Pass 1 — direct results. A module directly tested takes its own result;
        // if tested more than once, a single correct counts as mastered (the
        // hardest correct evidence wins).
        foreach ($responses as $r) {
            $m = $r->module_id;
            if (! array_key_exists($m, $direct)) {
                $direct[$m] = (bool) $r->is_correct;
            } elseif ($r->is_correct) {
                $direct[$m] = true;
            }
        }

        // Pass 2 — forward inference. Each correct medium+ answer infers the
        // prerequisite closure of its module (writing firewall applied).
        foreach ($responses as $r) {
            if (! $r->is_correct) {
                continue;
            }
            if ((int) $r->difficulty < self::DIFFICULTY_MEDIUM) {
                continue; // D3: easy correct does not propagate
            }
            foreach ($this->closure($r->module_id) as $prereq) {
                $inferred[$prereq] = true;
            }
        }

        // Pass 3 — conservative walk-back. Each WRONG answer on a harder anchor
        // contradicts inference along the modules it requires: un-mark those
        // (inference only — never a directly-earned 'mastered').
        foreach ($responses as $r) {
            if ($r->is_correct) {
                continue;
            }
            foreach ($this->closure($r->module_id) as $prereq) {
                $contradicted[$prereq] = true;
            }
        }

        // Compose final statuses.
        $map = [];

        // Direct results first — these are facts, never overridden by inference.
        foreach ($direct as $m => $correct) {
            $map[$m] = $correct ? self::STATUS_MASTERED : self::STATUS_NEEDS_WORK;
        }

        // Inferred mastery fills in modules NOT directly tested and NOT
        // contradicted by a harder failure.
        foreach (array_keys($inferred) as $m) {
            if (array_key_exists($m, $direct)) {
                continue; // a fact already stands here
            }
            if (isset($contradicted[$m])) {
                continue; // walked back
            }
            $map[$m] = self::STATUS_INFERRED;
        }

        return $map;
    }

    /**
     * Transitive prerequisite closure of a module, with the WRITING FIREWALL:
     * a writing node (69-72) is never a conduit. We never traverse into a
     * writing node, and we never expand a writing node's own prerequisites —
     * even when the writing node is the START. Thus a correct Writing anchor
     * masters only its own module (handled by the caller) and infers nothing,
     * and inference from a poetry module above never reaches a writing node.
     *
     * @return array<int>  prerequisite module ids (the start module not included)
     */
    private function closure(int $start): array
    {
        // A writing start node has no inferable prerequisites — firewall.
        if (in_array($start, self::WRITING_NODES, true)) {
            return [];
        }

        $seen = [];
        $stack = [$start];

        while ($stack) {
            $node = array_pop($stack);

            foreach ($this->prerequisites[$node] ?? [] as $prereq) {
                // Never traverse into a writing node (blocks reaching it AND
                // blocks passing through it to whatever it requires).
                if (in_array($prereq, self::WRITING_NODES, true)) {
                    continue;
                }
                if (! isset($seen[$prereq])) {
                    $seen[$prereq] = true;
                    $stack[] = $prereq;
                }
            }
        }

        return array_keys($seen);
    }

    /**
     * Build a MasteryInference from the module_prerequisites table rows.
     *
     * @param  iterable<object>  $edges  rows with ->module_id and ->prerequisite_module_id
     */
    public static function fromEdges(iterable $edges): self
    {
        $graph = [];
        foreach ($edges as $e) {
            $graph[$e->module_id][] = $e->prerequisite_module_id;
        }

        return new self($graph);
    }
}