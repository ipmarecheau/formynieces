<?php

use App\Models\User;

it('routes a verified guardian with no student to child setup', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
    ]);

    $response = $this->post('/login', [
        'email' => $guardian->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('child.setup'));
})->group('scenario:GO-03');

it('routes a verified guardian who already has a student to the dashboard', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
    ]);
    User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id]);

    $this->post('/login', [
        'email' => $guardian->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));
})->group('scenario:GO-18');
