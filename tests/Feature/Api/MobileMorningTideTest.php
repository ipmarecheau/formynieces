<?php

use App\Models\DailyReadingAssignment;
use App\Models\ReadingPassage;
use App\Models\User;
use App\Models\VocabularyWord;
use App\Services\LlmService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // LLM unavailable → deterministic MC baseline + authored example fallback.
    $this->mock(LlmService::class, fn ($m) => $m->shouldReceive('completeJson')->andReturn([]));
});

/**
 * A child with a reading level and an active passage carrying two MC questions and
 * two vocabulary words.
 *
 * @return array{0: User, 1: ReadingPassage, 2: string}
 */
function childWithPassage(): array
{
    $child = User::factory()->create(['role' => 'student']);
    $child->reading_level = 5;
    $child->save();

    $passage = ReadingPassage::create([
        'title' => 'The Lighthouse',
        'body' => str_repeat('word ', 100),
        'reading_level' => 5,
        'word_count' => 100,
        'questions' => [
            ['prompt' => 'Q1', 'type' => 'mc', 'options' => ['a', 'b'], 'correct_index' => 0],
            ['prompt' => 'Q2', 'type' => 'mc', 'options' => ['a', 'b'], 'correct_index' => 1],
        ],
        'is_active' => true,
    ]);
    VocabularyWord::create(['passage_id' => $passage->id, 'word' => 'beacon', 'definition' => 'a light', 'context_sentence' => 'The beacon shone.']);
    VocabularyWord::create(['passage_id' => $passage->id, 'word' => 'weary', 'definition' => 'tired', 'context_sentence' => 'A weary crew.']);

    return [$child, $passage, $child->createToken('t', ['child'])->plainTextToken];
}

it('serves today’s Morning Tide passage and questions (DR-01)', function () {
    [$child, $passage, $token] = childWithPassage();

    $this->withToken($token)->getJson('/api/mobile/child/morning-tide')
        ->assertOk()
        ->assertJsonPath('has_passage', true)
        ->assertJsonPath('passage.title', 'The Lighthouse')
        ->assertJsonPath('passage.reading_level', 5)
        ->assertJsonCount(2, 'questions')
        ->assertJsonPath('questions.0.prompt', 'Q1')
        ->assertJsonStructure(['assignment_id', 'completed', 'passage' => ['title', 'body', 'word_count'], 'questions' => [['prompt', 'type', 'options']]]);
});

it('gives a calm empty state when no passage is available', function () {
    $child = User::factory()->create(['role' => 'student']);
    $token = $child->createToken('t', ['child'])->plainTextToken;

    $this->withToken($token)->getJson('/api/mobile/child/morning-tide')
        ->assertOk()
        ->assertJsonPath('has_passage', false)
        ->assertJsonStructure(['has_passage', 'message']);
});

it('scores comprehension and returns vocabulary candidates (DR-07/DV)', function () {
    [$child, $passage, $token] = childWithPassage();

    $assignmentId = $this->withToken($token)->getJson('/api/mobile/child/morning-tide')->json('assignment_id');

    $this->withToken($token)->postJson('/api/mobile/child/morning-tide/comprehension', [
        'assignment_id' => $assignmentId,
        'answers' => [0, 1], // both correct → 100%
    ])
        ->assertOk()
        ->assertJsonPath('score', 100)
        ->assertJsonStructure(['score', 'feedback', 'words' => [['id', 'word', 'definition', 'example']]]);

    $this->assertDatabaseHas(DailyReadingAssignment::class, [
        'id' => $assignmentId, 'comprehension_score' => 100,
    ]);
});

it('records vocabulary sentences and completes the ritual duty (DV)', function () {
    [$child, $passage, $token] = childWithPassage();

    $assignmentId = $this->withToken($token)->getJson('/api/mobile/child/morning-tide')->json('assignment_id');
    $this->withToken($token)->postJson('/api/mobile/child/morning-tide/comprehension', [
        'assignment_id' => $assignmentId, 'answers' => [0, 1],
    ])->assertOk();

    $beacon = VocabularyWord::where('word', 'beacon')->first();
    $weary = VocabularyWord::where('word', 'weary')->first();

    $this->withToken($token)->postJson('/api/mobile/child/morning-tide/vocabulary', [
        'assignment_id' => $assignmentId,
        'sentences' => [
            ['word_id' => $beacon->id, 'sentence' => 'The beacon guided the ship.'],
            ['word_id' => $weary->id, 'sentence' => 'I felt weary after the climb.'],
        ],
    ])
        ->assertOk()
        ->assertJsonPath('done', true)
        ->assertJsonPath('word_usage.0.used', true) // sentence contains 'beacon'
        ->assertJsonStructure(['done', 'message', 'word_usage' => [['word', 'sentence', 'used', 'example']]]);

    // Her sentences are kept on the assignment for the diary.
    expect(DailyReadingAssignment::find($assignmentId)->vocab_sentences)
        ->toHaveKey((string) $beacon->id);
});

it('forbids scoring another child’s reading (403)', function () {
    [$childA, $passage] = childWithPassage();
    $assignment = DailyReadingAssignment::create([
        'student_id' => $childA->id, 'passage_id' => $passage->id,
        'date' => now()->toDateString(), 'answers' => [], 'started_at' => now(),
    ]);

    $tokenB = User::factory()->create(['role' => 'student'])->createToken('t', ['child'])->plainTextToken;
    $this->withToken($tokenB)->postJson('/api/mobile/child/morning-tide/comprehension', [
        'assignment_id' => $assignment->id, 'answers' => [0, 1],
    ])->assertForbidden();
});
