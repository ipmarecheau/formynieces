<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\Diagnostic\DiagnosticReconciliation;
use App\Services\Diagnostic\ItemWalk;
use App\Services\Diagnostic\SessionLifecycle;
use App\Services\Diagnostic\SessionPlanner;
use App\Services\Pacing\RoadmapGenerator;
use App\Support\IslandTaxonomy;
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

    public bool $awaitingGuardian = false;

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
        $this->redirectIfAwaitingGuardian();
    }

    /**
     * A completed diagnostic whose result awaits the guardian's decision sends
     * her to the waiting page rather than the map link. Called after every
     * loadCurrent() — completion can happen on mount (already-walked session) or
     * on the final answer via choose(). [RR-11]
     */
    protected function redirectIfAwaitingGuardian(): void
    {
        if ($this->awaitingGuardian) {
            // A hard redirect (NOT wire:navigate): the waiting page is a
            // standalone non-Livewire document, and an SPA navigate to it
            // silently no-ops, leaving the student on the completion card. [RR-11]
            $this->redirect(route('student.awaiting-guardian'));
        }
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

                // Generate her roadmap (journey + first weekly target) from the
                // completed diagnostic. Kept OUTSIDE complete() so the guardian
                // reconciliation step slots in between: when the diagnostic
                // cleared a strand the guardian flagged, generation waits until
                // she reconciles (or the 3-day auto-proceed resolves it). [RR-04]
                $student = User::find($session->student_id);
                if ($student !== null && $student->target_sea_year !== null
                    && ! app(DiagnosticReconciliation::class)->requiresGuardianDecision($student)) {
                    app(RoadmapGenerator::class)->generate($student);
                }
            }

            $this->isComplete = true;

            // Hold the reveal when the diagnostic cleared a strand the guardian
            // flagged: her map waits on the guardian's decision. [RR-04]
            $revealStudent = $session !== null ? User::find($session->student_id) : null;
            $this->awaitingGuardian = $revealStudent !== null
                && app(DiagnosticReconciliation::class)->isPending($revealStudent);

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
        return IslandTaxonomy::resolve($strand, $subject);
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
        $this->redirectIfAwaitingGuardian();
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
