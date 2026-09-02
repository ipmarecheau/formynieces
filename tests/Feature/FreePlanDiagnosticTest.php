<?php

use App\Services\Diagnostic\SessionLifecycle;
use Database\Seeders\ElaAnchorQuestionSeeder;
use Database\Seeders\MathAnchorQuestionSeeder;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Database\Seeders\WritingAnchorQuestionSeeder;
use Illuminate\Support\Facades\DB;

/**
 * free_tier.feature FP-14 — a free-plan child gets a SHORT diagnostic so the map is
 * seeded and alive, without the full paid assessment.
 */
beforeEach(function () {
    config()->set('features.free_tier', true);
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);
    $this->seed(MathAnchorQuestionSeeder::class);
    $this->seed(ElaAnchorQuestionSeeder::class);
    $this->seed(WritingAnchorQuestionSeeder::class);
    $this->lifecycle = app(SessionLifecycle::class);
});

function makePlanStudent(string $plan): int
{
    return DB::table('users')->insertGetId([
        'name' => 'Plan Student',
        'email' => 'plan-'.uniqid().'@example.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'plan' => $plan,
        'onboarding_completed_at' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function planCount(int $sessionId): int
{
    $s = DB::table('diagnostic_sessions')->find($sessionId);

    return count(json_decode($s->item_plan ?? '[]', true) ?: []);
}

it('gives a free child a short seed diagnostic (FP-14)', function () {
    $sessionId = $this->lifecycle->startOrResume(makePlanStudent('free'));

    $count = planCount($sessionId);
    expect($count)->toBeGreaterThan(0)               // the map is seeded, never empty
        ->and($count)->toBeLessThanOrEqual(SessionLifecycle::FREE_PLAN_ITEMS);
})->group('scenario:FP-14');

it('gives a paying child the full-length diagnostic', function () {
    $freeCount = planCount($this->lifecycle->startOrResume(makePlanStudent('free')));
    $paidCount = planCount($this->lifecycle->startOrResume(makePlanStudent('premium')));

    expect($paidCount)->toBeGreaterThan($freeCount);
})->group('scenario:FP-14');
