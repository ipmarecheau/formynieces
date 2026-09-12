<?php

use App\Models\Lesson;
use App\Models\MobilePracticeSession;
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
    $session = MobilePracticeSession::create([
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

it('returns the Voyage overworld with islands + streak', function () {
    $child = User::factory()->create(['role' => 'student']);
    SyllabusModule::factory()->count(13)->create();
    $token = $child->createToken('t', ['child'])->plainTextToken;

    $res = $this->withToken($token)->getJson('/api/mobile/child/voyage')
        ->assertOk()
        ->assertJsonStructure([
            'child' => ['id', 'name'],
            'streak' => ['days', 'label'],
            'islands' => [['slug', 'name', 'icon', 'conquered', 'total', 'state', 'current']],
        ]);
    expect($res->json('islands.0.slug'))->toBe('feather-isle');
    expect($res->json('islands.0.state'))->toBe('playable'); // first island reachable
});

it('returns a module lesson with renderable blocks, and flags modules without one', function () {
    $child = User::factory()->create(['role' => 'student']);
    $token = $child->createToken('t', ['child'])->plainTextToken;

    $withLesson = SyllabusModule::factory()->create(['topic' => 'Plurals', 'subject' => 'ELA']);
    Lesson::create([
        'module_id' => $withLesson->id, 'title' => 'Tricky plurals', 'is_published' => true,
        'blocks' => [
            ['type' => 'text', 'content' => 'Most words just add -s.'],
            ['type' => 'example', 'content' => 'baby', 'steps' => ['change y to i', 'add es']],
            ['type' => 'check', 'question' => 'plural of city?', 'options' => ['citys', 'cities'], 'answer' => 'cities'],
        ],
    ]);

    $this->withToken($token)->getJson("/api/mobile/child/module/{$withLesson->id}/lesson")
        ->assertOk()
        ->assertJsonPath('has_lesson', true)
        ->assertJsonPath('title', 'Tricky plurals')
        ->assertJsonPath('blocks.0.type', 'text')
        ->assertJsonPath('blocks.1.steps.0', 'change y to i');

    $noLesson = SyllabusModule::factory()->create();
    $this->withToken($token)->getJson("/api/mobile/child/module/{$noLesson->id}/lesson")
        ->assertOk()
        ->assertJsonPath('has_lesson', false);
});

it('returns Captain’s Orders (brief or shore leave)', function () {
    [$child, $module, $token] = childWithMission();

    $this->withToken($token)->getJson('/api/mobile/child/captains-orders')
        ->assertOk()
        ->assertJsonStructure(['title', 'is_writing_day', 'minimum_met', 'rest', 'message', 'duties', 'streak' => ['days', 'label']]);
});

it('opens a playable island’s levels, blocks a locked one, 404s unknown', function () {
    $child = User::factory()->create(['role' => 'student']);
    SyllabusModule::factory()->count(13)->create();
    $token = $child->createToken('t', ['child'])->plainTextToken;

    $this->withToken($token)->getJson('/api/mobile/child/island/feather-isle')
        ->assertOk()
        ->assertJsonStructure([
            'island' => ['slug', 'name', 'icon', 'state', 'conquered', 'total'],
            'levels' => [['id', 'topic', 'subject', 'mastered', 'review', 'mission_id']],
        ]);

    // A later island is locked until the first is conquered.
    $this->withToken($token)->getJson('/api/mobile/child/island/lantern-rock')->assertForbidden();
    $this->withToken($token)->getJson('/api/mobile/child/island/no-such-isle')->assertNotFound();
});
