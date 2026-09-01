<?php

use App\Mail\ChildAccountCreated;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

it('creates a linked student with a generated password and emails the guardian the login id', function () {
    Mail::fake();
    $guardian = User::factory()->create(['role' => 'guardian', 'age_attested_at' => now()]);

    $response = $this->actingAs($guardian)->post('/child-setup', [
        'name' => 'Aaliyah Thomas',
        'target_sea_year' => 2027,
        'known_weak_areas' => ['Fractions', 'Reading Comprehension'],
    ]);

    $student = User::where('role', 'student')->first();
    expect($student)->not->toBeNull()
        ->and($student->email)->toBe('athom@smoothseas.org')
        ->and($student->parent_id)->toBe($guardian->id)
        ->and($student->target_sea_year)->toBe(2027)
        ->and($student->child_password_enc)->not->toBeEmpty();               // recoverable copy stored
    expect(Hash::check($student->child_password_enc, $student->password))->toBeTrue(); // matches the hash

    $response->assertSessionHas('student_credentials');
    Mail::assertSent(ChildAccountCreated::class, fn ($m) => $m->hasTo($guardian->email) && $m->loginId === 'athom@smoothseas.org');
})->group('scenario:GO-04');

it('no longer requires a parent-set password', function () {
    Mail::fake();
    $guardian = User::factory()->create(['role' => 'guardian', 'age_attested_at' => now()]);

    $this->actingAs($guardian)->post('/child-setup', ['name' => 'Kwame Best', 'target_sea_year' => 2027])
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'kbest@smoothseas.org')->exists())->toBeTrue();
})->group('scenario:GO-04');

it('auto-suffixes the username when the generated one is taken', function () {
    Mail::fake();
    $guardian = User::factory()->create(['role' => 'guardian', 'age_attested_at' => now()]);
    User::factory()->create(['role' => 'student', 'email' => 'athom@smoothseas.org']);

    $this->actingAs($guardian)->post('/child-setup', ['name' => 'Aiden Thompson', 'target_sea_year' => 2027]);

    expect(User::where('email', 'athom1@smoothseas.org')->exists())->toBeTrue();
})->group('scenario:GO-04');

it('lets the guardian reveal and reset the child password, but not another guardian', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'age_attested_at' => now()]);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id]);
    $child->child_password_enc = 'CoralTide48';
    $child->save();

    // reveal
    $this->actingAs($guardian)->post(route('guardian.children.reveal', $child))
        ->assertSessionHas('revealed', fn ($r) => $r['id'] === $child->id && $r['password'] === 'CoralTide48');

    // reset -> new password, both hash and encrypted copy change
    $this->actingAs($guardian)->post(route('guardian.children.reset', $child))->assertSessionHas('reset_done');
    $child->refresh();
    expect($child->child_password_enc)->not->toBe('CoralTide48');
    expect(Hash::check($child->child_password_enc, $child->password))->toBeTrue();

    // a different guardian is forbidden
    $other = User::factory()->create(['role' => 'guardian', 'age_attested_at' => now()]);
    $this->actingAs($other)->post(route('guardian.children.reveal', $child))->assertForbidden();
    $this->actingAs($other)->post(route('guardian.children.reset', $child))->assertForbidden();
})->group('scenario:GO-04');
