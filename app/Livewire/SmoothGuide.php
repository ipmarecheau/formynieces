<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * Smooth's guide — a contextual how-to for a single student screen (SG-01..05).
 *
 * Auto-opens on a student's first visit to the screen, can always be reopened from
 * a help control, and never nags: once dismissed it stays closed (persisted per
 * student in users.seen_guides). Content comes from config/guides.php and is
 * child-layer only — never pace, percentages, or targets.
 */
class SmoothGuide extends Component
{
    /** The guide key, e.g. "practice" or "voyage". */
    public string $guide;

    public bool $open = false;

    /** An alert guide (e.g. a due review) opens proactively, regardless of prior dismissal. */
    public bool $alert = false;

    private const POSE_FILES = [
        'wave' => 'smooth.webp',
        'cheer' => 'smooth-cheer.webp',
        'chart' => 'smooth-chart.webp',
    ];

    public function mount(string $guide, bool $alert = false): void
    {
        $this->guide = $guide;
        $this->alert = $alert;
        // An alert (a due review) always opens; otherwise first visit auto-opens and a
        // previously dismissed guide stays closed (SG-01/02).
        $this->open = $alert || ! (auth()->user()?->hasSeenGuide($guide) ?? true);
    }

    /** Dismiss and remember, so it never nags again (SG-02). */
    public function dismiss(): void
    {
        auth()->user()?->markGuideSeen($this->guide);
        $this->open = false;
    }

    /** Reopen on demand from the help control (SG-02). */
    public function reopen(): void
    {
        $this->open = true;
    }

    /**
     * @return array{title:string, pose:string, lines:array<int,string>}|null
     */
    public function content(): ?array
    {
        return config("guides.{$this->guide}");
    }

    public function avatarUrl(string $pose): string
    {
        $file = self::POSE_FILES[$pose] ?? self::POSE_FILES['wave'];

        return asset("images/voyage/companion/{$file}");
    }

    public function render()
    {
        return view('livewire.smooth-guide');
    }
}
