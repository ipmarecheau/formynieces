<?php

use App\Models\PracticeAttempt;
use App\Models\PracticeQuestion;
use App\Models\ReteachSession;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Practice\Remediation;

function remStudent(string $suffix): User
{
    return User::create([
        'name' => 'Maya', 'email' => "maya-rem-{$suffix}@test.com",
        'password' => bcrypt('secret'), 'role' => 'student', 'onboarding_completed_at' => now(),
    ]);
}

/** Record one RESOLVED question outcome in the diary at a difficulty: a hard miss (both attempts wrong) or a correct. */
function remOutcome(int $studentId, int $moduleId, int $difficulty, bool $hardMiss): void
{
    $qid = PracticeQuestion::query()->where('module_id', $moduleId)->value('id')
        ?? PracticeQuestion::factory()->create(['module_id' => $moduleId])->id;

    if ($hardMiss) {
        PracticeAttempt::create(['student_id' => $studentId, 'practice_question_id' => $qid, 'module_id' => $moduleId, 'difficulty' => $difficulty, 'attempt' => 1, 'is_correct' => false]);
        PracticeAttempt::create(['student_id' => $studentId, 'practice_question_id' => $qid, 'module_id' => $moduleId, 'difficulty' => $difficulty, 'attempt' => 2, 'is_correct' => false]);
    } else {
        PracticeAttempt::create(['student_id' => $studentId, 'practice_question_id' => $qid, 'module_id' => $moduleId, 'difficulty' => $difficulty, 'attempt' => 1, 'is_correct' => true]);
    }
}

/** LL-14 — two hard misses in a row at D3/D5 trigger a re-teach. */
it('triggers a re-teach on two hard misses in a row at D3 or D5', function () {
    $student = remStudent('14');
    $module = SyllabusModule::factory()->create();
    $gate = app(Remediation::class);

    remOutcome($student->id, $module->id, 3, hardMiss: false);   // a correct first
    expect($gate->triggerFor($student->id, $module->id))->toBeNull();

    remOutcome($student->id, $module->id, 5, hardMiss: true);    // one hard miss
    expect($gate->triggerFor($student->id, $module->id))->toBeNull();

    remOutcome($student->id, $module->id, 5, hardMiss: true);    // two in a row
    expect($gate->triggerFor($student->id, $module->id))->toBe(ReteachSession::TRIGGER_STREAK);
})->group('scenario:LL-14');

it('triggers the streak rule at any difficulty, including D1', function () {
    $student = remStudent('14b');
    $module = SyllabusModule::factory()->create();

    remOutcome($student->id, $module->id, 1, hardMiss: true);
    expect(app(Remediation::class)->triggerFor($student->id, $module->id))->toBeNull();

    remOutcome($student->id, $module->id, 1, hardMiss: true);   // two misses in a row — even at D1
    expect(app(Remediation::class)->triggerFor($student->id, $module->id))->toBe(ReteachSession::TRIGGER_STREAK);
})->group('scenario:LL-14');

/** LL-22 — five hard misses in the last seven trigger a re-teach (any difficulty). */
it('triggers a re-teach on five hard misses in the last seven', function () {
    $student = remStudent('22');
    $module = SyllabusModule::factory()->create();

    // 7 resolved D1 outcomes, 5 of them hard misses (D1 so the streak rule never fires).
    foreach ([true, true, false, true, false, true, true] as $isMiss) {
        remOutcome($student->id, $module->id, 1, hardMiss: $isMiss);
    }

    expect(app(Remediation::class)->triggerFor($student->id, $module->id))->toBe(ReteachSession::TRIGGER_WINDOW);
})->group('scenario:LL-22');

/** LL-16 — proving understanding (3 correct at D1) completes the re-teach and resumes at D3. */
it('completes the re-teach after three correct proofs and resumes solo practice at D3', function () {
    $student = remStudent('16');
    $module = SyllabusModule::factory()->create();
    // She was struggling up at D5 before the re-teach.
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $module->id, 'status' => 'needs_work', 'current_rung' => 5, 'current_streak' => 1]);
    $gate = app(Remediation::class);

    $session = $gate->start($student->id, $module->id, ReteachSession::TRIGGER_STREAK);

    $gate->recordCorrectProof($session);
    $gate->recordCorrectProof($session);
    expect($session->fresh()->isComplete())->toBeFalse();       // 2 of 3

    $session = $gate->recordCorrectProof($session);
    expect($session->isComplete())->toBeTrue();                 // 3rd completes it

    $progress = StudentProgress::where('student_id', $student->id)->where('module_id', $module->id)->first();
    expect($progress->current_rung)->toBe(3);                   // resumes at D3, not the bottom
    expect($progress->current_streak)->toBe(0);
})->group('scenario:LL-16');

/** The miss-counters reset on entry: outcomes before a completed re-teach never re-trigger. */
it('resets the miss counters when a re-teach has been completed', function () {
    $student = remStudent('reset');
    $module = SyllabusModule::factory()->create();
    $gate = app(Remediation::class);

    remOutcome($student->id, $module->id, 3, hardMiss: true);
    remOutcome($student->id, $module->id, 3, hardMiss: true);
    expect($gate->triggerFor($student->id, $module->id))->toBe(ReteachSession::TRIGGER_STREAK);

    // Run a re-teach to completion.
    $session = $gate->start($student->id, $module->id, ReteachSession::TRIGGER_STREAK);
    $this->travel(1)->minutes();
    $gate->recordCorrectProof($session);
    $gate->recordCorrectProof($session);
    $gate->recordCorrectProof($session);

    // Those old misses are before the boundary now — no re-trigger.
    $this->travel(1)->minutes();
    expect($gate->triggerFor($student->id, $module->id))->toBeNull();

    // Fresh hard misses after the re-teach do re-trigger.
    remOutcome($student->id, $module->id, 3, hardMiss: true);
    remOutcome($student->id, $module->id, 3, hardMiss: true);
    expect($gate->triggerFor($student->id, $module->id))->toBe(ReteachSession::TRIGGER_STREAK);
})->group('scenario:LL-14');
