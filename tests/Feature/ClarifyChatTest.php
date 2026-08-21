<?php

use App\Livewire\ClarifyChat;
use App\Models\Lesson;
use App\Models\ReteachSession;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\LlmService;
use App\Services\Safety\ChildSafetyModerator;
use App\Services\Safety\SafetyResult;
use Livewire\Livewire;

/** Put the student into an active re-teach on this module, so the chat mounts in re-teach mode. */
function ccStartReteach(User $student, SyllabusModule $module): void
{
    ReteachSession::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'trigger' => ReteachSession::TRIGGER_STREAK,
        'started_at' => now(),
    ]);
}

function ccStudent(): User
{
    return User::create([
        'name' => 'Maya',
        'email' => 'maya-cc@test.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

function ccModule(): SyllabusModule
{
    $module = SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Number: Place Value', 'sea_section' => 'A',
        'sequence_order' => 1, 'pacing_week' => 1, 'description' => 'Place value.', 'resources' => [],
    ]);
    Lesson::create(['module_id' => $module->id, 'title' => 'Place value', 'blocks' => [
        ['type' => 'text', 'content' => 'Each digit has a place worth ten times the one to its right.'],
    ]]);

    return $module;
}

/** Mock the moderator's verdict for every call this test makes. */
function fakeModerator(SafetyResult $result): void
{
    $mod = Mockery::mock(ChildSafetyModerator::class);
    $mod->shouldReceive('moderate')->andReturn($result);
    test()->instance(ChildSafetyModerator::class, $mod);
}

/** LE-04 — a safe question gets a Socratic answer; progress/mastery are untouched. */
it('answers a safe lesson question without changing progress', function () {
    $student = ccStudent();
    $module = ccModule();
    fakeModerator(SafetyResult::safe());

    $llm = Mockery::mock(LlmService::class);
    $llm->shouldReceive('chat')->once()->andReturn('What do you notice about the two vowels? 🐢');
    $this->instance(LlmService::class, $llm);

    Livewire::actingAs($student)
        ->test(ClarifyChat::class, ['moduleId' => $module->id])
        ->set('draft', 'Why is it i before e?')
        ->call('send')
        ->assertSet('draft', '')
        ->assertSee('What do you notice about the two vowels?');

    expect(StudentProgress::where('student_id', $student->id)->count())->toBe(0);
})->group('scenario:LE-04');

/** AG-12 — an unsafe message is never forwarded to the tutor. */
it('never forwards an unsafe message to the tutor', function () {
    $student = ccStudent();
    $module = ccModule();
    fakeModerator(SafetyResult::blocked());

    $llm = Mockery::mock(LlmService::class);
    $llm->shouldReceive('chat')->never();   // tutor is never called
    $this->instance(LlmService::class, $llm);

    Livewire::actingAs($student)
        ->test(ClarifyChat::class, ['moduleId' => $module->id])
        ->set('draft', 'something off-topic and unsafe')
        ->call('send')
        ->assertSee('keep our chat about the lesson');
})->group('scenario:AG-12');

/** AG-13 — an unsafe tutor reply is withheld and replaced with a safe fallback. */
it('withholds an unsafe tutor reply', function () {
    $student = ccStudent();
    $module = ccModule();

    // Input passes, output is flagged unsafe.
    $mod = Mockery::mock(ChildSafetyModerator::class);
    $mod->shouldReceive('moderate')->once()->andReturn(SafetyResult::safe());       // input
    $mod->shouldReceive('moderate')->once()->andReturn(SafetyResult::blocked());    // output
    $this->instance(ChildSafetyModerator::class, $mod);

    $llm = Mockery::mock(LlmService::class);
    $llm->shouldReceive('chat')->once()->andReturn('a reply the guard will flag');
    $this->instance(LlmService::class, $llm);

    Livewire::actingAs($student)
        ->test(ClarifyChat::class, ['moduleId' => $module->id])
        ->set('draft', 'a fine question')
        ->call('send')
        ->assertDontSee('a reply the guard will flag')
        ->assertSee('let me put that a better way');
})->group('scenario:AG-13');

/** LE-04 — the lesson's "ask Smooth for more examples" button reaches the chat. */
it('answers when the lesson asks Smooth for more examples', function () {
    $student = ccStudent();
    $module = ccModule();
    fakeModerator(SafetyResult::safe());

    $llm = Mockery::mock(LlmService::class);
    $llm->shouldReceive('chat')->once()->andReturn('Sure — here is another worked example for you. 🐢');
    $this->instance(LlmService::class, $llm);

    Livewire::actingAs($student)
        ->test(ClarifyChat::class, ['moduleId' => $module->id])
        ->call('askSmooth', 'Show me another worked example')
        ->assertSee('here is another worked example');
})->group('scenario:LE-04');

/** LL-15/24/25 — remediation tests only the block's rule: type the word; a miss explains the rule and asks her to say it back. */
it('remediates the block rule with type-the-word + say-it-back, then returns (LL-15)', function () {
    $student = ccStudent();
    $module = ccModule();
    ccStartReteach($student, $module);
    fakeModerator(SafetyResult::safe());

    $llm = Mockery::mock(LlmService::class);
    $llm->shouldReceive('completeJson')->once()->andReturn(['match' => true]);   // say-it-back close enough
    $this->instance(LlmService::class, $llm);

    Livewire::actingAs($student)
        ->test(ClarifyChat::class, ['moduleId' => $module->id])
        ->assertSet('reteach', true)
        ->call('startRemediation', 'consonant then y: change y to i and add es', ['prompt' => "the plural of 'baby'", 'answer' => 'babies'])
        ->assertSet('reteachMode', 'remediation')
        ->assertSet('remStep', 'check')
        ->assertSee('the plural of')                              // the same-rule word to type
        ->set('draft', 'babys')->call('send')                    // wrong -> explain the rule + ask to say it back
        ->assertSet('remStep', 'sayback')
        ->assertSee('change y to i')                             // the block's own rule was explained
        ->set('draft', 'you swap the y for i and add es')->call('send')   // close enough -> return
        ->assertSet('reteachMode', 'dormant')
        ->assertDispatched('remediation-return');
})->group('scenario:LL-15', 'scenario:LL-25');

/** LL-25 — a block with no authored rule must not show an empty "here's the rule" bubble
 *  or ask her to restate nothing: reveal the answer kindly and return to the lesson. */
it('reveals the answer without an empty rule bubble when the block has no rule', function () {
    $student = ccStudent();
    $module = ccModule();
    ccStartReteach($student, $module);
    fakeModerator(SafetyResult::safe());

    $component = Livewire::actingAs($student)
        ->test(ClarifyChat::class, ['moduleId' => $module->id])
        ->call('startRemediation', '', ['prompt' => "the plural of 'baby'", 'answer' => 'babies'])
        ->assertSet('remStep', 'check')
        ->set('draft', 'babys')->call('send');   // wrong, but there is no rule to teach

    $component->assertSet('reteachMode', 'dormant')   // returned to the lesson, no say-it-back
        ->assertSee('babies')                         // revealed the answer instead
        ->assertDontSee("here's the rule")
        ->assertDispatched('remediation-return');

    // No empty assistant bubble slipped into the transcript.
    expect(collect($component->get('messages'))->every(fn ($m) => trim($m['content']) !== ''))->toBeTrue();
})->group('scenario:LL-25');

/** LL-24 — a correct typed same-rule answer returns to the lesson without the say-it-back. */
it('returns to the lesson when she types the right same-rule answer (LL-24)', function () {
    $student = ccStudent();
    $module = ccModule();
    ccStartReteach($student, $module);

    Livewire::actingAs($student)
        ->test(ClarifyChat::class, ['moduleId' => $module->id])
        ->call('startRemediation', 'rule', ['prompt' => "the plural of 'lady'", 'answer' => 'ladies'])
        ->set('draft', ' Ladies ')->call('send')                // case/space-insensitive match
        ->assertSet('reteachMode', 'dormant')
        ->assertDispatched('remediation-return');
})->group('scenario:LL-24');

/** LL-15 — typing when nothing is active nudges her back to the lesson, never a tutor turn. */
it('nudges typing back to the lesson when nothing is active (LL-15)', function () {
    $student = ccStudent();
    $module = ccModule();
    ccStartReteach($student, $module);

    $llm = Mockery::mock(LlmService::class);
    $llm->shouldReceive('chat')->never();   // no tutor turn in a re-teach
    $this->instance(LlmService::class, $llm);

    Livewire::actingAs($student)
        ->test(ClarifyChat::class, ['moduleId' => $module->id])
        ->set('draft', 'just tell me the answer')->call('send')
        ->assertSee('continue the lesson');
})->group('scenario:LL-15');

/** LL-15/24 — the end-of-lesson review uses the LESSON'S OWN practice items, guides on a miss, never gives the answer up front. */
it('reviews the lessons own practice items as guided type-the-answer (LL-15)', function () {
    $student = ccStudent();
    $module = ccModule();
    Lesson::where('module_id', $module->id)->update(['blocks' => [
        ['type' => 'check', 'question' => 'q', 'options' => ['a', 'b'], 'answer' => 1,
            'rule' => 'just add s', 'practiceItems' => [['prompt' => "the plural of 'cat'", 'answer' => 'cats']]],
    ]]);
    ccStartReteach($student, $module);

    Livewire::actingAs($student)
        ->test(ClarifyChat::class, ['moduleId' => $module->id])
        ->call('startFinal')
        ->assertSet('reteachMode', 'final')
        ->assertSee('Type the plural of')            // guided: she types, no answer shown
        ->assertDontSee('boxes')                     // never off-lesson bank content
        ->set('draft', 'catz')->call('send')         // wrong -> a RULE hint, not the answer
        ->assertSee('just add s')
        ->assertSet('reteachMode', 'final')
        ->set('draft', 'cats')->call('send')         // correct -> done
        ->assertSet('reteachMode', 'dormant')
        ->assertDispatched('final-done');
})->group('scenario:LL-15');
