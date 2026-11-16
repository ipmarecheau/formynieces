<?php

namespace App\Console\Commands;

use App\Mail\ScenarioChangedNotice;
use App\Models\VerificationRun;
use App\Services\UserTesting\ScenarioChangeLog;
use App\Services\UserTesting\ScenarioFingerprint;
use App\Services\UserTesting\TestingCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Once a day, finds answered test-drive verdicts whose scenario has changed since — in its BDD
 * definition, its implementation, or both — and emails the tester to re-test, explaining what
 * moved. Each distinct change is emailed once (tracked by change_notified_hash), so testers are
 * nudged on real changes without being overwhelmed. Runs daily by design, not minute-by-minute.
 */
class FlagChangedScenarios extends Command
{
    protected $signature = 'verification:flag-changes';

    protected $description = 'Email testers to re-test scenarios whose spec or implementation changed since their verdict';

    public function handle(ScenarioFingerprint $fingerprint, ScenarioChangeLog $changeLog): int
    {
        $answered = [
            VerificationRun::RESULT_WORKED,
            VerificationRun::RESULT_ISSUE,
            VerificationRun::RESULT_SKIPPED,
        ];

        $runs = VerificationRun::whereIn('result', $answered)
            ->whereNotNull('spec_hash')
            ->get();

        $sent = 0;

        foreach ($runs as $run) {
            $specNow = $fingerprint->specHash($run->scenario_id);
            $implNow = $fingerprint->implHash(TestingCatalog::sources($run->scenario_id));

            $specChanged = $specNow !== null && $run->spec_hash !== $specNow;
            $implChanged = $run->impl_hash !== null && $implNow !== null && $run->impl_hash !== $implNow;

            if (! $specChanged && ! $implChanged) {
                continue;
            }

            // Notify once per distinct post-change state; re-notify only if it changes again.
            $combined = ($specNow ?? '-').'|'.($implNow ?? '-');
            if ($run->change_notified_hash === $combined) {
                continue;
            }

            $summary = $changeLog->summarise($run, $specChanged, $implChanged);
            Mail::to($run->tester_email)->send(new ScenarioChangedNotice($run, $summary));

            $run->forceFill(['change_notified_hash' => $combined])->save();
            $sent++;
        }

        $this->info("Flagged {$sent} changed scenario(s) to testers.");

        return self::SUCCESS;
    }
}
