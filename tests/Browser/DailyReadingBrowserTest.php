<?php

use App\Models\ReadingPassage;
use App\Models\User;
use App\Models\VocabularyWord;
use App\Services\LlmService;
use Illuminate\Support\Carbon;

/**
 * Browser (Playwright) verification for the screen-backed Daily Reading scenarios.
 * The @system rules (DR-04/05/08/09) are covered by feature tests and have no
 * distinct screen; they are intentionally not driven here.
 */
beforeEach(function () {
    // LLM unavailable → MC baseline + authored fallback, so no real Groq/OpenRouter call.
    $this->mock(LlmService::class, fn ($m) => $m->shouldReceive('completeJson')->andReturn([]));
    Carbon::setTestNow(Carbon::parse('2026-08-18 08:00')); // a Tuesday morning
});

function drBrowserStudent(): User
{
    $student = User::factory()->create(['role' => 'student']);
    $student->reading_level = 5;
    $student->save();

    return $student;
}

function drBrowserPassage(): ReadingPassage
{
    $passage = ReadingPassage::create([
        'title' => 'The Lighthouse Keeper',
        'body' => str_repeat('word ', 100),
        'reading_level' => 5,
        'word_count' => 100,
        'questions' => [
            ['prompt' => 'Where did the story take place?', 'type' => 'mc', 'options' => ['A cliff', 'A cave'], 'correct_index' => 0],
            ['prompt' => 'Why did the keeper stay?', 'type' => 'mc', 'options' => ['Duty', 'Fear'], 'correct_index' => 0],
        ],
        'is_active' => true,
    ]);
    VocabularyWord::create(['passage_id' => $passage->id, 'word' => 'beacon', 'definition' => 'a light', 'context_sentence' => 'The beacon shone.']);
    VocabularyWord::create(['passage_id' => $passage->id, 'word' => 'weary', 'definition' => 'tired', 'context_sentence' => 'A weary crew.']);

    return $passage;
}

it('DR-01/DR-11: serves a level-matched passage inside the three-step Morning Tide ritual', function () {
    $student = drBrowserStudent();
    drBrowserPassage();
    $this->actingAs($student);

    $page = visit('/morning-tide');

    $page->assertNoJavascriptErrors()
        ->assertSee('The Morning Tide')
        ->assertSee('The Lighthouse Keeper') // one served passage, matched to level 5
        ->assertSee('Reading')               // three-step progress indicator (DR-11)
        ->assertSee('Questions')
        ->assertSee('Words');
});

it('DR-02: a comprehension check with real questions follows the passage', function () {
    $student = drBrowserStudent();
    drBrowserPassage();
    $this->actingAs($student);

    $page = visit('/morning-tide');

    $page->assertSee('The Lighthouse Keeper')
        ->click("I've read it →")
        ->assertSee('Where did the story take place?')
        ->assertNoJavascriptErrors();
});

it('DR-03: comprehension is formative — no letter grade or pass/fail is shown to her', function () {
    $student = drBrowserStudent();
    drBrowserPassage();
    $this->actingAs($student);

    $page = visit('/morning-tide');

    // No grading language surfaces on the reading screen.
    $page->assertDontSee('Grade')
        ->assertDontSee('Pass')
        ->assertDontSee('Fail')
        ->assertDontSee('%');
});
