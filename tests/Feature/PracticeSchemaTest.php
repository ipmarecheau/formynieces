<?php

use App\Models\PracticeAttempt;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\PracticeQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records a practice attempt with its rung and correctness', function () {
    $student  = User::factory()->create();
    $module   = SyllabusModule::factory()->create();
    $question = PracticeQuestion::factory()->create([
        'module_id'  => $module->id,
        'difficulty' => 3,
    ]);

    $attempt = PracticeAttempt::factory()->create([
        'student_id'           => $student->id,
        'practice_question_id' => $question->id,
        'module_id'            => $module->id,
        'difficulty'           => 3,
        'is_correct'           => true,
    ]);

    expect($attempt->fresh()->is_correct)->toBeTrue()
        ->and($attempt->fresh()->difficulty)->toBe(3)
        ->and($attempt->question->id)->toBe($question->id);
});

it('defaults a new student_progress row to rung 1, streak 0', function () {
    $student = User::factory()->create();
    $module  = SyllabusModule::factory()->create();

    $progress = StudentProgress::create([
        'student_id' => $student->id,
        'module_id'  => $module->id,
        'status'     => 'needs_work',
    ]);

    expect($progress->fresh()->current_rung)->toBe(1)
        ->and($progress->fresh()->current_streak)->toBe(0);
});