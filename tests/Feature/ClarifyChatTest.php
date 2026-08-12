<?php

use App\Livewire\ClarifyChat;
use App\Models\Lesson;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\LlmService;
use App\Services\Safety\ChildSafetyModerator;
use App\Services\Safety\SafetyResult;
use Livewire\Livewire;

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
