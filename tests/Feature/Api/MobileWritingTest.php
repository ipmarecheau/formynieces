<?php

use App\Models\User;
use App\Models\WritingPrompt;
use App\Models\WritingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/** @return array{0: User, 1: WritingPrompt, 2: string} */
function childWithPrompt(): array
{
    $child = User::factory()->create(['role' => 'student']);
    $prompt = WritingPrompt::create([
        'week_start_date' => now()->startOfWeek()->toDateString(),
        'title' => 'The Mystery Door',
        'prompt' => 'Write a story about a door that should not have been opened.',
        'type' => 'narrative',
    ]);

    return [$child, $prompt, $child->createToken('t', ['child'])->plainTextToken];
}

/** An OpenAI-compatible completion whose message content is the rubric JSON. */
function wrRubricResponse(array $rubric): array
{
    return ['choices' => [['message' => ['content' => json_encode($rubric)]]]];
}

it('shows this week’s Writer’s Log prompt (WR-01)', function () {
    [$child, $prompt, $token] = childWithPrompt();

    $this->withToken($token)->getJson('/api/mobile/child/writing')
        ->assertOk()
        ->assertJsonPath('has_prompt', true)
        ->assertJsonPath('prompt.title', 'The Mystery Door')
        ->assertJsonPath('submission', null)
        ->assertJsonStructure(['has_prompt', 'prompt' => ['id', 'title', 'prompt'], 'queued']);
});

it('gives a calm empty state when there is no prompt this week', function () {
    $child = User::factory()->create(['role' => 'student']);
    $token = $child->createToken('t', ['child'])->plainTextToken;

    $this->withToken($token)->getJson('/api/mobile/child/writing')
        ->assertOk()
        ->assertJsonPath('has_prompt', false)
        ->assertJsonStructure(['has_prompt', 'message']);
});

it('submits a draft and returns the four-criterion rubric, no grade (WR-02)', function () {
    [$child, $prompt, $token] = childWithPrompt();

    Http::fake(['openrouter.ai/*' => Http::response(wrRubricResponse([
        'content_score' => 7,
        'language_score' => 7,
        'grammar_score' => 8,
        'organisation_score' => 6,
        'did_well' => ['A strong opening line', 'Vivid describing words'],
        'try_next' => 'Try adding what your character was feeling.',
    ]), 200)]);

    $this->withToken($token)->postJson('/api/mobile/child/writing', [
        'body' => 'Once upon a rainy afternoon I found a small wooden door behind the shelves.',
    ])
        ->assertOk()
        ->assertJsonPath('queued', false)
        ->assertJsonPath('submission.scored', true)
        ->assertJsonPath('submission.rubric.Content', 7)
        ->assertJsonPath('submission.did_well.0', 'A strong opening line')
        ->assertJsonPath('submission.try_next', 'Try adding what your character was feeling.');

    // The writing duty is checked off regardless of scoring outcome (WR-06).
    $this->assertDatabaseHas(WritingSubmission::class, [
        'student_id' => $child->id, 'writing_prompt_id' => $prompt->id, 'status' => 'scored',
    ]);
});

it('rejects a too-short draft (WR-02 validation)', function () {
    [$child, $prompt, $token] = childWithPrompt();

    $this->withToken($token)->postJson('/api/mobile/child/writing', ['body' => 'too short'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('body');
});
