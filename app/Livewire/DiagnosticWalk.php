<?php

namespace App\Livewire;

use App\Services\Diagnostic\ItemWalk;
use App\Services\Diagnostic\SessionLifecycle;
use App\Services\Diagnostic\SessionPlanner;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.diagnostic')]
class DiagnosticWalk extends Component
{
    public int $sessionId;
    public ?array $question = null;
    public string $prompt = '';
    public array $options = [];
    public bool $showInterstitial = false;

    public function mount(): void
    {
        // Resolve the authenticated student's own session. No id from the URL.
        $this->sessionId = app(SessionLifecycle::class)->startOrResume(auth()->id());
        $this->loadCurrent();
    }

    protected function walk(): ItemWalk
    {
        return new ItemWalk(new SessionPlanner);
    }

    protected function loadCurrent(): void
    {
        $this->showInterstitial = $this->walk()->interstitialDue($this->sessionId);

        $this->question = $this->walk()->currentQuestion($this->sessionId);

        if ($this->question === null) {
            $this->prompt = '';
            $this->options = [];
            return;
        }

        $anchor = DB::table('anchor_questions')->find($this->question['anchor_id']);
        $this->prompt = $anchor->prompt;
        $this->options = json_decode($anchor->options, true);
    }

    public function choose(int $index): void
    {
        if ($this->question === null) {
            return;
        }

        $this->walk()->submitAnswer(
            $this->sessionId,
            $this->question['anchor_id'],
            $index,
        );

        $this->loadCurrent();
    }

    public function continueFromInterstitial(): void
    {
        $this->showInterstitial = false;
    }

    public function render()
    {
        return view('livewire.diagnostic-walk');
    }
}