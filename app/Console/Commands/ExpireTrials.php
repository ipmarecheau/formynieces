<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Falls lapsed trials back to the free plan (lead_capture.feature LG-08). Access already
 * treats an expired trial as free defensively; this makes the state durable.
 */
class ExpireTrials extends Command
{
    protected $signature = 'trials:expire';

    protected $description = 'Fall lapsed trial accounts back to the free plan';

    public function handle(): int
    {
        $count = User::query()
            ->where('plan', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->update(['plan' => 'free', 'trial_ends_at' => null]);

        $this->info("Expired {$count} trial(s) back to free.");

        return self::SUCCESS;
    }
}
