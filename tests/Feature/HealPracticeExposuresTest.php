<?php

use App\Models\PracticeAttempt;
use App\Models\PracticeQuestion;
use App\Models\StudentQuestionExposure;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('removes only never-answered practice exposures, keeping answered and non-practice ones', function () {
    $student = User::factory()->create(['role' => 'student']);
    $module = SyllabusModule::factory()->create();

    $answeredQ = PracticeQuestion::factory()->create([
        'module_id' => $module->id, 'difficulty' => 1,
        'prompt' => 'Answered', 'options' => ['A', 'B', 'C', 'D'], 'correct_index' => 1, 'explanation' => 'x',
    ]);
    $serveOnlyQ = PracticeQuestion::factory()->create([
        'module_id' => $module->id, 'difficulty' => 1,
        'prompt' => 'Served only', 'options' => ['A', 'B', 'C', 'D'], 'correct_index' => 1, 'explanation' => 'y',
    ]);

    // She actually answered the first one.
    PracticeAttempt::create([
        'student_id' => $student->id, 'practice_question_id' => $answeredQ->id,
        'module_id' => $module->id, 'difficulty' => 1, 'is_correct' => true, 'attempt' => 1,
    ]);

    // Exposures: answered (practice) — keep; serve-only (practice) — delete; check — keep.
    StudentQuestionExposure::create(['student_id' => $student->id, 'content_hash' => $answeredQ->content_hash, 'context' => 'practice']);
    StudentQuestionExposure::create(['student_id' => $student->id, 'content_hash' => $serveOnlyQ->content_hash, 'context' => 'practice']);
    StudentQuestionExposure::create(['student_id' => $student->id, 'content_hash' => $serveOnlyQ->content_hash.'-chk', 'context' => 'check']);

    // Dry run changes nothing.
    $this->artisan('practice:heal-exposures')->assertSuccessful();
    expect(StudentQuestionExposure::count())->toBe(3);

    // Apply removes exactly the serve-only practice row.
    $this->artisan('practice:heal-exposures --apply')->assertSuccessful();

    expect(StudentQuestionExposure::where('content_hash', $serveOnlyQ->content_hash)->where('context', 'practice')->exists())->toBeFalse();
    expect(StudentQuestionExposure::where('content_hash', $answeredQ->content_hash)->exists())->toBeTrue();
    expect(StudentQuestionExposure::where('context', 'check')->exists())->toBeTrue();
    expect(StudentQuestionExposure::count())->toBe(2);
});
