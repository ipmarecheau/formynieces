<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Creates (idempotently) the synthetic QA family the walkthrough agent drives:
 * one guardian + three children at mixed reading levels, all flagged is_test so
 * they never pollute real analytics and can be removed cleanly.
 *
 * Credentials are deterministic (a shared password passed in) so the agent can log in.
 * Accounts act through the UI only — never the database.
 */
class SeedQaAccounts extends Command
{
    protected $signature = 'qa:seed-accounts {--password= : Shared password for the QA accounts (required)}';

    protected $description = 'Create/refresh the QA guardian + children (is_test) for the walkthrough agent';

    public function handle(): int
    {
        $password = (string) $this->option('password');
        if ($password === '') {
            $this->error('Pass --password=<strong-password>. Aborting.');

            return self::FAILURE;
        }

        $guardian = User::updateOrCreate(
            ['email' => 'qa-parent@qa.smoothseas.org'],
            [
                'name' => 'QA Parent',
                'password' => Hash::make($password),
                'role' => 'guardian',
                'is_test' => true,
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'age_attested_at' => now(),
                'onboarding_completed_at' => now(),
            ],
        );

        $children = [
            ['name' => 'QA Child Low', 'email' => 'qa-child-low@qa.smoothseas.org', 'reading_level' => 4, 'target_sea_year' => now()->addYears(2)->year],
            ['name' => 'QA Child Mid', 'email' => 'qa-child-mid@qa.smoothseas.org', 'reading_level' => 5, 'target_sea_year' => now()->addYear()->year],
            ['name' => 'QA Child High', 'email' => 'qa-child-high@qa.smoothseas.org', 'reading_level' => 6, 'target_sea_year' => now()->addYear()->year],
        ];

        foreach ($children as $c) {
            $child = User::updateOrCreate(
                ['email' => $c['email']],
                [
                    'name' => $c['name'],
                    'password' => Hash::make($password),
                    'role' => 'student',
                    'parent_id' => $guardian->id,
                    'is_test' => true,
                    'reading_level' => $c['reading_level'],
                    'target_sea_year' => $c['target_sea_year'],
                    'onboarding_completed_at' => now(),
                    'guardian_reconciled_at' => now(),
                ],
            );
            $child->child_password_enc = $password; // so the guardian's reveal/handoff shows it
            $child->save();
        }

        $this->info('QA accounts ready (is_test):');
        $this->line('  guardian: qa-parent@qa.smoothseas.org');
        foreach ($children as $c) {
            $this->line("  child:    {$c['email']}  (reading level {$c['reading_level']})");
        }
        $this->line('  password: (as provided)');

        return self::SUCCESS;
    }
}
