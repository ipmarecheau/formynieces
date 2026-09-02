<?php

namespace App\Livewire;

use App\Models\Lead;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * The placement-report funnel (lead_capture.feature) — a parent gives their email (and
 * optionally a WhatsApp number) for a free SEA mock and a personalised first-choice
 * placement report. Capture → mock → report, all before they ever hold an account.
 *
 * This component owns capture (LG-01/02/13). The mock (LG-03) and report (LG-04..07) are
 * driven by the phases below and their collaborating services.
 */
#[Layout('components.layouts.marketing')]
class PlacementReport extends Component
{
    /** capture | mock | report */
    public string $phase = 'capture';

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('nullable|string|max:40')]
    public string $whatsapp = '';

    #[Validate('required|string')]
    public string $childLevel = '';

    public ?int $leadId = null;

    public function mount(): void
    {
        // A returning lead skips capture (LG-13) — straight to their mock or report.
        $leadId = session('lead_id');
        $lead = $leadId ? Lead::find($leadId) : null;

        if ($lead !== null) {
            $this->leadId = $lead->id;
            $this->phase = $lead->hasReport() ? 'report' : 'mock';
        }
    }

    /** Capture the lead and move into the mock (LG-02). */
    public function beginMock(): void
    {
        $this->validate();

        $lead = Lead::create([
            'email' => $this->email,
            'whatsapp' => $this->whatsapp !== '' ? $this->whatsapp : null,
            'child_level' => $this->childLevel,
            'source' => 'placement-report',
        ]);

        session(['lead_id' => $lead->id]);
        $this->leadId = $lead->id;
        $this->phase = 'mock';
    }

    public function render()
    {
        return view('livewire.placement-report');
    }
}
