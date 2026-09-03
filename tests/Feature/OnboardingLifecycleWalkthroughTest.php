<?php

use App\Livewire\OnboardingWizard;
use App\Models\ModuleStageCompletion;
use App\Models\SyllabusModule;
use App\Models\User;
use Livewire\Livewire;

/**
 * OnboardingLifecycleWalkthrough — the automated E2E walkthrough that confirms the whole first-run
 * lifecycle works from BOTH the parent's and the child's side, through the real routes (QC-08).
 * Starts from a verified guardian (registration + email verification are covered by GO-01..14).
 */
it('walks the full parent + child onboarding lifecycle end to end', function () {
    // ---- Parent: verified guardian, no child yet (WZ-01) -------------------------------
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    $this->actingAs($guardian);

    Livewire::test(OnboardingWizard::class)
        ->assertSee('Add your child')
        ->assertSet('minimised', false);
    expect(App\Services\Onboarding\OnboardingWizard::for($guardian)->nextStep()['key'])->toBe('child');

    // ---- Parent adds a child through the real route (sets the SEA year too) -------------
    $this->post(route('child.store'), [
        'name' => 'Maya',
        'target_sea_year' => 2027,
    ])->assertRedirect();

    $child = $guardian->students()->firstOrFail();
    expect($child->target_sea_year)->toBe(2027);

    // Wizard now reflects the child + exam date; next is the diagnostic (WZ-03/05).
    $steps = collect(App\Services\Onboarding\OnboardingWizard::for($guardian->refresh())->steps())->keyBy('key');
    expect($steps['child']['done'])->toBeTrue()
        ->and($steps['exam_date']['done'])->toBeTrue()
        ->and(App\Services\Onboarding\OnboardingWizard::for($guardian)->nextStep()['key'])->toBe('diagnostic');

    // ---- Child: logs in for the first time and is sent into her diagnostic (WZ-07) ------
    auth()->logout();
    $plainPassword = $child->child_password_enc; // encrypted cast → decrypts on read
    $this->post(route('login'), [
        'email' => $child->email,
        'password' => $plainPassword,
    ])->assertRedirect(route('diagnostic.intro'));

    // ---- Child completes the diagnostic and opens her first lesson ----------------------
    $child->diagnosticSessions()->create(['status' => 'completed', 'completed_at' => now()]);
    $module = SyllabusModule::factory()->create();
    ModuleStageCompletion::create([
        'student_id' => $child->id, 'module_id' => $module->id, 'stage' => 'lesson', 'completed_at' => now(),
    ]);

    // ---- Parent side: the wizard reflects the child's progress, then retires (WZ-06/09) -
    $this->actingAs($guardian);
    Livewire::test(OnboardingWizard::class)->assertSee('all set up');
    expect($guardian->fresh()->onboarding_completed_at)->not->toBeNull();
});

it('a returning guardian resumes the same progress on a fresh session (WZ-04)', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'target_sea_year' => 2027]);
    $this->actingAs($guardian);

    // A brand-new component instance (a different device/session) shows the same DB-derived progress.
    Livewire::test(OnboardingWizard::class)->assertSee('Take the diagnostic');
    $progress = App\Services\Onboarding\OnboardingWizard::for($guardian)->progress();
    expect($progress['done'])->toBe(3); // account + child + exam date
});
