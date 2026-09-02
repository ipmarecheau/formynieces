<?php

namespace App\Livewire;

use App\Models\Lead;
use App\Services\Funnel\TrialProvisioner;
use Livewire\Component;

/**
 * The finished placement report shown on-screen (LG-04): the readiness band, the three
 * weakest strands and the one next step, a shareable "SEA-Ready" card the parent can
 * share themselves (LG-06), and the call to action — a full month free + the AI practice
 * pack (LG-07). Claiming the offer is handled by the trial flow.
 */
class PlacementReportResult extends Component
{
    public int $leadId;

    public bool $weeklyOptIn = false;

    public function mount(int $leadId): void
    {
        $this->leadId = $leadId;
        $this->weeklyOptIn = (bool) ($this->lead?->weekly_opt_in ?? false);
    }

    /** Opt into / out of the weekly SEA Question nurture email (LG-10). */
    public function updatedWeeklyOptIn(bool $value): void
    {
        $this->lead?->update(['weekly_opt_in' => $value]);
    }

    public function getLeadProperty(): ?Lead
    {
        return Lead::find($this->leadId);
    }

    /** Claim the offer: provision a one-month trial + email the practice pack (LG-07/08/09). */
    public function claimTrial(TrialProvisioner $provisioner)
    {
        $lead = $this->lead;

        if ($lead === null) {
            return null;
        }

        if ($lead->converted_at === null) {
            $provisioner->fromLead($lead);
        }

        session()->flash('trial_started', "Your free month has started — we've emailed {$lead->email} a link to set your password and your AI practice pack.");

        return $this->redirect(route('login'), navigate: true);
    }

    public function render()
    {
        return view('livewire.placement-report-result', ['lead' => $this->lead]);
    }
}
