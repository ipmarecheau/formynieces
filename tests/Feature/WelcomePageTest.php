<?php

use App\Models\User;

it('shows guest CTAs on the landing page for visitors', function () {
    $this->get('/')
        ->assertOk()
        ->assertSeeText('Sign In')
        ->assertSeeText('Sign up free')
        ->assertSeeText('Book a free call');
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
        ->assertDontSeeText('Book a free 15-minute call') // guest hero CTA hidden
        ->assertDontSeeText('Create an account')           // no signup prompt when logged in
        ->assertDontSeeText('Sign In');                    // no sign-in prompt when logged in
});
