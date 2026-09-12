<?php

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WritingPrompt;
use App\Models\WritingSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function parentWithChild(): array
{
    $parent = User::factory()->create(['role' => 'guardian']);
    $child = User::factory()->create([
        'role' => 'student', 'parent_id' => $parent->id, 'target_sea_year' => now()->year,
    ]);

    return [$parent, $child, $parent->createToken('t', ['parent'])->plainTextToken];
}

it('lists the parent’s children with status and streak (MP-01)', function () {
    [$parent, $child, $token] = parentWithChild();

    $this->withToken($token)->getJson('/api/mobile/children')
        ->assertOk()
        ->assertJsonPath('children.0.id', $child->id)
        ->assertJsonStructure(['children' => [['id', 'name', 'standard', 'latest_activity', 'status', 'status_label', 'streak_days']]]);
});

it('returns a child overview with a next action and cards (MP-02)', function () {
    [$parent, $child, $token] = parentWithChild();

    $this->withToken($token)->getJson("/api/mobile/children/{$child->id}/overview")
        ->assertOk()
        ->assertJsonPath('child.id', $child->id)
        ->assertJsonStructure([
            'child' => ['id', 'name', 'standard'],
            'next_action' => ['title', 'why', 'cta'],
            'weekly_summary' => ['sessions_completed', 'streak_days', 'last_practised_at'],
            'cards' => ['weak_topics', 'writing_status', 'readiness_status'],
        ]);
});

it('surfaces weak topics worst-first and actionable (MP-03)', function () {
    [$parent, $child, $token] = parentWithChild();
    $module = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Equivalent fractions']);
    StudentProgress::create(['student_id' => $child->id, 'module_id' => $module->id, 'status' => 'needs_work']);

    $this->withToken($token)->getJson("/api/mobile/children/{$child->id}/weak-topics")
        ->assertOk()
        ->assertJsonPath('topics.0.title', 'Equivalent fractions')
        ->assertJsonPath('topics.0.subject', 'Math')
        ->assertJsonPath('topics.0.severity', 'high')
        ->assertJsonStructure(['topics' => [['id', 'subject', 'title', 'reason', 'suggested_action', 'severity']]]);
});

it('shows writing feedback when scored, and none when absent (MP-06)', function () {
    [$parent, $child, $token] = parentWithChild();

    $this->withToken($token)->getJson("/api/mobile/children/{$child->id}/writing")
        ->assertOk()->assertJsonPath('status', 'none');

    $prompt = WritingPrompt::factory()->create(['prompt' => 'A surprise at the beach']);
    WritingSubmission::create([
        'student_id' => $child->id, 'writing_prompt_id' => $prompt->id, 'body' => 'x',
        'status' => 'scored', 'content_score' => 3, 'language_score' => 3, 'grammar_score' => 3,
        'organisation_score' => 2, 'did_well' => 'Clear beginning.', 'try_next' => 'Develop the ending.',
        'scored_at' => now(),
    ]);

    $this->withToken($token)->getJson("/api/mobile/children/{$child->id}/writing")
        ->assertOk()
        ->assertJsonPath('status', 'feedback_ready')
        ->assertJsonPath('latest.prompt', 'A surprise at the beach');
});

it('does not overstate readiness on thin evidence (MP-05)', function () {
    [$parent, $child, $token] = parentWithChild();

    $this->withToken($token)->getJson("/api/mobile/children/{$child->id}/readiness")
        ->assertOk()
        ->assertJsonPath('status', 'building_evidence')
        ->assertJsonStructure(['status', 'headline', 'evidence', 'next_action']);
});

it('forbids reading a child that is not yours (403)', function () {
    [$parentA, $childA, $tokenA] = parentWithChild();
    [$parentB, $childB] = parentWithChild();

    $this->withToken($tokenA)->getJson("/api/mobile/children/{$childB->id}/overview")
        ->assertForbidden();
});

it('blocks a child-scoped token from parent endpoints (MC-06/MP-07)', function () {
    $child = User::factory()->create(['role' => 'student']);
    $childToken = $child->createToken('t', ['child'])->plainTextToken;

    $this->withToken($childToken)->getJson('/api/mobile/children')->assertForbidden();
});

it('requires a token for parent endpoints', function () {
    $this->getJson('/api/mobile/children')->assertUnauthorized();
});
