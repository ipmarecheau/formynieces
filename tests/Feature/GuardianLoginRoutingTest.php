<?php

use App\Models\User;

it('routes a verified guardian with no student to her dashboard', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
    ]);

    $this->post('/login', [
        'email' => $guardian->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));
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

it('shows an Add child empty state on the dashboard for a guardian with no child', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
    ]);

    // No email re-verification: her verified email opens the dashboard directly.
    $this->actingAs($guardian)
        ->get(route('guardian.dashboard'))
        ->assertOk()
        ->assertSee('Add child')
        ->assertSee(route('child.setup'));
})->group('scenario:GO-18');

it('lets a no-child guardian add a child without any email verification step', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
    ]);

    // Reaches child setup (no verification redirect) and creates the child.
    $this->actingAs($guardian)->get(route('child.setup'))->assertOk();

    $this->actingAs($guardian)->post(route('child.store'), [
        'name' => 'Amara',
        'password' => 'ChildPass123!',
        'password_confirmation' => 'ChildPass123!',
        'target_sea_year' => 2027,
    ])->assertRedirect(route('child.setup'));

    expect($guardian->students()->where('name', 'Amara')->exists())->toBeTrue();
})->group('scenario:GO-18');
