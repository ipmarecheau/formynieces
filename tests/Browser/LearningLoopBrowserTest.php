<?php

use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WorkedExample;
use App\Services\LlmService;

/**
 * Browser (Playwright) verification for the screen-backed Learning Loop /
 * Tutorial scenarios. The mastery-climb and reteach rules (LL-10/11/17/24/25)
 * are logic covered by their own feature tests.
 */
beforeEach(function () {
    $this->mock(LlmService::class, fn ($m) => $m->shouldReceive('completeJson')->andReturn([]));
});

function llModuleWithTutorial(User $student): SyllabusModule
{
    $module = SyllabusModule::query()->create([
        'subject' => 'Math',
        'topic' => 'Number: Place Value',
        'sea_section' => 'A',
        'sequence_order' => 1,
        'pacing_week' => 1,
        'description' => 'Understand the value of each digit in a whole number.',
        'resources' => [
            ['label' => 'Place Value Video', 'url' => 'https://example.test/pv'],
        ],
    ]);

    StudentProgress::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'status' => 'needs_work',
    ]);

    $question = PracticeQuestion::factory()->create([
        'module_id' => $module->id,
        'difficulty' => 1,
        'prompt' => 'What is 24 + 18?',
        'options' => ['42', '32', '44', '38'],
        'correct_index' => 0,
        'explanation' => 'Add the tens, then the ones.',
    ]);

    WorkedExample::create([
        'practice_question_id' => $question->id,
        'source' => 'authored',
        'steps' => [
            'First, add the tens: twenty plus ten is thirty.',
            'Next, add the ones: four plus eight is twelve.',
            'Finally, combine them: thirty plus twelve is forty-two.',
        ],
    ]);

    return $module;
}

it('LL-01: the lesson shows the module description and its human-vetted resources', function () {
    $student = User::factory()->create(['role' => 'student', 'onboarding_completed_at' => now()]);
    $module = llModuleWithTutorial($student);
    $this->actingAs($student);

    $page = visit("/practice/{$module->id}/lesson");

    // Resources live in an optional "go deeper" disclosure; expand it to see them.
    $page->assertNoJavascriptErrors()
        ->assertSee('Understand the value of each digit in a whole number.')
        ->click('.lw-deeper summary')
        ->assertSee('Place Value Video');
});

it('TU-02: a worked example reveals the method one step at a time', function () {
    $student = User::factory()->create(['role' => 'student', 'onboarding_completed_at' => now()]);
    $module = llModuleWithTutorial($student);
    $this->actingAs($student);

    $page = visit("/practice/{$module->id}/tutorial");

    // Only the first step is shown initially; advancing reveals the next in order.
    $page->assertNoJavascriptErrors()
        ->assertSee('First, add the tens')
        ->assertDontSee('Finally, combine them')
        ->click('Next step →')
        ->assertSee('Next, add the ones');
});

it('TU-01: the worked example leads on to practice once fully revealed', function () {
    $student = User::factory()->create(['role' => 'student', 'onboarding_completed_at' => now()]);
    $module = llModuleWithTutorial($student);
    $this->actingAs($student);

    $page = visit("/practice/{$module->id}/tutorial");

    $page->click('Next step →')  // reveal step 2
        ->click('Next step →')   // reveal step 3 (all)
        ->assertSee('Start practising')
        ->assertNoJavascriptErrors();
});
