<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ModuleStageCompletion;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * onboard:demo — provision a demo guardian + child at a chosen onboarding lifecycle state, so the
 * E2E walkthrough, demos and manual QC can jump straight to any point without clicking through setup.
 */
class OnboardDemoCommand extends Command
{
    protected $signature = 'onboard:demo {--state=complete : fresh|added|mid-diagnostic|diagnostic-done|complete}';

    protected $description = 'Provision a demo guardian + child at a chosen onboarding lifecycle state.';

    private const STATES = ['fresh', 'added', 'mid-diagnostic', 'diagnostic-done', 'complete'];

    public function handle(): int
    {
        $state = (string) $this->option('state');
        if (! in_array($state, self::STATES, true)) {
            $this->error("Unknown state '{$state}'. Use one of: ".implode(', ', self::STATES));

            return self::FAILURE;
        }

        $slug = Str::lower(Str::random(4));
        $guardianPassword = 'demo-'.Str::random(6);
        $guardian = User::create([
            'name' => 'Demo Guardian',
            'email' => "demo+{$slug}@smoothseas.test",
            'password' => Hash::make($guardianPassword),
            'role' => 'guardian',
        ]);
        // email_verified_at is guarded from mass assignment — set the onboarding stamps directly.
        $guardian->forceFill([
            'email_verified_at' => now(),
            'age_attested_at' => now(),
            'terms_accepted_at' => now(),
        ])->save();

        $child = null;
        if ($state !== 'fresh') {
            $childPassword = Str::random(10);
            $child = User::create([
                'name' => 'Maya',
                'email' => "maya.{$slug}@smoothseas.test",
                'password' => Hash::make($childPassword),
                'role' => 'student',
                'parent_id' => $guardian->id,
                'target_sea_year' => (int) now()->addYear()->year,
            ]);
            $child->child_password_enc = $childPassword;
            $child->save();
        }

        if ($state === 'mid-diagnostic') {
            $child->diagnosticSessions()->create(['status' => 'in_progress']);
        }

        if (in_array($state, ['diagnostic-done', 'complete'], true)) {
            $child->diagnosticSessions()->create(['status' => 'completed', 'completed_at' => now()]);
        }

        if ($state === 'complete') {
            $module = SyllabusModule::query()->inRandomOrder()->first() ?? SyllabusModule::factory()->create();
            ModuleStageCompletion::create([
                'student_id' => $child->id, 'module_id' => $module->id, 'stage' => 'lesson', 'completed_at' => now(),
            ]);
            $guardian->forceFill(['onboarding_completed_at' => now()])->save();
        }

        $this->info("Demo family provisioned — state: {$state}");
        $this->line("Guardian: {$guardian->email}  /  {$guardianPassword}");
        if ($child !== null) {
            $this->line("Child:    {$child->email}  /  {$child->child_password_enc}");
        }

        return self::SUCCESS;
    }
}
