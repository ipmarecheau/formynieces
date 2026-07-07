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