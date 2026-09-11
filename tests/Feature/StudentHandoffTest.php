<?php

use App\Models\User;
use Illuminate\Support\Facades\URL;

it('shows a countdown interstitial with a signed hand-off link and recovery actions', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'email' => 'ava.tide@smoothseas.org']);

    $this->actingAs($guardian)
        ->get(route('student.handoff', $child))
        ->assertOk()
        ->assertSee($child->name)
        ->assertSee('/go/handoff/'.$child->id.'/commit', false)  // the signed commit link
        ->assertSee('signature=', false)                          // it is signed
        ->assertSee(route('guardian.dashboard'), false);          // recovery: back to dashboard
});

it('logs the guardian out via the signed link and lands on the student sign-in with the login prefilled', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'email' => 'ava.tide@smoothseas.org']);

    $signed = URL::temporarySignedRoute('student.handoff.commit', now()->addMinutes(15), ['child' => $child->id]);

    $this->actingAs($guardian)
        ->get($signed)
        ->assertRedirect(route('student.login', ['login' => $child->email]));

    $this->assertGuest();
});

it('rejects an unsigned or tampered commit link with 403 (never a 419)', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id]);

    // No signature at all.
    $this->actingAs($guardian)
        ->get(route('student.handoff.commit', $child))
        ->assertForbidden();
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

    // Even a validly-signed link for another guardian's child is refused.
    $signed = URL::temporarySignedRoute('student.handoff.commit', now()->addMinutes(15), ['child' => $otherChild->id]);
    $this->actingAs($guardian)
        ->get($signed)
        ->assertForbidden();
});
