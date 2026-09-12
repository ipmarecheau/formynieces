<?php

use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function childWithMission(): array
{
    $child = User::factory()->create(['role' => 'student']);
    $module = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Equivalent fractions']);
    StudentProgress::create(['student_id' => $child->id, 'module_id' => $module->id, 'status' => 'needs_work', 'current_rung' => 1]);

    // Five D1 questions, correct answer always 'B' (index 1) for deterministic tests.
    PracticeQuestion::factory()->count(5)->create([
        'module_id' => $module->id, 'difficulty' => 1,
        'options' => ['A', 'B', 'C', 'D'], 'correct_index' => 1, 'explanation' => 'Because.',
    ]);

    return [$child, $module, $child->createToken('t', ['child'])->plainTextToken];
}

it('shows today’s mission for a child (MC-01)', function () {
    [$child, $module, $token] = childWithMission();

    $this->withToken($token)->getJson('/api/mobile/child/today')
        ->assertOk()
        ->assertJsonPath('mission.id', "m{$module->id}")
        ->assertJsonPath('mission.topic', 'Equivalent fractions')
        ->assertJsonStructure(['child' => ['id', 'name'], 'smooth_message', 'mission' => ['id', 'subject', 'topic', 'estimated_minutes', 'cta'], 'streak', 'voyage']);
});

it('runs a full practice session: start → answer → finish (MC-03/04/05)', function () {
    [$child, $module, $token] = childWithMission();

    $start = $this->withToken($token)->postJson('/api/mobile/child/practice/start', ['mission_id' => "m{$module->id}"])
        ->assertOk()
        ->assertJsonPath('total_questions', 5)
        ->assertJsonStructure(['session_id', 'total_questions', 'current_question' => ['id', 'prompt', 'choices']]);

    $sessionId = $start->json('session_id');
    $q = $start->json('current_question');

    for ($i = 0; $i < 5; $i++) {
        $res = $this->withToken($token)->postJson("/api/mobile/child/practice/{$sessionId}/answer", [
            'question_id' => $q['id'], 'choice_id' => 'B',
        ])->assertOk()->assertJsonPath('correct', true)
            ->assertJsonPath('progress.answered', $i + 1);

        $q = $res->json('next_question');
    }
    expect($q)->toBeNull();

    // Attempts were recorded against the real learning record (MC-03).
    $this->assertDatabaseCount('practice_attempts', 5);

    $this->withToken($token)->postJson("/api/mobile/child/practice/{$sessionId}/finish")
        ->assertOk()
        ->assertJsonPath('accuracy', 100)
        ->assertJsonStructure(['accuracy', 'celebration', 'streak' => ['days', 'label'], 'next_action']);
});

it('rejects an out-of-order answer', function () {
    [$child, $module, $token] = childWithMission();
    $start = $this->withToken($token)->postJson('/api/mobile/child/practice/start', ['mission_id' => "m{$module->id}"]);
    $sessionId = $start->json('session_id');

    $this->withToken($token)->postJson("/api/mobile/child/practice/{$sessionId}/answer", [
        'question_id' => 999999, 'choice_id' => 'B',
    ])->assertStatus(422);
});

it('forbids using another child’s session (403)', function () {
    // Build childA's session directly (no API auth first) so the guard resolves childB's
    // token cleanly on the request under test (avoids Sanctum's in-process user caching).
    [$childA, $module] = childWithMission();
    $session = App\Models\MobilePracticeSession::create([
        'student_id' => $childA->id, 'module_id' => $module->id,
        'question_ids' => [1], 'position' => 0, 'answers' => [],
    ]);

    $tokenB = User::factory()->create(['role' => 'student'])->createToken('t', ['child'])->plainTextToken;
    $this->withToken($tokenB)->postJson("/api/mobile/child/practice/{$session->id}/finish")->assertForbidden();
});

it('blocks a parent-scoped token from child endpoints (MP-07/MC-06)', function () {
    $parentToken = User::factory()->create(['role' => 'guardian'])->createToken('t', ['parent'])->plainTextToken;
    $this->withToken($parentToken)->getJson('/api/mobile/child/today')->assertForbidden();
});
