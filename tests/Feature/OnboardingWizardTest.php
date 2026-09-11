<?php

use App\Models\StudentStreak;
use App\Models\User;
use App\Services\Onboarding\OnboardingWizard;

function wizGuardian(): User
{
    return User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
}

function wizChild(User $g, array $attrs = []): User
{
    return User::factory()->create(array_merge(['role' => 'student', 'parent_id' => $g->id], $attrs));
}

it('greets a verified guardian with no child: account done, next step is add-child (WZ-01/02)', function () {
    $wiz = OnboardingWizard::for(wizGuardian());

    $steps = collect($wiz->steps())->keyBy('key');
    expect($steps['account']['done'])->toBeTrue()
        ->and($steps['child']['done'])->toBeFalse()
        ->and($wiz->nextStep()['key'])->toBe('child')
        ->and($wiz->progress())->toMatchArray(['done' => 1, 'total' => 3]);
});

it('ticks off add-child only once a child exists, then points at getting the login (WZ-03)', function () {
    $g = wizGuardian();
    wizChild($g);

    $wiz = OnboardingWizard::for($g->refresh());
    $steps = collect($wiz->steps())->keyBy('key');

    expect($steps['child']['done'])->toBeTrue()
        ->and($wiz->nextStep()['key'])->toBe('credentials');
});

it('completes the credentials step once the child has signed in (WZ-06)', function () {
    $g = wizGuardian();
    $child = wizChild($g, ['target_sea_year' => 2027]);
    StudentStreak::create(['student_id' => $child->id, 'type' => 'login', 'count' => 1]);

    $steps = collect(OnboardingWizard::for($g)->steps())->keyBy('key');
    expect($steps['credentials']['done'])->toBeTrue();
});

it('the credentials step is not done until the child actually signs in (WZ-03 real-state)', function () {
    $g = wizGuardian();
    wizChild($g);   // child exists, but has never signed in

    expect(collect(OnboardingWizard::for($g)->steps())->keyBy('key')['credentials']['done'])->toBeFalse();
});

it('is complete only when every step is done, then retires idempotently (WZ-09)', function () {
    $g = wizGuardian();
    $child = wizChild($g, ['target_sea_year' => 2027]);
    StudentStreak::create(['student_id' => $child->id, 'type' => 'login', 'count' => 1]);

    $wiz = OnboardingWizard::for($g);
    expect($wiz->isComplete())->toBeTrue()
        ->and($wiz->nextStep())->toBeNull();

    expect($wiz->retireIfComplete())->toBeTrue();
    expect($g->fresh()->onboarding_completed_at)->not->toBeNull();
    // idempotent — a second call does not re-stamp
    expect(OnboardingWizard::for($g->fresh())->retireIfComplete())->toBeFalse();
});
