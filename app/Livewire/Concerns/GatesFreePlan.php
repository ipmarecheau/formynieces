<?php

namespace App\Livewire\Concerns;

/**
 * Guards a full-page student surface behind a paid plan. A free-plan account is sent to
 * the contextual upgrade wall naming the surface it tried to reach (free_tier.feature).
 *
 * Call from mount() and return early when it redirects:
 *     if ($this->gateFreePlan('lesson')) { return; }
 */
trait GatesFreePlan
{
    /**
     * Redirect a free-plan viewer to the upgrade wall for the given surface.
     * Returns true when a redirect was issued so the caller can stop mounting.
     */
    protected function gateFreePlan(string $unlock): bool
    {
        $user = auth()->user();

        if ($user !== null && $user->onFreePlan()) {
            $this->redirect(route('upgrade', ['unlock' => $unlock]), navigate: true);

            return true;
        }

        return false;
    }
}
