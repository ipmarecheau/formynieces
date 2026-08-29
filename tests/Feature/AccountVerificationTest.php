<?php

use App\Livewire\VerifyAccount;
use App\Models\User;
use App\Services\Verification\StubPhoneVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

use function Pest\Laravel\post;

uses(RefreshDatabase::class);

it('requires a phone number to register', function () {
    post(route('register'), [
        'name' => 'No Phone',
        'email' => 'nophone@example.com',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
        'age_attestation' => '1',
        'terms' => '1',
    ])->assertSessionHasErrors('phone');
})->group('scenario:GO-12');

it('rejects a non international phone number', function () {
    post(route('register'), [
        'name' => 'Bad Phone',
        'email' => 'badphone@example.com',
        'phone' => '8685551234', // no +country code
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
        'age_attestation' => '1',
        'terms' => '1',
    ])->assertSessionHasErrors('phone');
})->group('scenario:GO-12');

it('rejects registration when the configured Turnstile token fails', function () {
    config()->set('services.turnstile.secret_key', 'test-secret');
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response(['success' => false], 200),
    ]);

    post(route('register'), [
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'phone' => '+18685551234',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
        'age_attestation' => '1',
        'terms' => '1',
        'cf-turnstile-response' => 'bad-token',
    ])->assertSessionHasErrors('cf-turnstile-response');
})->group('scenario:GO-12');

it('starts phone verification WhatsApp-first on registration', function () {
    config()->set('services.phone_verification.enabled', true);

    post(route('register'), [
        'name' => 'Reg',
        'email' => 'reg@example.com',
        'phone' => '+18685551234',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
        'age_attestation' => '1',
        'terms' => '1',
    ])->assertRedirect(route('verification.notice'));

    expect(app(StubPhoneVerifier::class)->lastChannel('+18685551234'))->toBe('whatsapp');
})->group('scenario:GO-13');

it('verifies the email with the 6-digit code as well as the link', function () {
    $user = User::factory()->create(['role' => 'guardian', 'email_verified_at' => null, 'phone' => null]);
    $code = $user->generateEmailVerificationCode();

    Livewire::actingAs($user)
        ->test(VerifyAccount::class)
        ->set('emailCode', $code)
        ->call('submitEmailCode');

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
})->group('scenario:GO-14');

it('rejects a wrong email code', function () {
    $user = User::factory()->create(['role' => 'guardian', 'email_verified_at' => null, 'phone' => null]);
    $user->generateEmailVerificationCode();

    Livewire::actingAs($user)
        ->test(VerifyAccount::class)
        ->set('emailCode', '000000')
        ->call('submitEmailCode')
        ->assertHasErrors('emailCode');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
})->group('scenario:GO-14');

it('verifies the phone with the WhatsApp code and offers an SMS fallback', function () {
    config()->set('services.phone_verification.enabled', true);

    $user = User::factory()->create([
        'role' => 'guardian',
        'email_verified_at' => now(),
        'phone' => '+18685551234',
    ]);

    $page = Livewire::actingAs($user)->test(VerifyAccount::class);

    // Fallback: request the code by SMS instead.
    $page->call('resendPhone', 'sms');
    expect(app(StubPhoneVerifier::class)->lastChannel('+18685551234'))->toBe('sms');

    // Enter the (stub) code.
    $page->set('phoneCode', StubPhoneVerifier::DEV_CODE)->call('submitPhoneCode');

    expect($user->fresh()->hasVerifiedPhone())->toBeTrue();
})->group('scenario:GO-13');

it('sends the fully-verified guardian into onboarding', function () {
    config()->set('services.phone_verification.enabled', true);

    $user = User::factory()->create([
        'role' => 'guardian',
        'email_verified_at' => now(),
        'phone' => '+18685551234',
    ]);

    Livewire::actingAs($user)
        ->test(VerifyAccount::class)
        ->set('phoneCode', StubPhoneVerifier::DEV_CODE)
        ->call('submitPhoneCode')
        ->assertRedirect(route('child.setup'));
})->group('scenario:GO-13');

it('at the free launch captures the phone but does not verify it', function () {
    // Feature off (default): registration must not start phone verification…
    post(route('register'), [
        'name' => 'Free Launch',
        'email' => 'free@example.com',
        'phone' => '+18685551234',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
        'age_attestation' => '1',
        'terms' => '1',
    ])->assertRedirect(route('verification.notice'));

    expect(app(StubPhoneVerifier::class)->lastChannel('+18685551234'))->toBeNull();

    // …and email verification alone opens onboarding, phone unverified.
    $user = User::where('email', 'free@example.com')->first();
    $code = $user->generateEmailVerificationCode();

    Livewire::actingAs($user)
        ->test(VerifyAccount::class)
        ->set('emailCode', $code)
        ->call('submitEmailCode')
        ->assertRedirect(route('child.setup'));

    expect($user->fresh()->hasVerifiedPhone())->toBeFalse();
})->group('scenario:GO-15');
