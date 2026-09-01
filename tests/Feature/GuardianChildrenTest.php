<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the children logins page with the guardian\'s children', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'age_attested_at' => now()]);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'name' => 'Amara']);

    $this->actingAs($guardian)
        ->get(route('guardian.children'))
        ->assertOk()
        ->assertSee('Amara')
        ->assertSee($child->email)
        ->assertSee('Reveal password')
        ->assertSee('Reset password');
})->group('scenario:GO-04');

it('surfaces a Children\'s logins link in the guardian portal navigation', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'age_attested_at' => now()]);
    User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id]);

    $this->actingAs($guardian)
        ->get(route('guardian.dashboard'))
        ->assertOk()
        ->assertSee(route('guardian.children'));
})->group('scenario:GO-04');
