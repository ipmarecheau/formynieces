<?php

use App\Models\User;

it('serves a kid-branded student sign-in at /go that posts to the shared login', function () {
    $this->get(route('student.login'))
        ->assertOk()
        ->assertSee('Sign in to your voyage')
        ->assertSee('action="'.route('login').'"', false);
});

it('cross-links the parent login to the student sign-in', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee(route('student.login'), false);
});

it('lets an authenticated guardian view the child sign-in without bouncing to the dashboard', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id]);

    $this->actingAs($guardian)
        ->get(route('student.login'))
        ->assertOk()                              // not a redirect to the dashboard
        ->assertSee('signed in as a parent');     // explicit parent-vs-child guidance
});
