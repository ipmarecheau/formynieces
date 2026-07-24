<?php

use App\Livewire\GuardianDashboard;
use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Diagnostic\DiagnosticReconciliation;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * RR-04 (loop 4b) — the guardian-facing reconciliation surfaces and the proceed
 * choice. When the diagnostic cleared a strand the guardian flagged, her
 * dashboard shows the difference and offers to proceed with the diagnostic
 * result or keep her stated weak areas; onboarding stays pending until she picks.
 */
function makePendingReconciliation(): array
{
    $guardian = User::create([
        'name' => 'Guardian',
        'email' => 'guard-'.uniqid().'@formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'guardian',
    ]);
    $guardian->forceFill(['email_verified_at' => now()])->save();

    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'rr04b-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'parent_id' => $guardian->id,
        'target_sea_year' => 2027,
        'onboarding_completed_at' => null,
        'guardian_reconciled_at' => null,
        'known_weak_areas' => ['Fractions'],
    ]);

    // Guardian flagged Fractions, but the diagnostic mastered it — cleared.
    $fractions = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Fractions: Adding', 'sea_section' => 'Section I', 'sequence_order' => 1, 'pacing_week' => 1]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $fractions->id, 'status' => 'mastered', 'score' => 3]);

    // A not-started module so the generated roadmap has a frontier + weekly target.
    SyllabusModule::create(['subject' => 'Math', 'topic' => 'Geometry: Angles', 'sea_section' => 'Section I', 'sequence_order' => 2, 'pacing_week' => 1]);

    // A completed diagnostic — a reconciliation is only pending once one exists.
    DB::table('diagnostic_sessions')->insert([
        'student_id' => $student->id,
        'status' => 'completed',
        'item_plan' => '[]',
        'current_item' => 0,
        'completed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [$guardian, $student];
}

it('shows the reconciliation prompt on the parent portal for a pending child', function () {
    [$guardian] = makePendingReconciliation();

    $this->actingAs($guardian)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Fractions')
        ->assertSee('Use the diagnostic result')
        ->assertSee('Keep my')
        ->assertSee('This decision cannot be undone');
})->group('scenario:RR-04');

it('proceeds with the diagnostic from the parent portal', function () {
    [$guardian, $student] = makePendingReconciliation();

    $this->actingAs($guardian)
        ->post(route('guardian.reconciliation.proceed', $student))
        ->assertRedirect();

    $student->refresh();

    expect($student->guardian_reconciled_at)->not->toBeNull()
        ->and($student->onboarding_completed_at)->not->toBeNull()
        ->and(StudentJourney::where('student_id', $student->id)->exists())->toBeTrue()
        ->and(app(DiagnosticReconciliation::class)->isPending($student))->toBeFalse();
})->group('scenario:RR-04');

it('forbids proceeding on a child that is not the guardian’s own', function () {
    [, $student] = makePendingReconciliation();

    $outsider = User::create([
        'name' => 'Outsider',
        'email' => 'outsider-'.uniqid().'@formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'guardian',
    ]);
    $outsider->forceFill(['email_verified_at' => now()])->save();

    $this->actingAs($outsider)
        ->post(route('guardian.reconciliation.proceed', $student))
        ->assertForbidden();

    expect($student->refresh()->guardian_reconciled_at)->toBeNull();
})->group('scenario:RR-04');

it('shows the guardian where the diagnostic differs from her stated weak areas', function () {
    [$guardian] = makePendingReconciliation();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSee('Fractions')
        ->assertSee('Use the diagnostic')
        ->assertSee('Keep my');
})->group('scenario:RR-04');

it('proceeds with the diagnostic, completing onboarding and generating the roadmap', function () {
    [$guardian, $student] = makePendingReconciliation();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->call('proceedWithDiagnostic');

    $student->refresh();

    expect($student->guardian_reconciled_at)->not->toBeNull()
        ->and($student->onboarding_completed_at)->not->toBeNull()
        ->and(StudentJourney::where('student_id', $student->id)->exists())->toBeTrue()
        ->and(WeeklyTarget::where('student_id', $student->id)->exists())->toBeTrue()
        ->and(app(DiagnosticReconciliation::class)->isPending($student))->toBeFalse();
})->group('scenario:RR-04');

it('shows no reconciliation prompt when the diagnostic did not clear a flagged strand', function () {
    $guardian = User::create([
        'name' => 'Guardian',
        'email' => 'guard-settled-'.uniqid().'@formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'guardian',
    ]);

    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'rr04b-settled-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'parent_id' => $guardian->id,
        'target_sea_year' => 2027,
        'onboarding_completed_at' => now(),
        'known_weak_areas' => ['Fractions'],
    ]);

    // The diagnostic confirmed Fractions weak — no disagreement, no prompt.
    $fractions = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Fractions: Adding', 'sea_section' => 'Section I', 'sequence_order' => 1, 'pacing_week' => 1]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $fractions->id, 'status' => 'needs_work', 'score' => 0]);

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertDontSee('Use the diagnostic');
})->group('scenario:RR-04');
