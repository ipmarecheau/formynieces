<?php

use App\Livewire\ChildLoginCard;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

it('shows the child login id and hides the password until revealed', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'email' => 'jordan.smit@smoothseas.org']);
    $child->child_password_enc = 'CoralTide48';
    $child->save();

    $this->actingAs($guardian);

    Livewire::test(ChildLoginCard::class, ['childId' => $child->id])
        ->assertSee('jordan.smit@smoothseas.org')
        ->assertDontSee('CoralTide48')          // hidden by default
        ->assertSet('revealed', false)
        ->call('toggleReveal')
        ->assertSet('revealed', true)
        ->assertSee('CoralTide48');              // revealed on tap
});

it('refuses to render a child that is not the guardian\'s own', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    $otherChild = User::factory()->create(['role' => 'student', 'parent_id' => User::factory()->create()->id]);

    $this->actingAs($guardian);

    Livewire::test(ChildLoginCard::class, ['childId' => $otherChild->id]);
})->throws(ModelNotFoundException::class);
