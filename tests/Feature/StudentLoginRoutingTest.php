<?php

use App\Models\User;

it('routes a student with incomplete onboarding to the diagnostic intro', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'parent_id' => $guardian->id,
        'email' => 'aaliyah@students.formynieces.com',
        'onboarding_completed_at' => null,
    ]);

    $response = $this->post('/login', [
        'email' => $student->email,
        'password' => 'password', // factory default
    ]);

    $response->assertRedirect(route('diagnostic.intro'));
})->group('scenario:GO-05');