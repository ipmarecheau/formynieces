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

it('opens for an email sign-up whose name is still the email placeholder, even before a child, and saves the real name', function () {
    // Mirrors the email sign-up path: name defaults to the email's local part.
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'email' => 'maria.thomas@example.com',
        'name' => 'maria.thomas',   // the placeholder RegisteredUserController sets
        'email_verified_at' => now(),
    ]);

    Livewire::actingAs($guardian)
        ->test(FamilyProfileWizard::class)
        ->assertSet('open', true)            // opens to gather the real name
        ->set('name', 'Maria Thomas')
        ->call('save')
        ->assertSet('open', false)
        ->assertHasNoErrors();

    expect($guardian->fresh()->name)->toBe('Maria Thomas');
});

it('requires a name to save', function () {
    [$guardian] = fpwGuardianWithChild();

    Livewire::actingAs($guardian)
        ->test(FamilyProfileWizard::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors('name')
        ->assertSet('open', true);
});
