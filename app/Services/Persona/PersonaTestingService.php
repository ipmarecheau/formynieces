<?php

namespace App\Services\Persona;

use App\Models\PersonaRun;
use App\Services\Persona\Contracts\PersonaSimulator;

/**
 * Orchestrates the nightly persona simulations (PA-01..03): for each persona, if the monthly
 * budget allows, drive the app and record a run with its findings; otherwise record a
 * budget-skipped run. A failed run is recorded, never thrown, so one persona never stops the
 * other.
 *
 * @see formynieces-spec/features/persona_agent.feature
 */
class PersonaTestingService
{
    public const PERSONAS = [
        PersonaRun::PERSONA_ANXIOUS_PARENT,
        PersonaRun::PERSONA_CHILD_FULL_DAY,
    ];

    public function __construct(
        private readonly PersonaBudget $budget,
        private readonly PersonaSimulator $simulator,
    ) {}

    /**
     * @return list<PersonaRun>
     */
    public function runNightly(): array
    {
        $runs = [];

        foreach (self::PERSONAS as $persona) {
            if (! $this->budget->canRun()) {
                $runs[] = PersonaRun::create([
                    'persona' => $persona,
                    'status' => PersonaRun::STATUS_SKIPPED_BUDGET,
                    'summary' => 'Skipped — monthly persona budget reached.',
                    'started_at' => now(),
                    'finished_at' => now(),
                ]);

                continue;
            }

            $runs[] = $this->runOne($persona);
        }

        return $runs;
    }

    private function runOne(string $persona): PersonaRun
    {
        $run = PersonaRun::create([
            'persona' => $persona,
            'status' => PersonaRun::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        try {
            $result = $this->simulator->run($persona);

            foreach ($result->findings as $finding) {
                $run->findings()->create([
                    'severity' => $finding['severity'] ?? 'medium',
                    'area' => $finding['area'] ?? 'general',
                    'observation' => $finding['observation'] ?? '',
                    'suggestion' => $finding['suggestion'] ?? null,
                ]);
            }

            $run->update([
                'status' => PersonaRun::STATUS_COMPLETED,
                'summary' => $result->summary,
                'cost_usd' => $result->costUsd,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $run->update([
                'status' => PersonaRun::STATUS_FAILED,
                'summary' => 'Run failed: '.$e->getMessage(),
                'finished_at' => now(),
            ]);
        }

        return $run->fresh();
    }
}
