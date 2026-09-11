<?php

namespace App\Livewire;

use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * FamilyProfileWizard — Phase 2 of onboarding: the just-in-time "finish your profile" card.
 *
 * The 3-step login spine deliberately collects the bare minimum, so anything non-essential is
 * gathered here after both accounts work: the guardian's (optional) phone and the child's known
 * weak areas (which feed the diagnostic reconciliation). Fully skippable — it never blocks — and
 * once completed or dismissed it stays closed (tracked via users.seen_guides, like SmoothGuide).
 */
class FamilyProfileWizard extends Component
{
    private const GUIDE_KEY = 'profile_wizard';

    public bool $open = false;

    public string $name = '';

    public ?string $phone = null;

    /** @var array<int,string> Selected weak-area strands for the focus child. */
    public array $weakAreas = [];

    public function mount(): void
    {
        $guardian = auth()->user();
        $child = $this->child();

        $this->name = $guardian?->name ?? '';
        $this->phone = $guardian?->phone;
        $this->weakAreas = $child?->known_weak_areas ?? [];

        // Open (until dismissed) when there is something to gather: a child to profile,
        // or a guardian whose name is still the email-derived placeholder from sign-up
        // (email sign-ups arrive without a real name; social sign-ups get it from the provider).
        $this->open = ! ($guardian?->hasSeenGuide(self::GUIDE_KEY) ?? true)
            && ($child !== null || $this->nameIsPlaceholder($guardian));
    }

    /** True when the guardian's name is still the local part of their email (never personalised). */
    private function nameIsPlaceholder(?User $guardian): bool
    {
        return $guardian !== null
            && $guardian->name === Str::before($guardian->email, '@');
    }

    /** The first child — the one this profile wizard is about. */
    private function child(): ?User
    {
        return auth()->user()?->students()->orderBy('id')->first();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'weakAreas' => ['array'],
            'weakAreas.*' => ['string', 'max:100'],
        ], [
            'name.required' => 'Please tell us your name.',
            'phone.regex' => 'Enter your phone in full international format, e.g. +18685551234.',
        ]);

        $guardian = auth()->user();
        $guardian->forceFill([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?: null,
        ])->save();

        if (($child = $this->child()) !== null) {
            $child->forceFill(['known_weak_areas' => $validated['weakAreas']])->save();
        }

        $guardian->markGuideSeen(self::GUIDE_KEY);
        $this->open = false;
    }

    /** Skip for now — never nag again. */
    public function skip(): void
    {
        auth()->user()?->markGuideSeen(self::GUIDE_KEY);
        $this->open = false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function strandsBySubject(): array
    {
        return SyllabusModule::strandsBySubject();
    }

    public function render()
    {
        return view('livewire.family-profile-wizard');
    }
}
