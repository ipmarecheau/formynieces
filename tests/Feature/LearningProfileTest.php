<?php

use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\LearningProfile;
use App\Services\Practice\WorkedExampleGenerator;
use Illuminate\Support\Facades\Http;

/**
 * AG-08 — a compact per-student learning profile (derived tags, never transcripts, never
 * PII) personalises the AI tutor prompts.
 */
function lpStudent(): User
{
    return User::create([
        'name' => 'Maya',
        'email' => 'maya-lp@test.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
    ]);
}

it('builds a compact tailoring context from derived tags, storing no conversation', function () {
    $student = lpStudent();
    $student->known_weak_areas = ['fractions', 'place value'];
    $student->learning_profile = ['responds to concrete examples', 'rushes multi-step problems'];
    $student->save();

    $context = app(LearningProfile::class)->promptContext($student);

    expect($context)->toContain('fractions')
        ->and($context)->toContain('concrete examples');

    // The profile holds only short tags — no transcript, no PII.
    expect($student->fresh()->learning_profile)->toBeArray()->each->toBeString();
})->group('scenario:AG-08');

it('remembers a new tag without duplicating and keeps the profile compact', function () {
    $student = lpStudent();

    $profile = app(LearningProfile::class);
    $profile->remember($student, 'likes visual hints');
    $profile->remember($student, 'likes visual hints');   // duplicate ignored

    expect($student->fresh()->learning_profile)->toBe(['likes visual hints']);
})->group('scenario:AG-08');

it('injects the learning profile into the tutor prompt', function () {
    $student = lpStudent();
    $student->learning_profile = ['responds to concrete examples'];
    $student->save();

    $module = SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Number: Place Value', 'sea_section' => 'A',
        'sequence_order' => 1, 'pacing_week' => 1, 'description' => 'x', 'resources' => [],
    ]);
    $question = PracticeQuestion::create([
        'module_id' => $module->id, 'subject' => 'Math', 'sea_section' => 'A',
        'difficulty' => 1, 'prompt' => 'What is 2 + 2?', 'options' => ['3', '4', '5', '6'],
        'correct_index' => 1, 'explanation' => 'Add them. The answer is four.', 'is_active' => true,
    ]);

    config(['services.llm.key' => 'test-key']);
    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => "Add them.\nThe answer is 4."]]],
            'usage' => ['prompt_tokens' => 20, 'completion_tokens' => 10],
        ], 200),
    ]);

    app(WorkedExampleGenerator::class)->forQuestion($question, $student);

    Http::assertSent(fn ($request) => str_contains(json_encode($request->data()), 'concrete examples'));
})->group('scenario:AG-08');
