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
    public bool $isComplete = false;

    public string $strand = '';
    public string $islandName = '';
    public string $islandIcon = '';
    public int $itemNumber = 0;
    public int $planTotal = 0;

    public function mount(): void
    {
        $this->sessionId = app(SessionLifecycle::class)->startOrResume(auth()->id());
        $this->planTotal = $this->resolvePlanTotal();
        $this->loadCurrent();
    }

    protected function walk(): ItemWalk
    {
        return new ItemWalk(new SessionPlanner);
    }

    protected function lifecycle(): SessionLifecycle
    {
        return app(SessionLifecycle::class);
    }

    protected function resolvePlanTotal(): int
    {
        $session = DB::table('diagnostic_sessions')->find($this->sessionId);
        $plan = json_decode($session->item_plan ?? '[]', true);

        return is_array($plan) ? count($plan) : 0;
    }

    protected function loadCurrent(): void
    {
        $this->showInterstitial = $this->walk()->interstitialDue($this->sessionId);
        $this->question = $this->walk()->currentQuestion($this->sessionId);

        if ($this->question === null) {
            // Plan walked — derive + persist the mastery map, mark completed.
            // complete() is idempotent; the status guard avoids redundant calls.
            $session = DB::table('diagnostic_sessions')->find($this->sessionId);
            if ($session !== null && $session->status !== 'completed') {
                $this->lifecycle()->complete($this->sessionId);
            }

            $this->isComplete = true;
            $this->prompt = '';
            $this->options = [];
            $this->strand = '';
            $this->islandName = '';
            $this->islandIcon = '';
            return;
        }

        $anchor = DB::table('anchor_questions')->find($this->question['anchor_id']);
        $this->prompt = $anchor->prompt;
        $this->options = json_decode($anchor->options, true);

        $this->strand = $this->question['strand'] ?? '';
        $this->itemNumber = $this->question['item_number'] ?? 0;
        [$this->islandName, $this->islandIcon] = $this->islandFor($this->strand, $anchor->subject ?? '');
    }

    protected function islandFor(string $strand, string $subject): array
    {
        $storyCove = ['Comprehension', 'Poetry', 'Media'];
        $wordHarbour = ['Spelling', 'Punctuation', 'Capitalisation', 'Grammar'];

        if ($subject === 'Writing' || $strand === 'Writing') {
            return ["Writer's Bay", '🪶'];
        }
        if (in_array($strand, $storyCove, true)) {
            return ['Story Cove', '📖'];
        }
        if (in_array($strand, $wordHarbour, true)) {
            return ['Word Harbour', '✏️'];
        }

        return ['Number Isle', '🔢'];
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