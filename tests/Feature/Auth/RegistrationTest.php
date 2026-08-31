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

test('check-email reports whether an account already exists', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson(route('register.check-email'), ['email' => 'taken@example.com'])
        ->assertOk()
        ->assertJson(['exists' => true]);

    // Case-insensitive: the stored email is matched regardless of casing.
    $this->postJson(route('register.check-email'), ['email' => 'TAKEN@example.com'])
        ->assertOk()
        ->assertJson(['exists' => true]);

    $this->postJson(route('register.check-email'), ['email' => 'free@example.com'])
        ->assertOk()
        ->assertJson(['exists' => false]);
});

test('registering with an existing email is rejected with a sign-in message', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'taken@example.com',
        'phone' => '+18685551234',
        'password' => 'password',
        'password_confirmation' => 'password',
        'age_attestation' => true,
        'terms' => '1',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'An account with this email already exists. Please sign in to your dashboard instead.',
    ]);
    $this->assertGuest();
});
