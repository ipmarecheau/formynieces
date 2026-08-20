<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * TR-07 — the island leg of the interactive tour. When a student on tour reaches
 * an island (tour_stage 'overworld' or 'island'), Smooth points out the stops and
 * asks her to tap the first one to open its lesson, where LessonTour continues.
 */
class IslandTour extends Component
{
    public bool $open = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->open = in_array($user?->tour_stage, ['overworld', 'island'], true);
        // Advancing the tour to this leg as she arrives.
        if ($this->open && $user?->tour_stage !== 'island') {
            $user->setTourStage('island');
        }
    }

    /** Skip the whole tour from here. */
    public function skip(): void
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
        return view('livewire.island-tour');
    }
}
