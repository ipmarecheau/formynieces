<?php

use App\Models\User;

it('shows a countdown interstitial before handing the device over', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'email' => 'ava.tide@smoothseas.org']);

    $this->actingAs($guardian)
        ->get(route('student.handoff', $child))
        ->assertOk()
        ->assertSee($child->name);
});

it('logs the guardian out and lands on the student sign-in with the child login prefilled', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'email' => 'ava.tide@smoothseas.org']);

    $this->actingAs($guardian)
        ->post(route('student.handoff.commit', $child))
        ->assertRedirect(route('student.login', ['login' => $child->email]));

    $this->assertGuest();
});

it('prefills the login id field on the student sign-in from the query string', function () {
    $this->get(route('student.login', ['login' => 'ava.tide@smoothseas.org']))
        ->assertOk()
        ->assertSee('value="ava.tide@smoothseas.org"', false);
});

it('forbids handing off a child that is not the guardian\'s own', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    $otherChild = User::factory()->create(['role' => 'student', 'parent_id' => User::factory()->create()->id]);

    $this->actingAs($guardian)
        ->get(route('student.handoff', $otherChild))
        ->assertForbidden();

    $this->actingAs($guardian)
        ->post(route('student.handoff.commit', $otherChild))
        ->assertForbidden();
});
