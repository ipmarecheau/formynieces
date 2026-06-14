<?php

use App\Models\User;

it('registers a guardian with an 18+ attestation', function () {
    $page = visit('/register');

    $page->type('#name', 'Test Guardian')
         ->type('#email', 'guardian@example.test')
         ->type('#password', 'password123')
         ->type('#password_confirmation', 'password123')
         ->check('#age_attestation')
         ->click('#submit');

    // Allow the POST + redirect to settle, then assert the destination.
    $page->assertPathContains('verify-email');

    $this->assertAuthenticated();

    $user = User::where('email', 'guardian@example.test')->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('guardian')
        ->and($user->age_attested_at)->not->toBeNull()
        ->and($user->hasVerifiedEmail())->toBeFalse();
});

it('redirects an unverified guardian away from child setup', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
        'email_verified_at' => null, // unverified
    ]);

    $page = visit('/child-setup')->actingAs($guardian);

    $page->navigate('/child-setup')
         ->assertPathContains('verify-email');
});