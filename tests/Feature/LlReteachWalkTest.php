<?php

use App\Livewire\PracticeWalk;
use App\Livewire\ReteachWalk;
use App\Models\PracticeAttempt;
use App\Models\PracticeQuestion;
use App\Models\ReteachSession;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Practice\QuestionExposure;
use App\Services\Practice\Remediation;
use Livewire\Livewire;

function rwStudent(string $suffix): User
{
    return User::create([
        'name' => 'Maya', 'email' => "maya-rw-{$suffix}@test.com",
        'password' => bcrypt('secret'), 'role' => 'student', 'onboarding_completed_at' => now(),
    ]);
}

/** LL-14 (entry wiring) — a second hard miss at D5 in practice redirects her into the re-teach. */
it('pulls her into the re-teach from practice on a triggering hard miss', function () {
    $student = rwStudent('entry');
    $module = SyllabusModule::factory()->create();   // no lesson -> ungated, practice is open
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $module->id, 'status' => 'needs_work', 'current_rung' => 5]);

    $q1 = PracticeQuestion::factory()->create(['module_id' => $module->id, 'difficulty' => 5, 'prompt' => 'q1', 'options' => ['a', 'b', 'c', 'd'], 'correct_index' => 1]);
    PracticeQuestion::factory()->create(['module_id' => $module->id, 'difficulty' => 5, 'prompt' => 'q2', 'options' => ['a', 'b', 'c', 'd'], 'correct_index' => 1]);

    // One prior resolved hard miss at D5 (on q1), and mark it seen so practice serves q2 next.
    PracticeAttempt::create(['student_id' => $student->id, 'practice_question_id' => $q1->id, 'module_id' => $module->id, 'difficulty' => 5, 'attempt' => 1, 'is_correct' => false]);
    PracticeAttempt::create(['student_id' => $student->id, 'practice_question_id' => $q1->id, 'module_id' => $module->id, 'difficulty' => 5, 'attempt' => 2, 'is_correct' => false]);
    app(QuestionExposure::class)->record($student->id, $q1->content_hash, 'practice');

    Livewire::actingAs($student)->test(PracticeWalk::class, ['module' => $module])
        ->call('choose', 0)   // first-try miss -> retry
        ->call('choose', 0)   // second miss -> hard miss -> two in a row at D5 -> re-teach
        ->assertRedirect(route('practice.reteach', $module->id));

    expect(app(Remediation::class)->activeSession($student->id, $module->id))->not->toBeNull();
})->group('scenario:LL-14');

/** LL-16 — three correct D1 proofs in the re-teach complete it and resume solo practice at D3. */
it('completes the re-teach after three D1 proofs and redirects to practice at D3', function () {
    $student = rwStudent('exit');
    $module = SyllabusModule::factory()->create();
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $module->id, 'status' => 'needs_work', 'current_rung' => 5, 'current_streak' => 1]);
    PracticeQuestion::factory()->create(['module_id' => $module->id, 'difficulty' => 1, 'prompt' => 'd1a', 'options' => ['a', 'b', 'c', 'd'], 'correct_index' => 1]);
    PracticeQuestion::factory()->create(['module_id' => $module->id, 'difficulty' => 1, 'prompt' => 'd1b', 'options' => ['a', 'b', 'c', 'd'], 'correct_index' => 1]);
    app(Remediation::class)->start($student->id, $module->id, ReteachSession::TRIGGER_STREAK);

    Livewire::actingAs($student)->test(ReteachWalk::class, ['module' => $module])
        ->assertSet('phase', 'relearn')
        ->call('startProving')
        ->assertSet('phase', 'prove')
        ->call('choose', 1)->call('nextQuestion')   // proof 1
        ->call('choose', 1)->call('nextQuestion')   // proof 2
        ->call('choose', 1)                          // proof 3 -> complete
        ->assertRedirect(route('practice.walk', $module->id));

    $progress = StudentProgress::where('student_id', $student->id)->where('module_id', $module->id)->first();
    expect($progress->current_rung)->toBe(3);        // resumes at D3, never the bottom
    expect(app(Remediation::class)->activeSession($student->id, $module->id))->toBeNull();   // completed
})->group('scenario:LL-16');

/** LL-15 — a missed proof offers the teacher chat, and the worked-examples escape is always there. */
it('offers the teacher chat on a missed proof and keeps a route back to the tutorial', function () {
    $student = rwStudent('teach');
    $module = SyllabusModule::factory()->create();
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $module->id, 'status' => 'needs_work', 'current_rung' => 5]);
    PracticeQuestion::factory()->create(['module_id' => $module->id, 'difficulty' => 1, 'prompt' => 'd1', 'options' => ['a', 'b', 'c', 'd'], 'correct_index' => 1]);
    app(Remediation::class)->start($student->id, $module->id, ReteachSession::TRIGGER_WINDOW);

    Livewire::actingAs($student)->test(ReteachWalk::class, ['module' => $module])
        ->call('startProving')
        ->call('choose', 0)                          // wrong
        ->assertSet('teacherOffered', true)
        ->assertSee('worked examples');              // the return-to-tutorial escape (LL-15 budget fallback)
})->group('scenario:LL-15');
