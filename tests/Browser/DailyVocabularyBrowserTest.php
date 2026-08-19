<?php

use App\Models\ReadingPassage;
use App\Models\User;
use App\Models\VocabularyWord;
use App\Services\LlmService;
use Illuminate\Support\Carbon;

/**
 * Browser (Playwright) verification for the screen-backed Daily Vocabulary scenarios.
 * DV-02/05/06 are @system rules covered by feature tests; not driven here.
 */
beforeEach(function () {
    $this->mock(LlmService::class, fn ($m) => $m->shouldReceive('completeJson')->andReturn([]));
    Carbon::setTestNow(Carbon::parse('2026-08-18 08:00'));
});

function dvStudent(): User
{
    $student = User::factory()->create(['role' => 'student']);
    $student->reading_level = 5;
    $student->save();

    return $student;
}

function dvPassage(): ReadingPassage
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
    VocabularyWord::create(['passage_id' => $passage->id, 'word' => 'beacon', 'definition' => 'a guiding light', 'context_sentence' => 'The beacon shone across the water.']);
    VocabularyWord::create(['passage_id' => $passage->id, 'word' => 'weary', 'definition' => 'very tired', 'context_sentence' => 'A weary crew rowed home.']);

    return $passage;
}

/** Drive the Morning Tide from the reading screen to the word-picking (vocab) phase. */
function dvWalkToWords($page)
{
    return $page->click("I've read it →")
        ->click('A cliff')->click('Next →')
        ->click('Duty')->click('Chart it →');
}

it('DV-01: the day\'s words come from the day\'s passage, each shown in its context sentence', function () {
    $student = dvStudent();
    dvPassage();
    $this->actingAs($student);

    $page = visit('/morning-tide');
    dvWalkToWords($page);

    // Word-pick phase: the words themselves come from the passage.
    $page->assertSee('beacon')
        ->assertSee('weary');

    // Choose both words and move into the build phase, where each word is shown
    // in the context sentence it appeared in (DV-01).
    $page->click('beacon')->click('weary')
        ->click('[wire\\:click="startWriting"]')
        ->assertSee('The beacon shone across the water.')
        ->assertNoJavascriptErrors();
});

it('DV-04: vocabulary is formative — no letter grade or pass/fail on the word screen', function () {
    $student = dvStudent();
    dvPassage();
    $this->actingAs($student);

    $page = visit('/morning-tide');
    dvWalkToWords($page);

    $page->assertDontSee('Grade')
        ->assertDontSee('Pass')
        ->assertDontSee('Fail');
});
