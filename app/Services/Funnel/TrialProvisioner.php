<?php

namespace App\Services\Funnel;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Turns a lead who claims the offer into a one-month trial account (LG-07/08). The
 * guardian account is created on the trial plan with trial_ends_at a full month out;
 * when the trial lapses the account falls back to free (handled by hasPaidAccess + the
 * expiry sweep). The AI practice pack is generated and emailed, and the team is notified.
 */
class TrialProvisioner
{
    public function __construct(
        private PracticePackService $pack,
        private PlacementReportService $report,
        private AdminNotifier $admin,
    ) {}

    public function fromLead(Lead $lead): User
    {
        $days = (int) config('funnel.trial_days', 30);

        $guardian = User::firstOrNew(['email' => $lead->email]);

        if (! $guardian->exists) {
            $guardian->name = 'Guardian';
            $guardian->password = Hash::make(Str::random(32));
            $guardian->role = 'guardian';
            $guardian->email_verified_at = now();
            $guardian->age_attested_at = now();
        }

        $guardian->plan = 'trial';
        $guardian->trial_ends_at = now()->addDays($days);
        $guardian->save();

        $lead->update([
            'converted_user_id' => $guardian->id,
            'converted_at' => now(),
        ]);

        // Email the AI practice pack (LG-09), then invite them to set a password.
        try {
            $pdfPath = $this->pack->renderPdf($lead->child_level);
            $this->report->deliver($lead->fresh(), [
                ['path' => $pdfPath, 'name' => 'SEA-Practice-Pack.pdf'],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Practice pack delivery failed: '.$e->getMessage());
        }

        // Let them set their password and sign in (a mail hiccup must not block the claim).
        try {
            Password::sendResetLink(['email' => $guardian->email]);
        } catch (\Throwable $e) {
            Log::warning('Trial set-password link failed: '.$e->getMessage());
        }

        $this->admin->leadConverted($lead);

        return $guardian;
    }
}
