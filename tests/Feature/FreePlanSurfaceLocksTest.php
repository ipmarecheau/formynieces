<?php

use App\Livewire\GuardianDashboard;
use App\Livewire\MorningTide;
use App\Livewire\WritingStop;
use App\Models\StudentJourney;
use App\Models\User;
use Livewire\Livewire;

/**
 * free_tier.feature — the remaining free-plan walls: the daily writing track (FP-09),
 * the Morning Tide rituals (FP-10), and the guardian's pace/Estimator sections (FP-11).
 */
beforeEach(function () {
    config()->set('features.free_tier', true);
});

function freeChildAndGuardian(): array
{
    $guardian = User::factory()->free()->create(['role' => 'guardian']);
    $child = User::factory()->free()->create([
        'role' => 'student',
        'parent_id' => $guardian->id,
        'onboarding_completed_at' => now(),
    ]);

    return [$guardian, $child];
}

it('locks the daily writing track for a free child (FP-09)', function () {
    [, $child] = freeChildAndGuardian();

    Livewire::actingAs($child)
        ->test(WritingStop::class)
        ->assertRedirect(route('upgrade', ['unlock' => 'writing']));
})->group('scenario:FP-09');

it('locks the Morning Tide rituals for a free child (FP-10)', function () {
    [, $child] = freeChildAndGuardian();

    Livewire::actingAs($child)
        ->test(MorningTide::class)
        ->assertRedirect(route('upgrade', ['unlock' => 'rituals']));
})->group('scenario:FP-10');

it('locks the guardian pace section behind the wall for a free plan (FP-11)', function () {
    [$guardian] = freeChildAndGuardian();

    Livewire::actingAs($guardian)
        ->withQueryParams(['section' => 'pace'])
        ->test(GuardianDashboard::class)
        ->assertRedirect(route('upgrade', ['unlock' => 'pace']));
})->group('scenario:FP-11');

it('locks the guardian Estimator section behind the wall for a free plan (FP-11)', function () {
    [$guardian] = freeChildAndGuardian();

    Livewire::actingAs($guardian)
        ->withQueryParams(['section' => 'estimator'])
        ->test(GuardianDashboard::class)
        ->assertRedirect(route('upgrade', ['unlock' => 'estimator']));
})->group('scenario:FP-11');

it('lets a free guardian see the overview (bare mastery), never gated off their own dashboard (FP-15)', function () {
    [$guardian] = freeChildAndGuardian();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSet('freePlan', true)
        ->assertNoRedirect();
})->group('scenario:FP-15');

it('never calls the AI briefing for a free guardian (FP-12)', function () {
    [$guardian] = freeChildAndGuardian();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->call('loadAiSummary')
        ->assertSet('aiSummary', null);
})->group('scenario:FP-12');

it('with the flag off, the guardian pace section is not gated (free-launch default)', function () {
    config()->set('features.free_tier', false);
    [$guardian] = freeChildAndGuardian();

    Livewire::actingAs($guardian)
        ->withQueryParams(['section' => 'pace'])
        ->test(GuardianDashboard::class)
        ->assertNoRedirect();
})->group('scenario:FP-11');

it('a guardian can actually load the upgrade wall — it is not student-only (regression: 403)', function () {
    [$guardian] = freeChildAndGuardian();

    $this->actingAs($guardian)
        ->get(route('upgrade', ['unlock' => 'estimator']))
        ->assertOk()
        ->assertSee('placement projection', false);
})->group('scenario:FP-11');

it('a free student can also load the upgrade wall', function () {
    [, $child] = freeChildAndGuardian();

    $this->actingAs($child)
        ->get(route('upgrade', ['unlock' => 'lesson']))
        ->assertOk();
})->group('scenario:FP-17');

it('a free guardian overview shows only the bare mastery, not the honest-layer panels (FP-15)', function () {
    [$guardian, $child] = freeChildAndGuardian();
    StudentJourney::create([
        'student_id' => $child->id,
        'journey_start' => now()->toDateString(),
        'exam_date' => now()->addYear()->toDateString(),
    ]);

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSet('freePlan', true)
        ->assertSee('Overall mastery')
        ->assertSee('On the free plan')
        ->assertSee('Unlock the full report')
        ->assertDontSee('Strengths & what to work on', false)  // the AI exam-agent card
        ->assertDontSee('Where '.$child->name.' stands', false); // the readiness verdict
})->group('scenario:FP-15');
