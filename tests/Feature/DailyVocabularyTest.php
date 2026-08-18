<?php

use App\Models\ReadingPassage;
use App\Models\StudentProgress;
use App\Models\User;
use App\Models\VocabularyWord;
use App\Services\LlmService;
use App\Services\Reading\VocabularyService;
use Illuminate\Support\Carbon;

function dvStudent(): User
{
    return User::factory()->create(['role' => 'student']);
}

function dvPassageWithWords(int $count = 3): ReadingPassage
{
    $passage = ReadingPassage::create([
        'title' => 'P', 'body' => 'body', 'reading_level' => 5,
        'word_count' => 100, 'questions' => [], 'is_active' => true,
    ]);
    for ($i = 1; $i <= $count; $i++) {
        VocabularyWord::create([
            'passage_id' => $passage->id,
            'word' => "word{$i}", 'definition' => "def{$i}", 'context_sentence' => "context {$i}",
        ]);
    }

    return $passage;
}

function dvSvc(): VocabularyService
{
    return app(VocabularyService::class);
}

it('draws today\'s words from the day\'s passage', function () {
    $student = dvStudent();
    $passage = dvPassageWithWords(3);

    $words = dvSvc()->wordsForToday($student->id, $passage);

    expect($words)->toHaveCount(3)
        ->and($words->pluck('word')->all())->toContain('word1');
})->group('scenario:DV-01');

it('reschedules a word later when right and sooner when wrong', function () {
    $student = dvStudent();
    $word = dvPassageWithWords(1)->vocabularyWords()->first();
    $on = Carbon::parse('2026-08-17');

    $right = dvSvc()->recordResult($student->id, $word->id, true, $on);
    expect($right->interval_days)->toBe(2)
        ->and($right->due_at->toDateString())->toBe('2026-08-19');

    $wrong = dvSvc()->recordResult($student->id, $word->id, false, $on);
    expect($wrong->interval_days)->toBe(1)
        ->and($wrong->due_at->toDateString())->toBe('2026-08-18')
        ->and($wrong->correct_streak)->toBe(0);
})->group('scenario:DV-03');

it('resurfaces due words alongside new ones', function () {
    $student = dvStudent();
    $reviewed = dvPassageWithWords(1)->vocabularyWords()->first();
    dvSvc()->recordResult($student->id, $reviewed->id, false, Carbon::parse('2026-08-16')); // due 2026-08-17

    $todaysPassage = dvPassageWithWords(2);
    $set = dvSvc()->wordsForToday($student->id, $todaysPassage, Carbon::parse('2026-08-17'));

    expect($set->pluck('id'))->toContain($reviewed->id);
})->group('scenario:DV-03');

it('caps the daily set so the ritual stays short', function () {
    $student = dvStudent();
    $passage = dvPassageWithWords(10);

    expect(dvSvc()->wordsForToday($student->id, $passage))->toHaveCount(6);
})->group('scenario:DV-05');

it('gives two LLM example sentences for a word', function () {
    $this->mock(LlmService::class, fn ($m) => $m->shouldReceive('completeJson')
        ->andReturn(['examples' => ['One.', 'Two.', 'Three.']]));
    $word = dvPassageWithWords(1)->vocabularyWords()->first();

    expect(app(VocabularyService::class)->exampleSentences($word))->toBe(['One.', 'Two.']);
})->group('scenario:DV-02');

it('falls back to the context sentence when the LLM is unavailable', function () {
    $this->mock(LlmService::class, fn ($m) => $m->shouldReceive('completeJson')->andReturn([]));
    $word = dvPassageWithWords(1)->vocabularyWords()->first();

    expect(app(VocabularyService::class)->exampleSentences($word))->toBe([$word->context_sentence]);
})->group('scenario:DV-02');

/**
 * DV-04 — vocabulary is formative and feeds writing, not mastery: recording a word
 * result advances only the spaced schedule and never changes a module's mastery.
 */
it('records a vocabulary result without changing any module mastery', function () {
    $student = dvStudent();
    $passage = dvPassageWithWords();
    $word = $passage->vocabularyWords()->first();

    dvSvc()->recordResult($student->id, $word->id, correct: true);

    expect(StudentProgress::where('student_id', $student->id)->exists())->toBeFalse();
})->group('scenario:DV-04');
