<?php

use App\Livewire\FamilyProfileWizard;
use App\Models\User;
use Livewire\Livewire;

function fpwGuardianWithChild(): array
{
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now(), 'phone' => null]);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id]);

    return [$guardian, $child];
}

it('opens for a guardian with a child who has not dismissed it', function () {
    [$guardian] = fpwGuardianWithChild();

    Livewire::actingAs($guardian)
        ->test(FamilyProfileWizard::class)
        ->assertSet('open', true)
        ->assertSee('finish your profile');
});

it('saves the phone and the child\'s weak areas, then closes and never nags again', function () {
    [$guardian, $child] = fpwGuardianWithChild();

    Livewire::actingAs($guardian)
        ->test(FamilyProfileWizard::class)
        ->set('phone', '+18685551234')
        ->set('weakAreas', ['Fractions', 'Reading Comprehension'])
        ->call('save')
        ->assertSet('open', false);

    expect($guardian->fresh()->phone)->toBe('+18685551234')
        ->and($child->fresh()->known_weak_areas)->toBe(['Fractions', 'Reading Comprehension']);

    // Dismissed for good — a fresh mount stays closed.
    Livewire::actingAs($guardian->fresh())
        ->test(FamilyProfileWizard::class)
        ->assertSet('open', false);
});

it('rejects a malformed phone number', function () {
    [$guardian] = fpwGuardianWithChild();

    Livewire::actingAs($guardian)
        ->test(FamilyProfileWizard::class)
        ->set('phone', 'not-a-number')
        ->call('save')
        ->assertHasErrors('phone')
        ->assertSet('open', true);
});

it('can be skipped without saving anything, and stays closed after', function () {
    [$guardian] = fpwGuardianWithChild();

    Livewire::actingAs($guardian)
        ->test(FamilyProfileWizard::class)
        ->call('skip')
        ->assertSet('open', false);

    expect($guardian->fresh()->phone)->toBeNull();

    Livewire::actingAs($guardian->fresh())
        ->test(FamilyProfileWizard::class)
        ->assertSet('open', false);
});

it('does not open when the guardian has no child yet', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);

    Livewire::actingAs($guardian)
        ->test(FamilyProfileWizard::class)
        ->assertSet('open', false);
});
