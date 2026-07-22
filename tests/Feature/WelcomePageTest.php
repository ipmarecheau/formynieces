<?php

use App\Models\User;

it('shows guest CTAs on the landing page for visitors', function () {
    $this->get('/')
        ->assertOk()
        ->assertSeeText('Sign In')
        ->assertSeeText('Start Learning Free')
        ->assertSeeText('Get Started');
});

it('shows the user greeting and a logout on the landing page when authenticated', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'name' => 'Aaliyah Thomas',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($student)->get('/')
        ->assertOk()
        ->assertSeeText('Aaliyah')          // greeting shows her first name
        ->assertSeeText('Log out')
        ->assertSeeText('My Dashboard')
        ->assertDontSeeText('Start Learning Free') // guest hero CTA hidden
        ->assertDontSeeText('Sign In');            // no sign-in prompt when logged in
});
