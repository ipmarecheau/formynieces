<?php

use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('a guardian can register with an 18+ attestation', function () {
    $email = 'guardian@example.com';

    $response = post(route('register'), [
        'name' => 'Jane Guardian',
        'email' => $email,
        'phone' => '+18685551234',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
        'age_attestation' => '1',
    ]);

    $response->assertRedirect(route('verification.notice'));

    assertDatabaseHas(User::class, [
        'email' => $email,
        'role' => 'guardian',
    ]);

    expect(User::where('email', $email)->first()->age_attested_at)
        ->not->toBeNull();
})->group('scenario:GO-01');

it('registration is rejected without the 18+ attestation', function () {
    $email = 'noattest@example.com';

    post(route('register'), [
        'name' => 'No Attest',
        'email' => $email,
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
    ])->assertSessionHasErrors('age_attestation');

    assertDatabaseMissing(User::class, ['email' => $email]);
})->group('scenario:GO-01');

it('the registration screen is reachable', function () {
    get(route('register'))->assertOk();
})->group('scenario:GO-01');

// GO-09 — the name field is clearly the guardian's own, not the child's.
it('asks for the guardian own name, not the child name', function () {
    get(route('register'))
        ->assertOk()
        ->assertSee('Parent / Guardian')      // label names whose name this is
        ->assertSee('add your child')          // helper points to the next step
        ->assertDontSee('Aaliyah Thomas');     // the child-like example is gone
})->group('scenario:GO-09');

// GO-11 — the verification notice tells her exactly what to do next.
it('tells the guardian what happens next on the verify-email screen', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'email' => 'mum@example.com',
        'email_verified_at' => null,
    ]);

    $this->actingAs($guardian);

    get(route('verification.notice'))
        ->assertOk()
        ->assertSee('mum@example.com')     // names the address the link went to
        ->assertSee('set up your child')   // what confirming leads to
        ->assertSee('Resend')              // can resend
        ->assertSee('Need help');          // a human-help path inside the flow
})->group('scenario:GO-11');

// GO-10 — the setup journey shows the guardian where she is.
it('shows the guardian where she is in the setup journey', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($guardian);

    get(route('child.setup'))
        ->assertOk()
        ->assertSee('Step 2 of 3')          // where she is / how many remain
        ->assertSee('Set up your child')     // step named in plain language
        ->assertSee('Start the diagnostic');
})->group('scenario:GO-10');
