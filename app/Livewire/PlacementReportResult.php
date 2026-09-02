<?php

namespace App\Livewire;

use App\Models\Lead;
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

    public function mount(int $leadId): void
    {
        $this->leadId = $leadId;
    }

    public function getLeadProperty(): ?Lead
    {
        return Lead::find($this->leadId);
    }

    public function render()
    {
        return view('livewire.placement-report-result', ['lead' => $this->lead]);
    }
}
