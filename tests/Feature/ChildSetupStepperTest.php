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
        ->assertSee('data-step="3"', false)
        ->assertSee('name="target_sea_year"', false); // all fields still in the single POST
});
