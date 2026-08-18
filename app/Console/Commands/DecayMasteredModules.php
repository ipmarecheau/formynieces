<?php

namespace App\Console\Commands;

use App\Services\Practice\MaintenanceDecay;
use Illuminate\Console\Command;

/**
 * LL-17 — the weekly review that slips an un-maintained mastered level to review.
 */
class DecayMasteredModules extends Command
{
    protected $signature = 'practice:decay-maintenance';

    protected $description = 'Decay mastered levels whose maintenance grace has passed to mastered_review (LL-17)';

    public function handle(MaintenanceDecay $decay): int
    {
        $count = $decay->run();

        $this->info("Decayed {$count} mastered level(s) to review.");

        return self::SUCCESS;
    }
}
