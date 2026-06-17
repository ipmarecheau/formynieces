<?php
// tests/Feature/DiagnosticMessagingTest.php

use App\Models\User;

beforeEach(function () {
    $this->student = User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-' . uniqid() . '@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => null,
    ]);
});

it('frames the diagnostic as finding the edge of what she knows', function () {
    $response = $this->actingAs($this->student)->get(route('diagnostic.intro'));

    $response->assertOk();
    $response->assertSeeText('everything you already know');
    $response->assertSeeText('how far you can go');

    // Still no pass/fail, score, or timer language on the intro
    $response->assertDontSee('score', false);
    $response->assertDontSee('timer', false);
});