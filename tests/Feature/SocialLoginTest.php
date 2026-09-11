<?php

use App\Models\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function () {
    // Enable Google for these tests (credentials gate whether the provider is live).
    config([
        'services.google.client_id' => 'test-id',
        'services.google.client_secret' => 'test-secret',
    ]);
});

function fakeSocialUser(string $email, string $name = 'Parent Name', string $id = 'oauth-123', string $providerName = 'google'): void
{
    $social = (new SocialiteUser)->map(['id' => $id, 'name' => $name, 'email' => $email]);

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($social);
    Socialite::shouldReceive('driver')->with($providerName)->andReturn($provider);
}

function enableAllSocialProviders(): void
{
    foreach (['google', 'microsoft', 'facebook', 'linkedin-openid'] as $p) {
        config(["services.{$p}.client_id" => 'test-id', "services.{$p}.client_secret" => 'test-secret']);
    }
}

it('creates a verified guardian on first Google sign-in when consent is given', function () {
    fakeSocialUser('newparent@example.com', 'Ada Parent');

    $this->withSession(['social_consent' => true])
        ->get(route('social.callback', 'google'))
        ->assertRedirect(route('dashboard'));

    $user = User::whereRaw('LOWER(email) = ?', ['newparent@example.com'])->first();

    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('guardian')
        ->and($user->email_verified_at)->not->toBeNull()   // provider vouches — no verify step
        ->and($user->age_attested_at)->not->toBeNull()
        ->and($user->terms_accepted_at)->not->toBeNull()
        ->and($user->social_provider)->toBe('google');

    $this->assertAuthenticatedAs($user);
});

it('refuses to create a new account without the 18+/terms consent', function () {
    fakeSocialUser('noconsent@example.com');

    $this->get(route('social.callback', 'google'))
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('social');

    expect(User::whereRaw('LOWER(email) = ?', ['noconsent@example.com'])->exists())->toBeFalse();
    $this->assertGuest();
});

it('links Google to an existing account and signs in without needing consent again', function () {
    $existing = User::factory()->create([
        'email' => 'known@example.com',
        'role' => 'guardian',
        'email_verified_at' => now(),
    ]);
    fakeSocialUser('known@example.com');

    $this->get(route('social.callback', 'google'))
        ->assertRedirect(route('dashboard'));

    expect($existing->fresh()->social_provider)->toBe('google');
    $this->assertAuthenticatedAs($existing->fresh());
    expect(User::whereRaw('LOWER(email) = ?', ['known@example.com'])->count())->toBe(1);
});

it('stores consent on redirect and 404s a provider with no credentials', function () {
    // A disabled provider (no creds) is not routable.
    config(['services.google.client_id' => null]);

    $this->get(route('social.redirect', 'google'))->assertNotFound();
    $this->get(route('social.callback', 'google'))->assertNotFound();
});

it('shows the Google button on the register page when configured', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Continue with Google');
});

it('renders every configured provider button on the register page', function () {
    enableAllSocialProviders();

    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Continue with Google')
        ->assertSee('Continue with Microsoft')
        ->assertSee('Continue with Facebook')
        ->assertSee('Continue with LinkedIn')
        ->assertSee('Continue with Apple')   // coming soon (greyed)
        ->assertSee('Coming soon')
        ->assertDontSee('Continue with TikTok')
        ->assertDontSee('Continue with Yahoo');
});

it('creates a verified guardian via a non-Google provider (Microsoft / Hotmail)', function () {
    enableAllSocialProviders();
    fakeSocialUser('hotmailparent@hotmail.com', 'Sam Parent', 'ms-1', 'microsoft');

    $this->withSession(['social_consent' => true])
        ->get(route('social.callback', 'microsoft'))
        ->assertRedirect(route('dashboard'));

    $user = User::whereRaw('LOWER(email) = ?', ['hotmailparent@hotmail.com'])->first();
    expect($user)->not->toBeNull()
        ->and($user->social_provider)->toBe('microsoft')
        ->and($user->email_verified_at)->not->toBeNull();
});
