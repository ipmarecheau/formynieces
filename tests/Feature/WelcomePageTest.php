<?php

use App\Models\User;

it('shows guest CTAs on the landing page for visitors', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Sign In')
        ->assertSee('Sign up free')
        ->assertSee('Book a free call');
});

it('shows the user greeting and a logout on the landing page when authenticated', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'name' => 'Aaliyah Thomas',
        'onboarding_completed_at' => now(),
    ]);

    $this->actingAs($student)->get('/')
        ->assertOk()
        ->assertSee('Aaliyah')          // greeting shows her first name
        ->assertSee('Log out')
        ->assertSee('My Dashboard')
        ->assertDontSeeText('Book a free 15-minute call') // guest hero CTA hidden
        ->assertDontSeeText('Create an account')           // no signup prompt when logged in
        ->assertDontSeeText('Sign In');                    // no sign-in prompt when logged in
});
