<?php

use App\Models\DiagnosticSession;
use App\Models\ModuleStageCompletion;
use App\Models\SyllabusModule;
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
        ->and($wiz->progress())->toMatchArray(['done' => 1, 'total' => 5]);
});

it('ticks off add-child only once a child exists, then points at exam date (WZ-03)', function () {
    $g = wizGuardian();
    wizChild($g);

    $wiz = OnboardingWizard::for($g->refresh());
    $steps = collect($wiz->steps())->keyBy('key');

    expect($steps['child']['done'])->toBeTrue()
        ->and($wiz->nextStep()['key'])->toBe('exam_date');
});

it('completes the exam-date step when the child has a SEA year (WZ-05)', function () {
    $g = wizGuardian();
    wizChild($g, ['target_sea_year' => 2027]);

    expect(collect(OnboardingWizard::for($g)->steps())->keyBy('key')['exam_date']['done'])->toBeTrue();
});

it('reflects what the child did: diagnostic + first lesson (WZ-06)', function () {
    $g = wizGuardian();
    $child = wizChild($g, ['target_sea_year' => 2027]);
    DiagnosticSession::create(['student_id' => $child->id, 'status' => 'completed', 'completed_at' => now()]);
    $module = SyllabusModule::factory()->create();
    ModuleStageCompletion::create(['student_id' => $child->id, 'module_id' => $module->id, 'stage' => 'lesson', 'completed_at' => now()]);

    $steps = collect(OnboardingWizard::for($g)->steps())->keyBy('key');
    expect($steps['diagnostic']['done'])->toBeTrue()
        ->and($steps['first_lesson']['done'])->toBeTrue();
});

it('an incomplete diagnostic does not count (WZ-03 real-state)', function () {
    $g = wizGuardian();
    $child = wizChild($g);
    DiagnosticSession::create(['student_id' => $child->id, 'status' => 'in_progress', 'completed_at' => null]);

    expect(collect(OnboardingWizard::for($g)->steps())->keyBy('key')['diagnostic']['done'])->toBeFalse();
});

it('is complete only when every step is done, then retires idempotently (WZ-09)', function () {
    $g = wizGuardian();
    $child = wizChild($g, ['target_sea_year' => 2027]);
    DiagnosticSession::create(['student_id' => $child->id, 'status' => 'completed', 'completed_at' => now()]);
    $module = SyllabusModule::factory()->create();
    ModuleStageCompletion::create(['student_id' => $child->id, 'module_id' => $module->id, 'stage' => 'lesson', 'completed_at' => now()]);

    $wiz = OnboardingWizard::for($g);
    expect($wiz->isComplete())->toBeTrue()
        ->and($wiz->nextStep())->toBeNull();

    expect($wiz->retireIfComplete())->toBeTrue();
    expect($g->fresh()->onboarding_completed_at)->not->toBeNull();
    // idempotent — a second call does not re-stamp
    expect(OnboardingWizard::for($g->fresh())->retireIfComplete())->toBeFalse();
});
