<?php

use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WritingSubmission;
use App\Services\Estimator\PerformanceEstimator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class)->group('scenario:GD-12');

function seedAttempts(int $studentId, SyllabusModule $module, int $correct, int $wrong): void
{
    $question = PracticeQuestion::factory()->create(['module_id' => $module->id, 'subject' => $module->subject]);

    for ($i = 0; $i < $correct + $wrong; $i++) {
        DB::table('practice_attempts')->insert([
            'student_id' => $studentId,
            'practice_question_id' => $question->id,
            'module_id' => $module->id,
            'difficulty' => 3,
            'is_correct' => $i < $correct ? 1 : 0,
            'attempt' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

it('reports no data when the student has no assessment history', function () {
    $student = User::factory()->create(['role' => 'student']);

    $result = app(PerformanceEstimator::class)->estimate($student, []);

    expect($result['has_data'])->toBeFalse()
        ->and($result['composite'])->toBeNull()
        ->and($result['confidence'])->toBe('insufficient')
        ->and($result['placement']['tier'])->toBe('Early days');
});

it('computes average score per subject from attempt history', function () {
    $student = User::factory()->create(['role' => 'student']);
    $math = SyllabusModule::factory()->create(['subject' => 'Math']);
    $ela = SyllabusModule::factory()->create(['subject' => 'ELA']);

    seedAttempts($student->id, $math, correct: 18, wrong: 2); // 90%
    seedAttempts($student->id, $ela, correct: 12, wrong: 8);  // 60%

    $result = app(PerformanceEstimator::class)->estimate($student, []);

    $mathRow = collect($result['subjects'])->firstWhere('subject', 'Math');
    $elaRow = collect($result['subjects'])->firstWhere('subject', 'ELA');

    expect($mathRow['accuracy'])->toBe(90)
        ->and($mathRow['confident'])->toBeTrue()
        ->and($elaRow['accuracy'])->toBe(60)
        ->and($result['has_data'])->toBeTrue();
});

it('projects a weighted composite and maps it to a placement tier', function () {
    $student = User::factory()->create(['role' => 'student']);
    $math = SyllabusModule::factory()->create(['subject' => 'Math']);
    $ela = SyllabusModule::factory()->create(['subject' => 'ELA']);

    // 92% Math (50%) + 92% ELA (30%) => composite 92 with no writing weight used.
    seedAttempts($student->id, $math, correct: 46, wrong: 4);
    seedAttempts($student->id, $ela, correct: 46, wrong: 4);

    $result = app(PerformanceEstimator::class)->estimate($student, []);

    expect($result['composite'])->toBe(92)
        ->and($result['placement']['tier'])->toBe('Traditional / most in-demand schools')
        ->and($result['confidence'])->toBe('high');
});

it('folds creative writing into the estimate as its own component', function () {
    $student = User::factory()->create(['role' => 'student']);

    WritingSubmission::factory()->create([
        'student_id' => $student->id,
        'status' => 'scored',
        'content_score' => 8, 'language_score' => 8, 'grammar_score' => 8, 'organisation_score' => 8,
        'scored_at' => now(),
    ]);

    $result = app(PerformanceEstimator::class)->estimate($student, []);
    $writingRow = collect($result['subjects'])->firstWhere('subject', 'Writing');

    expect($writingRow)->not->toBeNull()
        ->and($writingRow['accuracy'])->toBe(80)
        ->and($result['has_data'])->toBeTrue();
});
