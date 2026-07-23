<?php

use App\Models\PracticeAttempt;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;

/**
 * A week's-target module reports one of three states, derived live from
 * student_progress (mastery) + practice_attempts (has she practiced it):
 *   completed   — the module is mastered
 *   in_progress — any practice attempt exists (even a wrong one)
 *   not_started — no practice attempts yet
 */
function wtStateStudent(): User
{
    return User::create([
        'name' => 'Aaliyah',
        'email' => 'wtstate-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

function wtStateTarget(User $student, SyllabusModule $module): WeeklyTarget
{
    return WeeklyTarget::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'week_start_date' => now()->startOfWeek()->toDateString(),
        'is_completed' => false,
    ]);
}

it('reports not_started when the module has no practice attempts', function () {
    $student = wtStateStudent();
    $module = SyllabusModule::factory()->create();
    $target = wtStateTarget($student, $module);

    expect($target->state())->toBe('not_started');
});

it('reports in_progress after any practice attempt, even a wrong one', function () {
    $student = wtStateStudent();
    $module = SyllabusModule::factory()->create();
    $target = wtStateTarget($student, $module);

    PracticeAttempt::factory()->create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'is_correct' => false,
    ]);

    expect($target->state())->toBe('in_progress');
});

it('reports completed when the module is mastered, regardless of attempts', function () {
    $student = wtStateStudent();
    $module = SyllabusModule::factory()->create();
    $target = wtStateTarget($student, $module);

    PracticeAttempt::factory()->create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'is_correct' => true,
    ]);
    StudentProgress::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'status' => 'mastered',
        'score' => 100,
    ]);

    expect($target->state())->toBe('completed');
});
