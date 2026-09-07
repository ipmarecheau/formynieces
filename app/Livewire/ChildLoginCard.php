<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

/**
 * ChildLoginCard — the always-findable "your child's login" card on the guardian dashboard.
 *
 * Shows the child's login ID and reveals the password on tap (never by default). Only ever renders a
 * child that belongs to the authenticated guardian. This is the fix for "finding the child login is
 * impossible" — it lives on Home, one tap away, instead of buried behind the Children's-logins page.
 */
class ChildLoginCard extends Component
{
    public int $childId;

    public bool $revealed = false;

    public bool $showOtherDevice = false;

    public function mount(int $childId): void
    {
        $this->childId = $childId;
    }

    public function toggleReveal(): void
    {
        $this->revealed = ! $this->revealed;
    }

    public function toggleOtherDevice(): void
    {
        $this->showOtherDevice = ! $this->showOtherDevice;
    }

    public function render()
    {
        $child = User::query()
            ->where('id', $this->childId)
            ->where('parent_id', auth()->id())
            ->firstOrFail();

        return view('livewire.child-login-card', [
            'child' => $child,
            'password' => $this->revealed ? $child->child_password_enc : null,
        ]);
    }
}
