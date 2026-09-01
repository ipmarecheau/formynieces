<?php

use App\Models\CoParent;
use App\Models\User;

it('edits child metadata and invites a co-parent end to end, front and back in sync', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
        'email' => 'family-browser@example.test',
    ]);
    $child = User::factory()->create([
        'role' => 'student',
        'parent_id' => $guardian->id,
        'name' => 'Amara',
    ]);

    $page = visit('/login');
    $page->type('#email', 'family-browser@example.test')
        ->type('#password', 'password')
        ->click('button[type=submit]');

    // Navigate to the Family page from the portal.
    $page->navigate('/family')
        ->assertSee('Children')
        ->assertSee('The other parent')
        ->assertSee('Amara');

    // Edit the child's optional metadata and save.
    $page->fill("#child-{$child->id}-birth_year", '2016')
        ->fill("#child-{$child->id}-current_school", "St. Mary's Government")
        ->click('Save Amara')
        ->assertSee('details saved');

    // Back end reflects the front-end save.
    expect($child->fresh())
        ->birth_year->toBe(2016)
        ->current_school->toBe("St. Mary's Government");

    // Invite the other parent.
    $page->fill('#co-name', 'Marcus Thomas')
        ->fill('#co-email', 'marcus@example.test')
        ->fill('#co-relationship', 'Father')
        ->click('Send invitation')
        ->assertSee('Marcus Thomas')
        ->assertSee('marcus@example.test');

    // Back end has the invitation.
    expect(CoParent::where('guardian_id', $guardian->id)->where('email', 'marcus@example.test')->exists())
        ->toBeTrue();
})->group('scenario:GF-04');
