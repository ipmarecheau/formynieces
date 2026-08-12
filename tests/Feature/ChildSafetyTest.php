<?php

use App\Models\SafetyFlag;
use App\Models\User;
use App\Services\Safety\ChildSafetyModerator;
use Illuminate\Support\Facades\Http;

function csStudent(string $suffix): User
{
    return User::create([
        'name' => 'Maya',
        'email' => "maya-cs-{$suffix}@test.com",
        'password' => bcrypt('secret'),
        'role' => 'student',
    ]);
}

/** Fake the Llama Guard verdict returned for the next moderation call. */
function fakeGuard(string $verdict): void
{
    config(['services.llm.key' => 'test-key']);
    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => $verdict]]],
            'usage' => ['prompt_tokens' => 200, 'completion_tokens' => 5],
        ], 200),
    ]);
}

/** AG-12/13 — a clearly safe message passes moderation. */
it('passes a safe message', function () {
    $student = csStudent('safe');
    fakeGuard('safe');

    $result = app(ChildSafetyModerator::class)->moderate('How do I add fractions?', $student->id);

    expect($result->safe)->toBeTrue();
    expect(SafetyFlag::count())->toBe(0);
})->group('scenario:AG-12');

/** AG-13 — unsafe content is withheld; a non-concerning category does not escalate. */
it('withholds unsafe content without escalating a non-concerning category', function () {
    $student = csStudent('hate');
    fakeGuard("unsafe\nS10");   // hate — unsafe but not an escalation category

    $result = app(ChildSafetyModerator::class)->moderate('a rude message', $student->id);

    expect($result->safe)->toBeFalse()
        ->and($result->concerning)->toBeFalse();
    expect(SafetyFlag::count())->toBe(0);
})->group('scenario:AG-13');

/** AG-15 — a concerning category is withheld AND escalated to a trusted adult. */
it('withholds and escalates a concerning message', function () {
    $student = csStudent('concern');
    fakeGuard("unsafe\nS11");   // self-harm

    $result = app(ChildSafetyModerator::class)->moderate('a worrying message', $student->id);

    expect($result->safe)->toBeFalse()
        ->and($result->concerning)->toBeTrue()
        ->and($result->category)->toBe('self-harm');

    $flag = SafetyFlag::where('student_id', $student->id)->first();
    expect($flag)->not->toBeNull()
        ->and($flag->category)->toBe('self-harm');
})->group('scenario:AG-15');

/** AG-14 — if the classifier is unavailable, moderation fails CLOSED (content withheld). */
it('fails closed when the classifier is unavailable', function () {
    $student = csStudent('down');
    config(['services.llm.key' => 'test-key']);
    Http::fake(['*/chat/completions' => Http::response('', 500)]);

    $result = app(ChildSafetyModerator::class)->moderate('anything', $student->id);

    expect($result->safe)->toBeFalse();   // withheld, never passed through unmoderated
})->group('scenario:AG-14');
