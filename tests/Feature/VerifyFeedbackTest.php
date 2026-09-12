<?php

use App\Livewire\VerifyAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// Stop mount()'s auto-send from replacing our hand-set code with a fresh one.
function suppressAutosend(int $userId): void
{
    RateLimiter::hit('verify-email-autosend:'.$userId, 120);
}

it('tells an incorrect code apart from an expired one', function () {
    $user = User::factory()->create([
        'role' => 'guardian',
        'email_verified_at' => null,
        'email_verification_code' => Hash::make('123456'),
        'email_verification_code_expires_at' => now()->addMinutes(30),
    ]);
    suppressAutosend($user->id);

    // Wrong code, valid window -> "incorrect".
    Livewire::actingAs($user)->test(VerifyAccount::class)
        ->set('emailCode', '999999')
        ->call('submitEmailCode')
        ->assertSee('incorrect');

    // Right code but expired -> "expired".
    $user->forceFill(['email_verification_code_expires_at' => now()->subMinute()])->save();
    suppressAutosend($user->id);
    Livewire::actingAs($user)->test(VerifyAccount::class)
        ->set('emailCode', '123456')
        ->call('submitEmailCode')
        ->assertSee('expired');
})->group('scenario:GO-12');

it('enforces a resend cooldown so the button is not spammed', function () {
    $user = User::factory()->create(['role' => 'guardian', 'email_verified_at' => null]);
    RateLimiter::clear('verify-email-resend:'.$user->id);

    $page = Livewire::actingAs($user)->test(VerifyAccount::class)
        ->call('resendEmail')
        ->assertSet('resendCountdown', VerifyAccount::RESEND_COOLDOWN)
        ->call('resendEmail');

    expect($page->get('resendCountdown'))->toBeGreaterThan(0);
})->group('scenario:GO-12');
