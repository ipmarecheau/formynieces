<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+18685551234',
        'password' => 'password',
        'password_confirmation' => 'password',
        'age_attestation' => true,
        'terms' => '1',
        // Turnstile passes automatically when unconfigured (test env).
    ]);

    // Registration logs the new guardian in, then sends them to verify their email.
    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice'));

    // The registrant is created as an unverified guardian who has attested their age.
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => 'guardian',
    ]);
    $user = User::where('email', 'test@example.com')->first();
    expect($user->age_attested_at)->not->toBeNull();
    expect($user->hasVerifiedEmail())->toBeFalse();
});
