<?php

namespace Tests\Support;

use App\Services\Persona\Contracts\PersonaSimulator;
use App\Services\Persona\PersonaResult;
use RuntimeException;

/**
 * An in-memory persona simulator for tests — returns canned findings + a cost without ever
 * driving a browser or calling an LLM. Can be told to throw to prove a failed run is
 * recorded, not propagated.
 *
 * @see formynieces-spec/features/persona_agent.feature
 */
class FakePersonaSimulator implements PersonaSimulator
{
    public function __construct(
        private readonly float $costPerRun = 0.05,
        private readonly bool $throwFor = false,
    ) {}

    public function run(string $persona): PersonaResult
    {
        if ($this->throwFor) {
            throw new RuntimeException('simulated engine failure');
        }

        return new PersonaResult(
            summary: "Walked the {$persona} journey; raised 2 findings.",
            costUsd: $this->costPerRun,
            findings: [
                ['severity' => 'high', 'area' => 'onboarding', 'observation' => "The {$persona} got stuck.", 'suggestion' => 'Clarify the next step.'],
                ['severity' => 'low', 'area' => 'voyage', 'observation' => 'A label was unclear.'],
            ],
        );
    }
}
