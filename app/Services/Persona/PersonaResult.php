<?php

namespace App\Services\Persona;

/**
 * What one persona run produced: a human summary, what it cost, and the findings it raised.
 *
 * @see formynieces-spec/features/persona_agent.feature (PA-01, PA-05)
 */
final class PersonaResult
{
    /**
     * @param  list<array{severity:string, area:string, observation:string, suggestion?:string}>  $findings
     */
    public function __construct(
        public readonly string $summary,
        public readonly float $costUsd,
        public readonly array $findings = [],
    ) {}
}
