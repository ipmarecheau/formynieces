<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Scrubs a STAGING database restored from a production snapshot: anonymises real PII,
 * resets every real login to one shared staging password, and clears PII-bearing
 * prospect tables. Outbound (mail/LLM/payments) is neutered by the staging env, not here.
 *
 * SAFETY: refuses to run unless the app environment is exactly 'staging'. This can never
 * touch production (APP_ENV=production) even if invoked by accident.
 */
class SanitizeStaging extends Command
{
    protected $signature = 'staging:sanitize {--password=staging-walkthrough : shared password set on every real account}';

    protected $description = 'Anonymise PII on a staging DB copied from production (staging env only)';

    public function handle(): int
    {
        if (! app()->environment('staging')) {
            $this->error('REFUSING: staging:sanitize only runs when APP_ENV=staging (current: '.app()->environment().').');

            return self::FAILURE;
        }

        $password = Hash::make((string) $this->option('password'));
        $scrubbed = 0;

        // Anonymise REAL accounts. is_test (QA/agent) accounts keep their known
        // credentials so the walkthrough agent can still log in on staging.
        User::where('is_test', false)->cursor()->each(function (User $u) use ($password, &$scrubbed) {
            $label = $u->role === 'guardian' ? 'Parent' : 'Student';
            $u->forceFill([
                'name' => "{$label} {$u->id}",
                'email' => "user{$u->id}@staging.invalid",
                'phone' => null,
                'phone_verified_at' => null,
                'social_provider' => null,
                'social_id' => null,
                'password' => $password,
                'child_password_enc' => $u->role === 'student' ? $this->option('password') : null,
                'remember_token' => null,
                'current_school' => null,
            ])->save();
            $scrubbed++;
        });

        // Clear prospect/PII tables not needed to walk the product.
        foreach (['leads', 'contact_messages', 'chat_conversations', 'chat_messages'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        $this->info("Sanitised {$scrubbed} real account(s); cleared prospect/PII tables.");
        $this->line('All real logins now use the shared staging password; QA (is_test) accounts unchanged.');

        return self::SUCCESS;
    }
}
