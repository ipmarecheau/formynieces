<?php

use App\Livewire\LessonWalk;
use App\Livewire\PracticeWalk;
use App\Livewire\ReteachWalk;
use App\Models\Lesson;
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

/** LL-14 (entry wiring) — a second hard miss at D5 shows the re-teach hand-off splash, then routes in on tap. */
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
        ->call('choose', 0)   // second miss -> hard miss -> two in a row at D5 -> re-teach hand-off
        ->assertSet('reteachSplash', true)                         // warm splash first, never an abrupt jump
        ->assertNoRedirect()
        ->call('enterReteach')                                     // she taps "Let's revisit it →"
        ->assertRedirect(route('practice.lesson', $module->id));   // re-teach begins by re-walking the lesson

    // The session is opened the moment she is pulled in, so the lesson page sees it on arrival.
    expect(app(Remediation::class)->activeSession($student->id, $module->id))->not->toBeNull();
})->group('scenario:LL-14');

/** LL-15 (relearn) — finishing the lesson ALWAYS hands off to the chat's 3 examples; the proof CTA is gated until they're done. */
it('re-walks the lesson and hands off to the chat examples, gating the proof', function () {
    $student = rwStudent('relearn');
    $module = SyllabusModule::factory()->create();
    Lesson::create(['module_id' => $module->id, 'is_published' => true, 'title' => 'Lesson', 'blocks' => [
        ['type' => 'text', 'content' => 'First idea.'],
        ['type' => 'text', 'content' => 'Second idea.'],
    ]]);
    app(Remediation::class)->start($student->id, $module->id, ReteachSession::TRIGGER_STREAK);

    Livewire::actingAs($student)->test(LessonWalk::class, ['module' => $module])
        ->assertSet('reteach', true)
        ->call('next')                              // reveal block 2 -> lesson complete
        ->assertSet('lessonComplete', true)
        ->assertNotDispatched('smooth-reinforce')   // no per-block prompting
        ->assertDispatched('reteach-final')         // always hands off to the chat's 3 examples
        ->assertSet('paused', true)                 // proof frozen until the chat finishes
        ->assertDontSee(route('practice.reteach', $module->id))   // CTA hidden until final-done
        ->call('onFinalDone')                       // chat signals the examples are done
        ->assertSet('finalDone', true)
        ->assertSet('paused', false)
        ->assertSee(route('practice.reteach', $module->id));      // proof CTA now shown
})->group('scenario:LL-15');

/** LL-15/24/26/27 — a missed block hands its OWN rule to the chat; wrong re-asks advance the cycle; three → "in progress". */
it('remediates the missed block with its own rule and lands in progress after three cycles', function () {
    $student = rwStudent('miss');
    $module = SyllabusModule::factory()->create();
    Lesson::create(['module_id' => $module->id, 'is_published' => true, 'title' => 'Lesson', 'blocks' => [
        ['type' => 'check', 'question' => 'Plural of city?', 'options' => ['citys', 'cities'], 'answer' => 1, 'explain' => 'y to i',
            'rule' => 'consonant then y: change y to i and add es',
            'practiceItems' => [
                ['prompt' => "the plural of 'baby'", 'answer' => 'babies'],
                ['prompt' => "the plural of 'lady'", 'answer' => 'ladies'],
                ['prompt' => "the plural of 'penny'", 'answer' => 'pennies'],
            ],
        ],
    ]]);
    app(Remediation::class)->start($student->id, $module->id, ReteachSession::TRIGGER_STREAK);

    $c = Livewire::actingAs($student)->test(LessonWalk::class, ['module' => $module]);
    // Options are shuffled on mount — derive the wrong index rather than assuming index 0.
    $wrong = 1 - (int) $c->get('lessonBlocks')[0]['answer'];
    $c->assertSet('reteach', true)
        ->call('answerCheck', 0, $wrong)            // 1st wrong -> retry (two tries)
        ->assertSet('handoffSplash', false)
        ->call('answerCheck', 0, $wrong)            // 2nd wrong -> hand-off splash
        ->assertSet('handoffSplash', true)
        ->call('enterRemediation')                  // cycle 1 -> chat gets THIS block's rule + first same-rule word
        ->assertSet('remediationCycle', 1)
        ->assertSet('paused', true)
        ->assertDispatched('reteach-miss', function (string $event, array $params): bool {
            return str_contains($params['rule'] ?? '', 'change y to i')      // LL-24: the block's own rule
                && ($params['item']['answer'] ?? '') === 'babies';
        });

    // Cycle 1 → re-ask wrong → cycle 2 → re-ask wrong → cycle 3 → re-ask wrong → in progress (LL-26/27).
    $c->call('onRemediationReturn')->assertSet('paused', false);
    expect($c->get('checkResults'))->not->toHaveKey(0);      // the block is re-asked
    $c->call('answerCheck', 0, $wrong)->assertSet('remediationCycle', 2);
    $c->call('onRemediationReturn')->call('answerCheck', 0, $wrong)->assertSet('remediationCycle', 3);
    $c->call('onRemediationReturn')->call('answerCheck', 0, $wrong)
        ->assertSet('lessonInProgress', true)
        ->assertSet('paused', false);

    $session = ReteachSession::where('student_id', $student->id)->where('module_id', $module->id)->first();
    expect($session->left_in_progress_at)->not->toBeNull();
})->group('scenario:LL-15', 'scenario:LL-26', 'scenario:LL-27');

/** LL-15 — a correct re-ask after remediation resolves it and continues the lesson. */
it('resolves the remediation when she gets the re-asked block right', function () {
    $student = rwStudent('resolve');
    $module = SyllabusModule::factory()->create();
    Lesson::create(['module_id' => $module->id, 'is_published' => true, 'title' => 'Lesson', 'blocks' => [
        ['type' => 'check', 'question' => 'Plural of city?', 'options' => ['citys', 'cities'], 'answer' => 1,
            'rule' => 'change y to i and add es', 'practiceItems' => [['prompt' => "the plural of 'baby'", 'answer' => 'babies']]],
    ]]);
    app(Remediation::class)->start($student->id, $module->id, ReteachSession::TRIGGER_STREAK);

    $c = Livewire::actingAs($student)->test(LessonWalk::class, ['module' => $module]);
    // Options are shuffled on mount — read the remapped correct index so the miss/hit are real.
    $correct = (int) $c->get('lessonBlocks')[0]['answer'];
    $wrong = 1 - $correct;
    $c->call('answerCheck', 0, $wrong)->call('answerCheck', 0, $wrong)   // two misses -> splash
        ->call('enterRemediation')->assertSet('remediationCycle', 1)
        ->call('onRemediationReturn')
        ->call('answerCheck', 0, $correct)                      // correct re-ask -> resolved
        ->assertSet('remediationCycle', 0)
        ->assertSet('lessonComplete', true)
        ->assertDispatched('reteach-final');
})->group('scenario:LL-15');

/** LL-16/24 — three correct TYPED proofs (the lesson's own same-rule words) complete it and resume at D3. */
it('completes the re-teach after three typed proofs and redirects to practice at D3', function () {
    $student = rwStudent('exit');
    $module = SyllabusModule::factory()->create();
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $module->id, 'status' => 'needs_work', 'current_rung' => 5, 'current_streak' => 1]);
    Lesson::create(['module_id' => $module->id, 'is_published' => true, 'title' => 'L', 'blocks' => [
        ['type' => 'check', 'question' => 'q', 'options' => ['a', 'b'], 'answer' => 1, 'rule' => 'change y to i',
            'practiceItems' => [
                ['prompt' => "the plural of 'baby'", 'answer' => 'babies'],
                ['prompt' => "the plural of 'lady'", 'answer' => 'ladies'],
                ['prompt' => "the plural of 'penny'", 'answer' => 'pennies'],
            ],
        ],
    ]]);
    app(Remediation::class)->start($student->id, $module->id, ReteachSession::TRIGGER_STREAK);

    Livewire::actingAs($student)->test(ReteachWalk::class, ['module' => $module])
        ->set('typed', 'babies')->call('submit')->call('nextQuestion')    // proof 1
        ->set('typed', 'ladies')->call('submit')->call('nextQuestion')    // proof 2
        ->set('typed', 'pennies')->call('submit')                         // proof 3 -> complete
        ->assertRedirect(route('practice.walk', $module->id));

    $progress = StudentProgress::where('student_id', $student->id)->where('module_id', $module->id)->first();
    expect($progress->current_rung)->toBe(3);        // resumes at D3, never the bottom
    expect(app(Remediation::class)->activeSession($student->id, $module->id))->toBeNull();   // completed
})->group('scenario:LL-16');

/** LL-15 — a missed proof reveals the answer kindly, with the rule, and never off-lesson content. */
it('reveals the answer kindly with the rule on a missed proof', function () {
    $student = rwStudent('teach');
    $module = SyllabusModule::factory()->create();
    Lesson::create(['module_id' => $module->id, 'is_published' => true, 'title' => 'L', 'blocks' => [
        ['type' => 'check', 'question' => 'q', 'options' => ['a', 'b'], 'answer' => 1, 'rule' => 'change y to i and add es',
            'practiceItems' => [['prompt' => "the plural of 'baby'", 'answer' => 'babies']]],
    ]]);
    app(Remediation::class)->start($student->id, $module->id, ReteachSession::TRIGGER_WINDOW);

    Livewire::actingAs($student)->test(ReteachWalk::class, ['module' => $module])
        ->set('typed', 'babys')->call('submit')      // wrong
        ->assertSee('babies')                         // the answer, revealed kindly
        ->assertSee('change y to i');                 // with the rule
})->group('scenario:LL-15');
