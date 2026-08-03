<?php

use App\Jobs\ScoreWritingSubmission;
use App\Livewire\WritingStop;
use App\Models\StudentProgress;
use App\Models\User;
use App\Models\WritingPrompt;
use App\Models\WritingSubmission;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

function wrStudent(): User
{
    return User::create([
        'name' => 'Maya',
        'email' => 'maya-'.uniqid().'@students.local',
        'password' => 'secret-password',
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

function wrCurrentPrompt(): WritingPrompt
{
    return WritingPrompt::create([
        'week_start_date' => now()->startOfWeek()->toDateString(),
        'title' => 'The Mystery Door',
        'prompt' => 'Write a story about a door that should not have been opened.',
        'type' => 'narrative',
    ]);
}

/** A valid OpenAI-compatible chat completion whose message content is the rubric JSON. */
function wrLlmRubric(array $rubric): array
{
    return ['choices' => [['message' => ['content' => json_encode($rubric)]]]];
}

it('shows this week\'s prompt on the Writer\'s Log', function () {
    $student = wrStudent();
    wrCurrentPrompt();

    Livewire::actingAs($student)
        ->test(WritingStop::class)
        ->assertSeeText('The Mystery Door')
        ->assertSeeText('Write a story about a door that should not have been opened.');
})->group('scenario:WR-01');

it('returns a four-criterion rubric with two strengths and one next step, no grade', function () {
    $student = wrStudent();
    wrCurrentPrompt();

    Http::fake(['openrouter.ai/*' => Http::response(wrLlmRubric([
        'content_score' => 7,
        'language_score' => 7,
        'grammar_score' => 8,
        'organisation_score' => 6,
        'did_well' => ['A strong opening line', 'Vivid describing words'],
        'try_next' => 'Try adding what your character was feeling.',
    ]), 200)]);

    Livewire::actingAs($student)
        ->test(WritingStop::class)
        ->set('body', 'Once upon a rainy afternoon I found a small wooden door behind the shelves...')
        ->call('submit')
        ->assertHasNoErrors()
        // Scored against all four SEA criteria.
        ->assertSeeText('Content')
        ->assertSeeText('Language Use')
        ->assertSeeText('Grammar and Mechanics')
        ->assertSeeText('Organisation')
        // Two things well, one to try.
        ->assertSeeText('A strong opening line')
        ->assertSeeText('Vivid describing words')
        ->assertSeeText('Try adding what your character was feeling.');

    $submission = WritingSubmission::where('student_id', $student->id)->first();
    expect($submission)->not->toBeNull();
    expect($submission->status)->toBe(WritingSubmission::STATUS_SCORED);
    expect($submission->content_score)->toBe(7);
    expect($submission->did_well)->toHaveCount(2);
    expect($submission->try_next)->not->toBeEmpty();

    // No letter grade or pass/fail is stored anywhere.
    expect($submission->getAttributes())->not->toHaveKeys(['grade', 'passed', 'pass_fail']);

    // No module's mastery status changes — the writing track is parallel.
    expect(StudentProgress::where('student_id', $student->id)->count())->toBe(0);
})->group('scenario:WR-02');

it('saves and queues the submission when the AI scorer is unavailable', function () {
    Queue::fake();

    $student = wrStudent();
    wrCurrentPrompt();

    // Provider rate-limited: the LLM returns a 429, so no usable rubric comes back.
    Http::fake(['openrouter.ai/*' => Http::response('rate limited', 429)]);

    Livewire::actingAs($student)
        ->test(WritingStop::class)
        ->set('body', 'Once upon a rainy afternoon I found a small wooden door behind the shelves...')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSeeText('Your feedback is on its way.');

    $submission = WritingSubmission::where('student_id', $student->id)->first();
    expect($submission)->not->toBeNull();
    expect($submission->status)->toBe(WritingSubmission::STATUS_PENDING);

    Queue::assertPushed(ScoreWritingSubmission::class, fn ($job) => $job->submission->is($submission));
})->group('scenario:WR-03');
