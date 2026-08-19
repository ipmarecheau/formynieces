<?php

use App\Models\Lesson;
use App\Models\SyllabusModule;
use Database\Seeders\LessonSeeder;
use Database\Seeders\SyllabusModuleSeeder;

/**
 * The authored lesson bundles import cleanly and stay coherent with the re-teach:
 * every interactive block carries its own rule + same-rule practiceItems (the
 * single most important thing an author gets right — the AI re-teach draws on them).
 */
beforeEach(function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(LessonSeeder::class);
});

it('imports the ELA-001 plurals lesson as a coherent, published bundle', function () {
    $module = SyllabusModule::where('code', 'ELA-001')->firstOrFail();
    $lesson = Lesson::where('module_id', $module->id)->first();

    expect($lesson)->not->toBeNull()
        ->and($lesson->is_published)->toBeTrue();
});

it('every authored ELA lesson meets the authoring checklist', function () {
    $elaModuleIds = SyllabusModule::where('code', 'like', 'ELA-%')->pluck('id', 'code');
    $lessons = Lesson::whereIn('module_id', $elaModuleIds)->get();

    // We authored a lesson for every ELA module.
    expect($lessons)->toHaveCount($elaModuleIds->count());

    $interactiveTypes = ['check', 'fillblank', 'markwords', 'matchpairs', 'ordersteps'];

    foreach ($lessons as $lesson) {
        $blocks = collect($lesson->blocks);
        $interactive = $blocks->whereIn('type', $interactiveTypes);

        expect($lesson->is_published)->toBeTrue()
            ->and($blocks->count())->toBeGreaterThanOrEqual(6)->toBeLessThanOrEqual(10)
            ->and($interactive->count())->toBeGreaterThanOrEqual(2);

        // Coherence: each interactive block carries its own rule + >=4 same-rule practiceItems.
        foreach ($interactive as $block) {
            expect($block['rule'] ?? null)->not->toBeEmpty()
                ->and(count($block['practiceItems'] ?? []))->toBeGreaterThanOrEqual(4);
        }
    }
});
