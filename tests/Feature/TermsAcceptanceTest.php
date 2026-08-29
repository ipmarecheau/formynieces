<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

function termsRegistrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Terms Guardian',
        'email' => 'terms@example.com',
        'phone' => '+18685551234',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
        'age_attestation' => '1',
        'terms' => '1',
    ], $overrides);
}

it('requires accepting the terms to register', function () {
    post(route('register'), termsRegistrationPayload(['terms' => null]))
        ->assertSessionHasErrors('terms');

    expect(User::where('email', 'terms@example.com')->exists())->toBeFalse();
})->group('scenario:GO-16');

it('records when and which version of the terms was accepted', function () {
    post(route('register'), termsRegistrationPayload())
        ->assertRedirect(route('verification.notice'));

    $user = User::where('email', 'terms@example.com')->first();

    expect($user->terms_accepted_at)->not->toBeNull()
        ->and($user->terms_version)->toBe(config('legal.terms_version'));
})->group('scenario:GO-16');

it('serves a public terms page', function () {
    get(route('terms'))
        ->assertOk()
        ->assertSee('Terms')
        ->assertSee('Limitation of liability');
})->group('scenario:GO-16');

it('shows the terms and an acceptance box on the registration screen', function () {
    get(route('register'))
        ->assertOk()
        ->assertSee('I have read and agree')
        ->assertSee('Governing law');   // the terms body is embedded for viewing
})->group('scenario:GO-16');

it('serves a public privacy policy that centres children data', function () {
    get(route('privacy'))
        ->assertOk()
        ->assertSee('Privacy Policy')
        ->assertSee('additional commitments')
        ->assertSee('never sell it, never use it for advertising')
        ->assertSee('64-Bit Software Solutions');
})->group('scenario:GO-16');

it('links the terms and privacy policy from the registration acceptance', function () {
    get(route('register'))
        ->assertOk()
        ->assertSee(route('terms'))
        ->assertSee(route('privacy'));
})->group('scenario:GO-16');
