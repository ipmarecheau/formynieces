<?php

use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Diagnostic\DiagnosticReconciliation;
use App\Services\Pacing\RoadmapGenerator;

/**
 * RR-02 / RR-03 — reconciling the completed diagnostic against the guardian's
 * stated weak areas.
 *
 * A guardian-flagged strand is "confirmed weak" when the diagnostic marked any
 * of that strand's modules needs_work, and "cleared" when it did not. When the
 * diagnostic confirms the flagged strands exactly (RR-02) or confirms them and
 * finds further weak strands (RR-03), no strand is cleared, so no guardian
 * decision is required and the roadmap proceeds from the diagnostic result.
 *
 * A strand is the portion of a module's "Strand: Topic" name before the colon,
 * matching SyllabusModule::strandsBySubject() and the child-setup checkboxes.
 */
function makeStudentWithWeakAreas(array $weakAreas): User
{
    return User::create([
        'name' => 'Aaliyah',
        'email' => 'rr02-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'target_sea_year' => 2027,
        'onboarding_completed_at' => now(),
        'known_weak_areas' => $weakAreas,
    ]);
}

it('requires no guardian decision when the diagnostic confirms the flagged weak strands exactly', function () {
    $student = makeStudentWithWeakAreas(['Fractions']);

    // Guardian flagged Fractions; the diagnostic marked a Fractions module
    // needs_work — the flagged strand is confirmed, not cleared.
    $fractions = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Fractions: Adding', 'sea_section' => 'Section I', 'sequence_order' => 1, 'pacing_week' => 2]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $fractions->id, 'status' => 'needs_work', 'score' => 0]);

    // An unrelated strand the diagnostic mastered — must not count as cleared,
    // because the guardian never flagged it.
    $algebra = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Algebra: Basics', 'sea_section' => 'Section I', 'sequence_order' => 2, 'pacing_week' => 1]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $algebra->id, 'status' => 'mastered', 'score' => 3]);

    $reconciliation = app(DiagnosticReconciliation::class);

    expect($reconciliation->clearedStrands($student))->toBe([])
        ->and($reconciliation->requiresGuardianDecision($student))->toBeFalse();
})->group('scenario:RR-02');

it('proceeds to generate the roadmap from the diagnostic result when no decision is required', function () {
    $student = makeStudentWithWeakAreas(['Fractions']);

    $fractions = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Fractions: Adding', 'sea_section' => 'Section I', 'sequence_order' => 1, 'pacing_week' => 1]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $fractions->id, 'status' => 'needs_work', 'score' => 0]);

    expect(app(DiagnosticReconciliation::class)->requiresGuardianDecision($student))->toBeFalse();

    app(RoadmapGenerator::class)->generate($student);

    expect(StudentJourney::where('student_id', $student->id)->exists())->toBeTrue()
        ->and(WeeklyTarget::where('student_id', $student->id)->exists())->toBeTrue();
})->group('scenario:RR-02');

it('requires no guardian decision when the diagnostic finds the flagged strands plus further weak strands', function () {
    $student = makeStudentWithWeakAreas(['Fractions']);

    // Guardian flagged only Fractions; the diagnostic confirms Fractions AND
    // finds Geometry weak too. Extra weakness never triggers a decision.
    $fractions = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Fractions: Adding', 'sea_section' => 'Section I', 'sequence_order' => 1, 'pacing_week' => 2]);
    $geometry = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Geometry: Angles', 'sea_section' => 'Section I', 'sequence_order' => 2, 'pacing_week' => 3]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $fractions->id, 'status' => 'needs_work', 'score' => 0]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $geometry->id, 'status' => 'needs_work', 'score' => 0]);

    $reconciliation = app(DiagnosticReconciliation::class);

    expect($reconciliation->clearedStrands($student))->toBe([])
        ->and($reconciliation->requiresGuardianDecision($student))->toBeFalse();
})->group('scenario:RR-03');
