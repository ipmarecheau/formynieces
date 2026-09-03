<?php

use App\Livewire\OnboardingWizard;
use App\Models\DiagnosticSession;
use App\Models\ModuleStageCompletion;
use App\Models\SyllabusModule;
use App\Models\User;

use Livewire\Livewire;

function wizardGuardian(): User
{
    return User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
}

it('shows the getting-started checklist to a fresh guardian (WZ-01)', function () {
    $this->actingAs(wizardGuardian());

    Livewire::test(OnboardingWizard::class)
        ->assertSee('Getting started')
        ->assertSee('Add your child')
        ->assertSee('Take the diagnostic');
});

it('minimises to a pill and reopens with progress intact (WZ-08/WZ-10)', function () {
    $this->actingAs(wizardGuardian());

    Livewire::test(OnboardingWizard::class)
        ->assertSet('minimised', false)
        ->call('minimise')
        ->assertSet('minimised', true)
        ->assertSee('Getting started ·')
        ->call('reopen')
        ->assertSet('minimised', false)
        ->assertSee('Take the diagnostic');
});

it('congratulates and retires once the family is fully set up (WZ-09)', function () {
    $g = wizardGuardian();
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $g->id, 'target_sea_year' => 2027]);
    DiagnosticSession::create(['student_id' => $child->id, 'status' => 'completed', 'completed_at' => now()]);
    $module = SyllabusModule::factory()->create();
    ModuleStageCompletion::create(['student_id' => $child->id, 'module_id' => $module->id, 'stage' => 'lesson', 'completed_at' => now()]);

    $this->actingAs($g);

    Livewire::test(OnboardingWizard::class)->assertSee('all set up');

    expect($g->fresh()->onboarding_completed_at)->not->toBeNull();
});
