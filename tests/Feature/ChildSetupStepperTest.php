<?php

use App\Models\User;

it('shows a step-by-step "add your first child" form', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);

    $this->actingAs($guardian)
        ->get(route('child.setup'))
        ->assertOk()
        ->assertSee('Add your first child')
        ->assertSee('add others later')          // note that more children come from the dashboard
        ->assertSee('rw-progress', false)         // stepper present
        ->assertSee('data-step="1"', false)
        ->assertSee('data-step="2"', false)          // trimmed to two steps: name, then SEA year
        ->assertDontSee('data-step="3"', false)       // weak areas moved to the post-login profile wizard
        ->assertSee('name="target_sea_year"', false); // all fields still in the single POST
});

it('shows the "I have saved it" acknowledgement and dashboard CTA after creating a child', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);

    $this->actingAs($guardian)
        ->post(route('child.store'), ['name' => 'Jordan', 'target_sea_year' => 2027])
        ->assertRedirect(route('child.setup'));

    $this->actingAs($guardian)
        ->get(route('child.setup'))
        ->assertOk()
        ->assertSee("Jordan's login", false)
        ->assertSee('only ever shown here and on your dashboard')
        ->assertSee('saved-ack', false)
        ->assertSee('Go to my dashboard');
});
