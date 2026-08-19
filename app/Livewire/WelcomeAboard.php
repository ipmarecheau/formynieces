<?php

namespace App\Livewire;

use App\Models\StreakReward;
use App\Services\Motivation\StreakEconomyService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * TR-01/TR-05 — the first-login welcome. Smooth welcomes a new voyager aboard and
 * drops one of every perk into her Captain's Locker as a joining gift. Fires exactly
 * once (guarded by users.welcomed_at); revisiting just shows the greeting again and
 * flows on to her Voyage, where the tour runs.
 */
#[Layout('components.layouts.diagnostic')]
class WelcomeAboard extends Component
{
    /**
     * The perks granted, for display. Order matches the Locker.
     *
     * @var array<int, array{type:string, label:string, icon:string, blurb:string}>
     */
    public array $perks = [];

    public function mount(StreakEconomyService $economy): void
    {
        $user = auth()->user();

        // Grant the joining bonus exactly once, atomically with the welcomed stamp.
        if ($user !== null && ! $user->hasBeenWelcomed()) {
            DB::transaction(function () use ($user, $economy) {
                $economy->grantJoiningBonus($user->id);
                $user->forceFill(['welcomed_at' => now()])->save();
            });
        }

        $this->perks = [
            ['type' => 'shore_leave', 'label' => 'Shore Leave', 'icon' => '🏖️', 'blurb' => 'Skip one duty for a day, streak safe.'],
            ['type' => 'anchor', 'label' => 'Anchor', 'icon' => '⚓', 'blurb' => 'Freeze every streak for a day off.'],
            ['type' => 'tailwind', 'label' => 'Tailwind', 'icon' => '🌬️', 'blurb' => 'Sail ahead and bank an extra day.'],
            ['type' => 'lifebuoy', 'label' => 'Lifebuoy', 'icon' => '🛟', 'blurb' => 'Rescue a streak that just reset.'],
        ];
    }

    public function avatarUrl(): string
    {
        return asset('images/voyage/companion/smooth-cheer.webp');
    }

    public function render()
    {
        // Reference the constant so the perk list and the model stay in lockstep.
        assert(count($this->perks) === count(StreakReward::TYPES));

        return view('livewire.welcome-aboard');
    }
}
