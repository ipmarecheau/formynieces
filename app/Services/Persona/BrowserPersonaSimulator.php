<?php

namespace App\Services\Persona;

use App\Services\Persona\Contracts\PersonaSimulator;
use RuntimeException;

/**
 * The live engine: drives Playwright against the staging mirror and uses an LLM to reason
 * about the experience and write findings, metering its spend against the monthly budget.
 *
 * This is the deferred live piece (like the real push transports). Until it is implemented,
 * it throws, and {@see PersonaTestingService} records the run as failed rather than crashing
 * the schedule. The test suite binds a FakePersonaSimulator in its place.
 *
 * @see formynieces-spec/features/persona_agent.feature
 */
class BrowserPersonaSimulator implements PersonaSimulator
{
    public function run(string $persona): PersonaResult
    {
        throw new RuntimeException(
            'Live persona engine not yet wired (Playwright + LLM against '
            .config('services.persona_agent.base_url').'). See docs/operations/persona-agent.md.'
        );
    }
}
