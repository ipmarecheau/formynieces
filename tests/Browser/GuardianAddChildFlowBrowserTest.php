<?php

use App\Models\User;

it('lets a verified guardian with no child log in, land on the dashboard, and add a child', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
        'email' => 'browser-guardian@example.test',
        // UserFactory default password is "password".
    ]);

    $page = visit('/login');

    $page->type('#email', 'browser-guardian@example.test')
        ->type('#password', 'password')
        ->click('button[type=submit]');

    // Lands on the guardian dashboard with the Add child empty state —
    // never the email-verification screen (her email is already verified).
    $page->assertPathContains('guardian/dashboard')
        ->assertSee('Add child')
        ->assertDontSee('verify your email');

    // Open the Add child empty state → child setup.
    $page->click('a[href*="child-setup"]')
        ->assertPathContains('child-setup');

    // Create her first child.
    $page->type('#name', 'Amara')
        ->type('#password', 'ChildPass123!')
        ->type('#password_confirmation', 'ChildPass123!')
        ->type('#target_sea_year', '2027')
        ->click('button[type=submit]');

    // The student is created and linked to the guardian, with no verification step.
    expect($guardian->students()->where('name', 'Amara')->exists())->toBeTrue();

    $page->assertSee('Amara');
})->group('scenario:GO-18');
