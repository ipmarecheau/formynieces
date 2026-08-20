<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

/**
 * TR-02/03/04/07 — the overworld leg of the interactive cross-page tour.
 *
 * Auto-opens when the student's tour_stage is 'overworld' (set at welcome), steps
 * through chapters spotlighting each area, and ends on an interactive hand-off: she
 * taps her first island to sail in, where IslandTour picks the tour up. "Take the
 * tour" restarts it; skipping ends it (tour_stage → 'done', remembered so it never
 * nags). Content is child-layer, from config/tour.php.
 */
class VoyageTour extends Component
{
    public bool $open = false;

    public function mount(): void
    {
        $this->open = auth()->user()?->tour_stage === 'overworld';
    }

    /** Reopen from the "Take the tour" control — restart the whole cross-page tour (TR-04). */
    #[On('start-tour')]
    public function start(): void
    {
        auth()->user()?->setTourStage('overworld');
        $this->open = true;
    }

    /** Finish or skip the overworld leg — end the tour and remember it (TR-03). */
    public function finish(): void
    {
        $user = auth()->user();
        $user?->setTourStage('done');
        $user?->markGuideSeen('tour');
        $this->open = false;
    }

    /**
     * @return array{title:string, chapters:array<int, array<string, mixed>>}
     */
    public function tour(): array
    {
        return [
            'title' => config('tour.title'),
            'chapters' => array_values(config('tour.chapters', [])),
        ];
    }

    public function avatarUrl(string $pose): string
    {
        $files = ['wave' => 'smooth.webp', 'cheer' => 'smooth-cheer.webp', 'chart' => 'smooth-chart.webp'];

        return asset('images/voyage/companion/'.($files[$pose] ?? $files['wave']));
    }

    public function render()
    {
        return view('livewire.voyage-tour');
    }
}
