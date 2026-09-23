<?php

use App\Models\Lesson;
use App\Models\MobilePracticeSession;
use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Practice\CompetencyCheck;
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

    // Answer correctly until the adaptive climb stops serving (a rung is cleared or the
    // bank at the current rung is exhausted). The bounded loop guards against a runaway.
    $answered = 0;
    while ($q !== null && $answered < 20) {
        $res = $this->withToken($token)->postJson("/api/mobile/child/practice/{$sessionId}/answer", [
            'question_id' => $q['id'], 'choice_id' => 'B',
        ])->assertOk()->assertJsonPath('correct', true);

        $answered++;
        $q = $res->json('next_question');
    }
    expect($answered)->toBeGreaterThan(0);

    // Each answer was recorded against the real learning record (MC-03).
    $this->assertDatabaseCount('practice_attempts', $answered);

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
        ->assertJsonStructure([
            'orders' => ['title', 'is_writing_day', 'minimum_met', 'rest', 'message', 'duties', 'lesson_tasks'],
            'locker' => [['type', 'icon', 'label', 'blurb', 'earn', 'held']],
            'logs',
            'journal',
            'streak' => ['days', 'label'],
        ])
        ->assertJsonCount(4, 'locker');
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

it('returns the welcome-back splash with streaks (SH-06)', function () {
    $child = User::factory()->create(['role' => 'student', 'name' => 'Ava']);
    StudentStreak::create(['student_id' => $child->id, 'type' => 'practice', 'count' => 3]);
    StudentStreak::create(['student_id' => $child->id, 'type' => 'login', 'count' => 5]);
    $token = $child->createToken('t', ['child'])->plainTextToken;

    $this->withToken($token)->getJson('/api/mobile/child/welcome-back')
        ->assertOk()
        ->assertJsonPath('child.name', 'Ava')
        ->assertJsonPath('streaks.practice', 3)
        ->assertJsonPath('streaks.login', 5)
        ->assertJsonStructure(['child' => ['id', 'name'], 'streaks' => ['voyage', 'practice', 'login', 'mastery', 'pace_weeks'], 'milestone', 'message']);
});

// --- Learning-loop transitions (LL-14/LL-20/LL-21) -------------------------------------

/**
 * A module with a full competency-check bank: two UNSEEN questions at each of D1/D3/D5
 * (unique prompts so their content hashes differ — the check serves by no-repeat hash).
 * Correct answer is always 'B' (index 1) for deterministic answering.
 *
 * @return array{0: User, 1: SyllabusModule, 2: string}
 */
function childWithCheckBank(): array
{
    $child = User::factory()->create(['role' => 'student']);
    $module = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Place value']);

    foreach (CompetencyCheck::DIFFICULTIES as $difficulty) {
        for ($n = 0; $n < CompetencyCheck::QUESTIONS_PER_DIFFICULTY; $n++) {
            PracticeQuestion::factory()->create([
                'module_id' => $module->id,
                'difficulty' => $difficulty,
                'prompt' => "Check D{$difficulty} #{$n}: which is right?",
                'options' => ['A', 'B', 'C', 'D'],
                'correct_index' => 1,
                'explanation' => 'Because.',
            ]);
        }
    }

    return [$child, $module, $child->createToken('t', ['child'])->plainTextToken];
}

it('tests out of a module: a clean competency check masters it without a lesson (LL-20)', function () {
    [$child, $module, $token] = childWithCheckBank();

    $start = $this->withToken($token)->postJson("/api/mobile/child/module/{$module->id}/check/start")
        ->assertOk()
        ->assertJsonPath('total_questions', 6)
        ->assertJsonStructure(['session_id', 'total_questions', 'current_question' => ['id', 'prompt', 'choices']]);

    $sessionId = $start->json('session_id');
    $q = $start->json('current_question');

    // Answer all six correctly on the first try.
    for ($i = 0; $i < 6; $i++) {
        $res = $this->withToken($token)->postJson("/api/mobile/child/check/{$sessionId}/answer", [
            'question_id' => $q['id'], 'choice_id' => 'B',
        ])->assertOk();

        if ($i < 5) {
            $res->assertJsonPath('done', false)->assertJsonPath('progress.answered', $i + 1);
            $q = $res->json('next_question');
        } else {
            $res->assertJsonPath('done', true)->assertJsonPath('mastered', true);
        }
    }

    expect(StudentProgress::where('student_id', $child->id)->where('module_id', $module->id)->value('status'))
        ->toBe('mastered');
});

it('does not master when the competency check is missed (LL-21)', function () {
    [$child, $module, $token] = childWithCheckBank();

    $start = $this->withToken($token)->postJson("/api/mobile/child/module/{$module->id}/check/start");
    $sessionId = $start->json('session_id');
    $q = $start->json('current_question');

    // Miss the very first question ('A' is wrong), then answer the rest correctly.
    for ($i = 0; $i < 6; $i++) {
        $res = $this->withToken($token)->postJson("/api/mobile/child/check/{$sessionId}/answer", [
            'question_id' => $q['id'], 'choice_id' => $i === 0 ? 'A' : 'B',
        ])->assertOk();
        $q = $res->json('next_question');
    }

    expect(StudentProgress::where('student_id', $child->id)->where('module_id', $module->id)->value('status'))
        ->not->toBe('mastered');
});

it('offers a second try on a first-try practice miss (LL-14)', function () {
    [$child, $module, $token] = childWithMission();

    $start = $this->withToken($token)->postJson('/api/mobile/child/practice/start', ['mission_id' => "m{$module->id}"]);
    $sessionId = $start->json('session_id');
    $q = $start->json('current_question');

    // 'A' (index 0) is wrong; the correct answer is 'B'.
    $this->withToken($token)->postJson("/api/mobile/child/practice/{$sessionId}/answer", [
        'question_id' => $q['id'], 'choice_id' => 'A',
    ])
        ->assertOk()
        ->assertJsonPath('correct', false)
        ->assertJsonPath('retry', true)
        ->assertJsonPath('next_question', null);
});

it('loops back to reteach after missing both practice tries (LL-14)', function () {
    [$child, $module, $token] = childWithMission();

    $start = $this->withToken($token)->postJson('/api/mobile/child/practice/start', ['mission_id' => "m{$module->id}"]);
    $sessionId = $start->json('session_id');
    $q = $start->json('current_question');

    // First miss → retry offered on the same question.
    $this->withToken($token)->postJson("/api/mobile/child/practice/{$sessionId}/answer", [
        'question_id' => $q['id'], 'choice_id' => 'A',
    ])->assertJsonPath('retry', true);

    // Second miss on the same question → reteach.
    $this->withToken($token)->postJson("/api/mobile/child/practice/{$sessionId}/answer", [
        'question_id' => $q['id'], 'choice_id' => 'A',
    ])
        ->assertOk()
        ->assertJsonPath('correct', false)
        ->assertJsonPath('reteach', true)
        ->assertJsonPath('next_question', null);
});

it('masters a module when the final D5 streak clears in practice', function () {
    $child = User::factory()->create(['role' => 'student']);
    $module = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Ratios']);

    // Already at the mastery rung with two of the three first-try-correct banked.
    StudentProgress::create([
        'student_id' => $child->id, 'module_id' => $module->id,
        'status' => 'needs_work', 'current_rung' => 5, 'current_streak' => 2,
        'streak_question_ids' => [900001, 900002],
    ]);

    // Unseen D5 questions to serve; the next first-try-correct carries her to mastery.
    PracticeQuestion::factory()->count(2)->sequence(
        ['prompt' => 'D5 mastery A?'],
        ['prompt' => 'D5 mastery B?'],
    )->create([
        'module_id' => $module->id, 'difficulty' => 5,
        'options' => ['A', 'B', 'C', 'D'], 'correct_index' => 1, 'explanation' => 'Mastered.',
    ]);

    $token = $child->createToken('t', ['child'])->plainTextToken;

    $start = $this->withToken($token)->postJson('/api/mobile/child/practice/start', ['mission_id' => "m{$module->id}"]);
    $q = $start->json('current_question');
    $sessionId = $start->json('session_id');

    $this->withToken($token)->postJson("/api/mobile/child/practice/{$sessionId}/answer", [
        'question_id' => $q['id'], 'choice_id' => 'B',
    ])
        ->assertOk()
        ->assertJsonPath('correct', true)
        ->assertJsonPath('done', true)
        ->assertJsonPath('mastered', true);

    expect(StudentProgress::where('student_id', $child->id)->where('module_id', $module->id)->value('status'))
        ->toBe('mastered');
});
