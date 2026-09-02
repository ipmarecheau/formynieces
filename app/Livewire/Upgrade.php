<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The upgrade wall — where every free-plan lock leads (free_tier.feature). It is always
 * CONTEXTUAL: it names the exact surface the viewer tried to reach and what unlocking it
 * gives them, offers a one-tap upgrade, and offers a way back to the free surfaces they
 * can still use. Never a generic dead end (FP-17).
 */
#[Layout('components.layouts.diagnostic')]
class Upgrade extends Component
{
    /** The surface key the viewer was gated from (e.g. lesson, writing, rituals, pace, ai). */
    public string $unlock = 'lesson';

    public function mount(?string $unlock = null): void
    {
        $this->unlock = $unlock ?? request()->query('unlock', 'lesson');
    }

    /**
     * Contextual copy per gated surface.
     *
     * @return array{eyebrow:string,title:string,blurb:string,back:string,backRoute:string}
     */
    public function surface(): array
    {
        $map = [
            'lesson' => ['The lesson', 'Let Smooth teach this island', 'On the free plan you can test yourself, but the step-by-step lesson — where Smooth actually teaches the concept — is part of the full voyage.'],
            'tutorial' => ['The worked examples', 'See Smooth work it through', 'Smooth walks through worked examples, one step at a time, on the full voyage.'],
            'reteach' => ['Smooth’s re-teach', 'Missed it? Smooth can re-teach it', 'When something doesn’t click, Smooth re-teaches it until it does — that’s the heart of the full plan.'],
            'explainer' => ['The lesson', 'Let Smooth teach this island', 'The teaching side of every island opens up on the full voyage.'],
            'writing' => ['The daily writing', 'Unlock the daily writing track', 'Daily writing with kind, specific feedback is part of the full voyage.'],
            'rituals' => ['Morning Tide', 'Unlock the daily rituals', 'The morning vocabulary and reading ritual is part of the full voyage.'],
            'pace' => ['Pace & placement', 'Unlock pace & the placement projection', 'See whether they’re on pace and their projected first-choice placement on the full plan.'],
            'estimator' => ['Placement projection', 'Unlock the placement projection', 'The projected first-choice SEA placement is part of the full plan.'],
            'ai' => ['Smooth’s help', 'Unlock Smooth’s help', 'Ask-Smooth and the AI helpers are part of the full voyage.'],
            'reporting' => ['Your full report', 'Unlock the honest layer', 'The weekly readiness picture, pace and placement are part of the full plan.'],
        ];

        [$eyebrow, $title, $blurb] = $map[$this->unlock] ?? ['The full voyage', 'Unlock the full voyage', 'This is part of the full SmoothSeas voyage.'];

        // Guardians land back on their dashboard; students back on the map.
        $isGuardian = auth()->user()?->role === 'guardian';

        return [
            'eyebrow' => $eyebrow,
            'title' => $title,
            'blurb' => $blurb,
            'back' => $isGuardian ? 'Back to your dashboard' : 'Back to the map',
            'backRoute' => $isGuardian ? 'guardian.dashboard' : 'student.voyage',
        ];
    }

    public function render()
    {
        return view('livewire.upgrade', ['s' => $this->surface()]);
    }
}
