<?php

use App\Models\User;
use App\Services\Onboarding\OnboardingWizard;

it('rejects an unknown state', function () {
    $this->artisan('onboard:demo', ['--state' => 'bogus'])->assertExitCode(1);
});

it('provisions a fresh verified guardian with no child', function () {
    $this->artisan('onboard:demo', ['--state' => 'fresh'])->assertExitCode(0);

    $g = User::where('role', 'guardian')->latest('id')->first();
    expect($g->email_verified_at)->not->toBeNull()
        ->and($g->students()->count())->toBe(0);
});

it('provisions a guardian + child with an exam year for the "added" state', function () {
    $this->artisan('onboard:demo', ['--state' => 'added'])->assertExitCode(0);

    $child = User::where('role', 'student')->latest('id')->first();
    expect($child->parent_id)->not->toBeNull()
        ->and($child->target_sea_year)->not->toBeNull();
});

it('provisions a fully complete family with onboarding stamped', function () {
    $this->artisan('onboard:demo', ['--state' => 'complete'])->assertExitCode(0);

    $g = User::where('role', 'guardian')->latest('id')->first();
    $child = $g->students()->first();
    expect($g->onboarding_completed_at)->not->toBeNull()
        ->and($child->diagnosticSessions()->whereNotNull('completed_at')->exists())->toBeTrue()
        ->and(OnboardingWizard::for($g)->isComplete())->toBeTrue();
});
