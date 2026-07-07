<?php

use App\Models\User;
use function Pest\Laravel\actingAs;

it('redirects an unverified guardian away from child setup', function () {
    $guardian = User::factory()->unverified()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
    ]);

    actingAs($guardian)
        ->get('/child-setup')
        ->assertRedirect(route('verification.notice'));
})->group('scenario:GO-02');