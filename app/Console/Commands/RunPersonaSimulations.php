<?php

namespace App\Console\Commands;

use App\Models\PersonaRun;
use App\Services\Persona\PersonaBudget;
use App\Services\Persona\PersonaTestingService;
use Illuminate\Console\Command;

/**
 * Nightly persona-simulation run (PA-01..03). Walks the anxious-parent and child-full-day
 * personas through the app within the monthly budget, recording findings for the team to
 * review and critique in the admin panel.
 *
 * @see formynieces-spec/features/persona_agent.feature
 */
class RunPersonaSimulations extends Command
{
    protected $signature = 'personas:run';

    protected $description = 'Run the nightly persona simulations within the monthly budget and record findings.';

    public function handle(PersonaTestingService $service, PersonaBudget $budget): int
    {
        $this->info(sprintf('Persona budget: $%.2f spent / $%.2f cap this month.', $budget->spentThisMonth(), $budget->cap()));

        $runs = $service->runNightly();

        foreach ($runs as $run) {
            $this->line(sprintf('  %-16s %-14s %d finding(s) $%.4f', $run->persona, $run->status, $run->findings()->count(), (float) $run->cost_usd));
        }

        $completed = collect($runs)->where('status', PersonaRun::STATUS_COMPLETED)->count();
        $this->info("Completed {$completed} of ".count($runs).' persona run(s).');

        return self::SUCCESS;
    }
}
