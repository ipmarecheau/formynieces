<?php

use App\Models\User;

it('renders a 4-item portal nav with a fixed bottom bar, not the old 9-item scroll strip', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);

    $this->actingAs($guardian)
        ->get(route('guardian.dashboard'))
        ->assertOk()
        ->assertSee('gb-botnav', false)        // new fixed bottom bar
        ->assertDontSee('gb-mobnav', false)    // old horizontal scroll strip removed
        ->assertDontSee('The honest layer')    // old 9-item nav eyebrow removed
        ->assertDontSee("Children's logins");  // moved off the nav onto the Home login card
});
