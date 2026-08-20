<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * LoopCoach (TR-07) — the first-run tour's coach on the downstream learning-loop pages
 * (lesson → worked examples → practice). Dropped onto each page with its `leg`; on mount
 * it advances the student's tour position to that leg (never backwards) and shows Smooth's
 * guidance for it. Purely additive: a brand-new student on the tour sees the coach; everyone
 * else sees nothing. Skipping ends the whole tour and is remembered (TR-03).
 */
class LoopCoach extends Component
{
    /** Which loop leg this instance coaches: learn | examples | practice. */
    public string $leg;

    public bool $open = false;

    public function mount(string $leg): void
    {
        $this->leg = $leg;
        $user = auth()->user();

        if ($user?->onGuidedTour()) {
            $user->advanceTourStage($leg);
            $this->open = true;
        }
    }

    /** End the tour from any leg and remember it (TR-03). */
    public function finish(): void
    {
        $user = auth()->user();
        $user?->setTourStage('done');
        $user?->markGuideSeen('tour');
        $this->open = false;
    }

    public function avatarUrl(): string
    {
        return asset('images/voyage/companion/smooth-chart.webp');
    }

    public function render()
    {
        return view('livewire.loop-coach');
    }
}
