<?php

namespace App\Livewire;

use App\Services\Onboarding\OnboardingWizard as WizardService;
use Livewire\Component;

/**
 * OnboardingWizard (WZ-01..10) — the guided getting-started card on the guardian dashboard.
 *
 * Renders the family's first-run checklist from live state (via the service), highlights the single
 * next step, and never blocks: it can be minimised and reopened (WZ-08/WZ-10). Once every step is
 * done it congratulates and retires — it will not reappear unless reopened (WZ-09).
 */
class OnboardingWizard extends Component
{
    public bool $minimised = false;

    /** Force-show after the guardian reopens a retired wizard (WZ-10). */
    public bool $reopened = false;

    public function mount(): void
    {
        $this->minimised = (bool) session('onboarding_wizard_minimised', false);
    }

    public function minimise(): void
    {
        $this->minimised = true;
        session(['onboarding_wizard_minimised' => true]);
    }

    public function reopen(): void
    {
        $this->minimised = false;
        $this->reopened = true;
        session(['onboarding_wizard_minimised' => false]);
    }

    public function render()
    {
        $guardian = auth()->user();
        $wiz = WizardService::for($guardian);

        // Retired = already finished on a past visit and not explicitly reopened (WZ-09).
        $retired = $guardian->onboarding_completed_at !== null && ! $this->reopened;

        // Stamp completion the first time the whole lifecycle is done, so it retires next visit.
        $justCompleted = $wiz->retireIfComplete();

        return view('livewire.onboarding-wizard', [
            'show' => ! $retired,
            'complete' => $wiz->isComplete(),
            'justCompleted' => $justCompleted,
            'steps' => $wiz->steps(),
            'progress' => $wiz->progress(),
            'next' => $wiz->nextStep(),
        ]);
    }
}
