<?php

use App\Livewire\ChildLoginCard;
use App\Models\User;
use App\Services\Onboarding\OnboardingWizard;
use Livewire\Livewire;

/**
 * OnboardingLifecycleWalkthrough — the automated E2E walkthrough that confirms the whole first-run
 * lifecycle works from BOTH the parent's and the child's side, through the real routes (QC-08).
 * Starts from a verified guardian (registration + email verification are covered by GO-01..14).
 */
it('walks the full parent + child onboarding lifecycle end to end', function () {
    // ---- Parent: verified guardian, no child yet — next step is "add child" -------------
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    $this->actingAs($guardian);
    expect(OnboardingWizard::for($guardian)->nextStep()['key'])->toBe('child');

    // ---- Parent adds a child through the real route (captures the SEA year too) ----------
    $this->post(route('child.store'), [
        'name' => 'Jordan',
        'target_sea_year' => 2027,
    ])->assertRedirect();

    $child = $guardian->students()->firstOrFail();
    expect($child->target_sea_year)->toBe(2027);

    // Next step is now getting the child's login; it is findable on the dashboard card.
    expect(OnboardingWizard::for($guardian->refresh())->nextStep()['key'])->toBe('credentials');
    Livewire::test(ChildLoginCard::class, ['childId' => $child->id])
        ->assertSee($child->email)
        ->call('toggleReveal')
        ->assertSee($child->child_password_enc);

    // ---- Child logs in for the first time and is sent into the diagnostic (WZ-07) --------
    auth()->logout();
    $this->post(route('login'), [
        'email' => $child->email,
        'password' => $child->child_password_enc, // encrypted cast → decrypts on read
    ])->assertRedirect(route('diagnostic.intro'));

    // ---- Onboarding is complete: the child's first sign-in closed the last step ----------
    expect(OnboardingWizard::for($guardian->fresh())->isComplete())->toBeTrue()
        ->and(OnboardingWizard::for($guardian)->nextStep())->toBeNull();
});

it('a returning guardian sees the same progress on a fresh session (WZ-04)', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'target_sea_year' => 2027]);
    $this->actingAs($guardian);

    // Progress is DB-derived, so a fresh session/device shows the same state.
    expect(OnboardingWizard::for($guardian)->progress()['done'])->toBe(2) // account + child
        ->and(OnboardingWizard::for($guardian)->nextStep()['key'])->toBe('credentials');
});
