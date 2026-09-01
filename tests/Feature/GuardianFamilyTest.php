<?php

use App\Livewire\GuardianFamily;
use App\Models\CoParent;
use App\Models\User;
use App\Notifications\CoParentInvitation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function familyGuardian(): User
{
    return User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
        'phone' => '+18685551234',
    ]);
}

function childOf(User $guardian, array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'student',
        'parent_id' => $guardian->id,
        'name' => 'Amara',
    ], $attributes));
}

it('renders the family page with children and the co-parent invite', function () {
    $guardian = familyGuardian();
    childOf($guardian);

    $this->actingAs($guardian)
        ->get(route('guardian.family'))
        ->assertOk()
        ->assertSee('Children')
        ->assertSee('The other parent')
        ->assertSee('Amara');
})->group('scenario:GF-01');

it('saves optional child metadata', function () {
    $guardian = familyGuardian();
    $child = childOf($guardian);

    Livewire::actingAs($guardian)
        ->test(GuardianFamily::class)
        ->set("children.{$child->id}.birth_year", '2016')
        ->set("children.{$child->id}.current_school", "St. Mary's Government")
        ->call('saveChild', $child->id)
        ->assertHasNoErrors();

    expect($child->fresh())
        ->birth_year->toBe(2016)
        ->current_school->toBe("St. Mary's Government");
})->group('scenario:GF-02');

it('requires the child name but not the optional metadata', function () {
    $guardian = familyGuardian();
    $child = childOf($guardian);

    // Name cleared → rejected.
    Livewire::actingAs($guardian)
        ->test(GuardianFamily::class)
        ->set("children.{$child->id}.name", '')
        ->call('saveChild', $child->id)
        ->assertHasErrors(["children.{$child->id}.name"]);

    // Name only, no optional metadata → succeeds.
    Livewire::actingAs($guardian)
        ->test(GuardianFamily::class)
        ->set("children.{$child->id}.name", 'Amara B')
        ->set("children.{$child->id}.birth_year", '')
        ->set("children.{$child->id}.current_school", '')
        ->set("children.{$child->id}.target_sea_year", '')
        ->call('saveChild', $child->id)
        ->assertHasNoErrors();

    expect($child->fresh())
        ->name->toBe('Amara B')
        ->birth_year->toBeNull()
        ->current_school->toBeNull();
})->group('scenario:GF-03');

it('rejects an out-of-range birth year', function () {
    $guardian = familyGuardian();
    $child = childOf($guardian);

    Livewire::actingAs($guardian)
        ->test(GuardianFamily::class)
        ->set("children.{$child->id}.birth_year", '1990')
        ->call('saveChild', $child->id)
        ->assertHasErrors(["children.{$child->id}.birth_year"]);
})->group('scenario:GF-02');

it('a guardian cannot edit another guardian\'s child', function () {
    $guardian = familyGuardian();
    $other = childOf(familyGuardian());

    // saveChild scopes to her own students, so another guardian's child is not found.
    Livewire::actingAs($guardian)
        ->test(GuardianFamily::class)
        ->set("children.{$other->id}.name", 'Hacked')
        ->call('saveChild', $other->id);
})->group('scenario:GF-02')->throws(ModelNotFoundException::class);

it('invites the other parent and sends an email', function () {
    Notification::fake();
    $guardian = familyGuardian();

    Livewire::actingAs($guardian)
        ->test(GuardianFamily::class)
        ->set('coName', 'Marcus Thomas')
        ->set('coEmail', 'marcus@example.com')
        ->set('coRelationship', 'Father')
        ->call('addCoParent')
        ->assertHasNoErrors();

    expect($guardian->coParents()->where('email', 'marcus@example.com')->exists())->toBeTrue();

    Notification::assertSentOnDemand(CoParentInvitation::class);
})->group('scenario:GF-04');

it('rejects inviting the same co-parent twice', function () {
    $guardian = familyGuardian();
    CoParent::factory()->for($guardian, 'guardian')->create(['email' => 'dupe@example.com']);

    Livewire::actingAs($guardian)
        ->test(GuardianFamily::class)
        ->set('coName', 'Dupe Person')
        ->set('coEmail', 'dupe@example.com')
        ->call('addCoParent')
        ->assertHasErrors(['coEmail']);

    expect($guardian->coParents()->where('email', 'dupe@example.com')->count())->toBe(1);
})->group('scenario:GF-05');

it('removes a co-parent', function () {
    $guardian = familyGuardian();
    $coParent = CoParent::factory()->for($guardian, 'guardian')->create();

    Livewire::actingAs($guardian)
        ->test(GuardianFamily::class)
        ->call('removeCoParent', $coParent->id)
        ->assertHasNoErrors();

    expect(CoParent::find($coParent->id))->toBeNull();
})->group('scenario:GF-06');
