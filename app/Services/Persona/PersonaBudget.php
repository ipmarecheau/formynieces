<?php

namespace App\Services\Persona;

use App\Models\PersonaRun;

/**
 * The persona agent's own monthly USD budget (PA-02) — separate from the per-student LLM
 * caps. Month-to-date spend is the sum of this month's run costs; once it reaches the cap,
 * no further run is allowed until the month rolls over.
 *
 * @see formynieces-spec/features/persona_agent.feature
 */
class PersonaBudget
{
    public function cap(): float
    {
        return (float) config('services.persona_agent.monthly_cap_usd');
    }

    public function spentThisMonth(): float
    {
        return (float) PersonaRun::query()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('cost_usd');
    }

    public function remaining(): float
    {
        return max(0.0, $this->cap() - $this->spentThisMonth());
    }

    public function canRun(): bool
    {
        return $this->spentThisMonth() < $this->cap();
    }
}
