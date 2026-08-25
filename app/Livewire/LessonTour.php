<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * TR-07 (lesson leg) — the learning-loop tour. When a student on tour opens a stop
 * (tour_stage 'island' or 'lesson'), Smooth explains the loop — lesson → tutorial →
 * practice → the check — spotlighting the loop stepper, then hands her off to try it
 * and head back to her Voyage. Finishing ends the whole tour.
 */
class LessonTour extends Component
{
    public bool $open = false;

    public function mount(): void
    {
        $user = auth()->user();
        // Only during the first-run tour (or one the user re-started) — never once she has seen it,
        // so a returning student opening a lesson isn't shown the tour.
        $this->open = in_array($user?->tour_stage, ['island', 'lesson'], true)
            && ! ($user?->hasSeenGuide('tour') ?? false);
        if ($this->open && $user?->tour_stage !== 'lesson') {
            $user->setTourStage('lesson');
        }
    }

    /** Done with the tour — end it and remember it (TR-03). */
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
        return view('livewire.lesson-tour');
    }
}
