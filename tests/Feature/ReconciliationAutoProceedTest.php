<?php

use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use Illuminate\Support\Facades\DB;

/**
 * RR-10 — an unanswered reconciliation auto-proceeds after three days so the
 * student's progress is not halted. A daily command finds students whose
 * guardian decision has been pending for three or more days (measured from the
 * diagnostic's completion) and proceeds with the diagnostic result.
 */
function makeStalePendingStudent(int $daysSinceCompletion): User
{
    $guardian = User::create([
        'name' => 'Guardian',
        'email' => 'rr10-guard-'.uniqid().'@formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'guardian',
    ]);

    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'rr10-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'parent_id' => $guardian->id,
        'target_sea_year' => 2027,
        'onboarding_completed_at' => null,
        'guardian_reconciled_at' => null,
        'known_weak_areas' => ['Fractions'],
    ]);

    // The diagnostic cleared the flagged Fractions strand — a decision is pending.
    $fractions = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Fractions: Adding', 'sea_section' => 'Section I', 'sequence_order' => 1, 'pacing_week' => 1]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $fractions->id, 'status' => 'mastered', 'score' => 3]);
    SyllabusModule::create(['subject' => 'Math', 'topic' => 'Geometry: Angles', 'sea_section' => 'Section I', 'sequence_order' => 2, 'pacing_week' => 1]);

    $when = now()->subDays($daysSinceCompletion);
    DB::table('diagnostic_sessions')->insert([
        'student_id' => $student->id,
        'status' => 'completed',
        'item_plan' => '[]',
        'current_item' => 0,
        'completed_at' => $when,
        'created_at' => $when,
        'updated_at' => $when,
    ]);

    return $student;
}

it('auto-proceeds a reconciliation left unanswered for three days', function () {
    $student = makeStalePendingStudent(4);

    $this->artisan('reconciliation:auto-proceed')->assertSuccessful();

    $student->refresh();

    expect($student->guardian_reconciled_at)->not->toBeNull()
        ->and($student->onboarding_completed_at)->not->toBeNull()
        ->and(StudentJourney::where('student_id', $student->id)->exists())->toBeTrue()
        ->and(WeeklyTarget::where('student_id', $student->id)->exists())->toBeTrue();
})->group('scenario:RR-10');

it('leaves a reconciliation pending fewer than three days untouched', function () {
    $student = makeStalePendingStudent(1);

    $this->artisan('reconciliation:auto-proceed')->assertSuccessful();

    $student->refresh();

    expect($student->guardian_reconciled_at)->toBeNull()
        ->and($student->onboarding_completed_at)->toBeNull()
        ->and(StudentJourney::where('student_id', $student->id)->exists())->toBeFalse();
})->group('scenario:RR-10');
