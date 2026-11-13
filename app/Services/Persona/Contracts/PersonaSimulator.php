<?php

namespace App\Services\Persona\Contracts;

use App\Services\Persona\PersonaResult;

/**
 * Drives one persona through the live app and reports what it found and what it cost.
 *
 * The real implementation (BrowserPersonaSimulator) drives Playwright against staging and
 * uses an LLM to reason about the experience and write findings — that is the live engine,
 * exercised on the schedule, not in the test suite, which binds a FakePersonaSimulator.
 *
 * @see formynieces-spec/features/persona_agent.feature
 */
interface PersonaSimulator
{
    public function run(string $persona): PersonaResult;
}
