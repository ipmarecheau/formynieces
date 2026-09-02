<?php

use App\Livewire\LessonWalk;
use App\Livewire\ModuleEntry;
use App\Livewire\TutorialWalk;
use App\Livewire\Upgrade;
use App\Models\Lesson;
use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use App\Models\User;
use Livewire\Livewire;

/**
 * free_tier.feature — on the free plan a child gets the map and the mastery quiz, but
 * every teaching surface (explainer, lesson, tutorials, re-teach) is behind the upgrade
 * wall. These specs enable the gating flag; with it off (the free-launch default) nothing
 * is gated and the rest of the suite is unaffected.
 */
beforeEach(function () {
    config()->set('features.free_tier', true);
});

/** A free-plan guardian and their child, plus a module with a lesson and a quiz. */
function freePlanChild(): array
{
    $guardian = User::factory()->free()->create(['role' => 'guardian']);
    $child = User::factory()->free()->create([
        'role' => 'student',
        'parent_id' => $guardian->id,
        'onboarding_completed_at' => now(),
    ]);

    $module = SyllabusModule::create([
        'subject' => 'Math',
        'topic' => 'Number: Place Value',
        'sea_section' => 'A',
        'sequence_order' => 1,
        'pacing_week' => 1,
        'description' => 'Understand place value.',
        'resources' => [],
    ]);

    Lesson::create([
        'module_id' => $module->id,
        'title' => 'Place Value',
        'blocks' => [['type' => 'text', 'body' => 'Teaching.']],
        'is_published' => true,
    ]);

    // Six competency-check questions across D1/D3/D5 so the quiz can be served.
    foreach ([1, 1, 3, 3, 5, 5] as $i => $d) {
        PracticeQuestion::factory()->create([
            'module_id' => $module->id,
            'subject' => 'Math',
            'prompt' => "Q{$i}?",
            'options' => ['a', 'b', 'c', 'd'],
            'correct_index' => 0,
            'difficulty' => $d,
        ]);
    }

    return [$child, $module];
}

it('a free child reaches the mastery quiz, skipping the teaching explainer (FP-02/FP-06)', function () {
    [$child, $module] = freePlanChild();

    Livewire::actingAs($child)
        ->test(ModuleEntry::class, ['module' => $module])
        ->assertSet('freePlan', true)
        ->assertSet('phase', 'check')            // straight to the quiz, no explainer
        ->assertSet('checkQuestions', fn ($q) => count($q) > 0);
})->group('scenario:FP-02')->group('scenario:FP-06');

it('locks the lesson behind the upgrade wall for a free child (FP-04)', function () {
    [$child, $module] = freePlanChild();

    Livewire::actingAs($child)
        ->test(LessonWalk::class, ['module' => $module])
        ->assertRedirect(route('upgrade', ['unlock' => 'lesson']));
})->group('scenario:FP-04');

it('locks the worked-example tutorials behind the upgrade wall for a free child (FP-05)', function () {
    [$child, $module] = freePlanChild();

    Livewire::actingAs($child)
        ->test(TutorialWalk::class, ['module' => $module])
        ->assertRedirect(route('upgrade', ['unlock' => 'tutorial']));
})->group('scenario:FP-05');

it('the upgrade wall names the surface it was reached from (FP-17)', function () {
    $guardian = User::factory()->free()->create(['role' => 'guardian']);
    $child = User::factory()->free()->create(['role' => 'student', 'parent_id' => $guardian->id, 'onboarding_completed_at' => now()]);

    Livewire::actingAs($child)
        ->test(Upgrade::class, ['unlock' => 'lesson'])
        ->assertSee('teach this island');
})->group('scenario:FP-17');

it('a PAID child still gets the lesson and the explainer — gating does not touch them', function () {
    $guardian = User::factory()->premium()->create(['role' => 'guardian']);
    $child = User::factory()->premium()->create(['role' => 'student', 'parent_id' => $guardian->id, 'onboarding_completed_at' => now()]);

    $module = SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Number: Place Value', 'sea_section' => 'A',
        'sequence_order' => 1, 'pacing_week' => 1, 'description' => 'x', 'resources' => [],
    ]);

    Livewire::actingAs($child)
        ->test(ModuleEntry::class, ['module' => $module])
        ->assertSet('freePlan', false)
        ->assertSet('phase', 'explainer');

    Livewire::actingAs($child)
        ->test(LessonWalk::class, ['module' => $module])
        ->assertNoRedirect();
})->group('scenario:FP-04');

it('with the flag OFF, a free-plan child is not gated (free-launch default)', function () {
    config()->set('features.free_tier', false);
    [$child, $module] = freePlanChild();

    Livewire::actingAs($child)
        ->test(LessonWalk::class, ['module' => $module])
        ->assertNoRedirect();
})->group('scenario:FP-04');
