<?php

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Diagnostic\DiagnosticReconciliation;
use App\Services\Diagnostic\ReconciliationResolver;
use Illuminate\Support\Facades\DB;

/**
 * RR-05 — the guardian keeps her stated weak areas over the diagnostic. When the
 * diagnostic cleared a strand she flagged, choosing to keep it reverts every
 * module in that strand to not_started before the roadmap generates, while every
 * other diagnostic result is applied unchanged.
 */
function rr05PendingKeep(): array
{
    $guardian = User::create([
        'name' => 'Guardian',
        'email' => 'rr05-guard-'.uniqid().'@formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'guardian',
    ]);
    $guardian->forceFill(['email_verified_at' => now()])->save();

    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'rr05-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'parent_id' => $guardian->id,
        'target_sea_year' => 2027,
        'onboarding_completed_at' => null,
        'guardian_reconciled_at' => null,
        'known_weak_areas' => ['Fractions'],
    ]);

    // Guardian flagged Fractions, but the diagnostic cleared it: two Fractions
    // modules the diagnostic decided she already knows.
    $fracAdd = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Fractions: Adding', 'sea_section' => 'Section I', 'sequence_order' => 1, 'pacing_week' => 1]);
    $fracSub = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Fractions: Subtracting', 'sea_section' => 'Section I', 'sequence_order' => 2, 'pacing_week' => 1]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $fracAdd->id, 'status' => 'mastered', 'score' => 100]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $fracSub->id, 'status' => 'inferred_mastered', 'score' => 100]);

    // A module in a different strand the diagnostic also mastered — must be left
    // untouched (remaining diagnostic results applied unchanged).
    $geometry = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Geometry: Angles', 'sea_section' => 'Section I', 'sequence_order' => 3, 'pacing_week' => 1]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $geometry->id, 'status' => 'mastered', 'score' => 100]);

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

    return [$student, $fracAdd, $fracSub, $geometry];
}

it('reverts every module in a kept strand to not_started', function () {
    [$student, $fracAdd, $fracSub, $geometry] = rr05PendingKeep();

    app(ReconciliationResolver::class)->keepStatedWeakAreas($student);

    expect(StudentProgress::where('student_id', $student->id)->where('module_id', $fracAdd->id)->value('status'))->toBe('not_started')
        ->and(StudentProgress::where('student_id', $student->id)->where('module_id', $fracSub->id)->value('status'))->toBe('not_started');

    // The kept decision still finalizes onboarding and clears the pending hold.
    $student->refresh();
    expect($student->guardian_reconciled_at)->not->toBeNull()
        ->and($student->onboarding_completed_at)->not->toBeNull()
        ->and(app(DiagnosticReconciliation::class)->isPending($student))->toBeFalse();
})->group('scenario:RR-05');

it('leaves diagnostic results outside the kept strand unchanged', function () {
    [$student, , , $geometry] = rr05PendingKeep();

    app(ReconciliationResolver::class)->keepStatedWeakAreas($student);

    expect(StudentProgress::where('student_id', $student->id)->where('module_id', $geometry->id)->value('status'))->toBe('mastered');
})->group('scenario:RR-05');
