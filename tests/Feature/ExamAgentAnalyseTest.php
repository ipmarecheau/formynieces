<?php

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\ExamAgentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Regression: after the 2026_06_13 remap, syllabus_modules.subject is
 * constrained to ('Math','ELA'). analyse() must iterate the live subjects,
 * not the dead pre-remap strings, or every ELA module is invisible to it.
 */
it('analyses the live Math and ELA subjects only', function () {
    $student = User::factory()->create(['role' => 'student']);

    // Two modules per subject, all in week 1 so both are "expected" now.
    $mathA = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);
    $mathB = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);
    $elaA  = SyllabusModule::factory()->create(['subject' => 'ELA',  'pacing_week' => 1]);
    $elaB  = SyllabusModule::factory()->create(['subject' => 'ELA',  'pacing_week' => 1]);

    // Master one of each subject; leave the other behind.
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $mathA->id, 'status' => 'mastered']);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $elaA->id,  'status' => 'mastered']);

    $result = app(ExamAgentService::class)->analyse($student);
    $subjects = $result['subject_analysis'];

    // Exactly the two live subjects, no dead keys.
    expect(array_keys($subjects))->toEqualCanonicalizing(['Math', 'ELA']);

    // ELA is actually seen now (the bug made this 0 expected / 0 behind).
    expect($subjects['ELA']['expected'])->toBe(2)
        ->and($subjects['ELA']['completed'])->toBe(1)
        ->and($subjects['ELA']['behind_count'])->toBe(1);

    expect($subjects['Math']['expected'])->toBe(2)
        ->and($subjects['Math']['behind_count'])->toBe(1);
});
