<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

/**
 * TR-02/03/04 — the first-run application tour, guided from the Voyage home.
 *
 * Auto-opens on a student's first Voyage visit (once she has been welcomed), steps
 * through chapters spotlighting each area, awaits her click to advance, and never
 * nags: once finished it stays closed (persisted in users.seen_guides as "tour").
 * The "Take the tour" control reopens it any time (TR-04). Content is child-layer,
 * from config/tour.php.
 */
class VoyageTour extends Component
{
    public bool $open = false;

    public function mount(): void
    {
        // First visit after being welcomed auto-opens; a finished tour stays closed.
        $user = auth()->user();
        $this->open = $user !== null
            && $user->hasBeenWelcomed()
            && ! $user->hasSeenGuide('tour');
    }

    /** Reopen on demand from the "Take the tour" control (TR-04). */
    #[On('start-tour')]
    public function start(): void
    {
        $this->open = true;
    }

    /** Finish (or skip) — remember it so it never nags again (TR-03). */
    public function finish(): void
    {
        auth()->user()?->markGuideSeen('tour');
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
