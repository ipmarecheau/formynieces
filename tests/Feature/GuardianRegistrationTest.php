<?php

use App\Models\User;
use function Pest\Laravel\{post, get, assertDatabaseHas, assertDatabaseMissing};

it('a guardian can register with an 18+ attestation', function () {
    $email = 'guardian@example.com';

    $response = post(route('register'), [
        'name' => 'Jane Guardian',
        'email' => $email,
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
