<?php

declare(strict_types=1);

namespace App\Services\Diagnostic;

/**
 * The graph-driven diagnostic planner (retires the adaptive anchor engine).
 *
 * The diagnostic asks the HARDEST (D5) question for a competency and leans on the
 * prerequisite graph to ask the MINIMUM number of questions: a correct D5 answer
 * infers the whole prerequisite chain is mastered (via MasteryInference), so those
 * modules are never asked; a wrong answer descends to probe the prerequisites.
 *
 * Stateless: the caller holds the answers so far and asks for the next module to
 * probe. Question SELECTION (which D5 item, unseen) is the caller's job — this
 * class only decides WHICH competency to test next, and the final inferred map.
 */
class GraphDiagnostic
{
    private MasteryInference $inference;

    /**
     * @param  array<int, array<int>>  $prerequisites  module_id => [prerequisite ids]
     * @param  array<int, int>  $allModuleIds  every competency the diagnostic may cover
     */
    public function __construct(
        private array $prerequisites,
        private array $allModuleIds,
    ) {
        $this->inference = new MasteryInference($prerequisites);
    }

    /**
     * The next competency to probe with a D5 question, or null when every module is
     * resolved (directly answered or inferred). Picks the unresolved module whose
     * probing would resolve the MOST still-unknown prerequisites — terminals first.
     *
     * @param  list<array{module_id:int, is_correct:bool}>  $answers
     */
    public function nextModule(array $answers): ?int
    {
        $map = $this->map($answers);

        $unresolved = array_values(array_filter(
            $this->allModuleIds,
            fn (int $m): bool => ($map[$m] ?? MasteryInference::STATUS_NOT_STARTED) === MasteryInference::STATUS_NOT_STARTED
        ));

        if ($unresolved === []) {
            return null;
        }

        usort($unresolved, function (int $a, int $b) use ($map): int {
            $coverageA = $this->unresolvedAncestorCount($a, $map);
            $coverageB = $this->unresolvedAncestorCount($b, $map);

            return $coverageB <=> $coverageA                                   // most coverage first
                ?: count($this->ancestors($b)) <=> count($this->ancestors($a)) // then deepest
                ?: $a <=> $b;                                                    // stable
        });

        return $unresolved[0];
    }

    public function isComplete(array $answers): bool
    {
        return $this->nextModule($answers) === null;
    }

    /**
     * The final inferred status per module (module_id => status). D5 answers, so
     * correct answers propagate mastery to their prerequisite closure.
     *
     * @param  list<array{module_id:int, is_correct:bool}>  $answers
     * @return array<int, string>
     */
    public function map(array $answers): array
    {
        $responses = collect($answers)->map(fn (array $a): object => (object) [
            'module_id' => $a['module_id'],
            'is_correct' => $a['is_correct'],
            'difficulty' => 5, // the diagnostic always asks the hardest level
        ]);

        return $this->inference->deriveMap($responses);
    }

    private function unresolvedAncestorCount(int $module, array $map): int
    {
        return count(array_filter(
            $this->ancestors($module),
            fn (int $a): bool => ($map[$a] ?? MasteryInference::STATUS_NOT_STARTED) === MasteryInference::STATUS_NOT_STARTED
        ));
    }

    /** Transitive prerequisites of a module. */
    private function ancestors(int $module): array
    {
        $seen = [];
        $stack = $this->prerequisites[$module] ?? [];
        while ($stack !== []) {
            $m = array_pop($stack);
            if (isset($seen[$m])) {
                continue;
            }
            $seen[$m] = true;
            foreach ($this->prerequisites[$m] ?? [] as $p) {
                $stack[] = $p;
            }
        }

        return array_keys($seen);
    }
}
