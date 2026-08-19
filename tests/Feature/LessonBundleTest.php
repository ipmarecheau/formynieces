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

it('has authored a published lesson for every syllabus module', function () {
    $moduleCount = SyllabusModule::count();
    $withLessons = Lesson::where('is_published', true)->distinct()->count('module_id');

    expect($moduleCount)->toBe(90)
        ->and($withLessons)->toBe(90);
});

it('every authored lesson meets the authoring checklist', function () {
    $interactiveTypes = ['check', 'fillblank', 'markwords', 'matchpairs', 'ordersteps'];

    foreach (Lesson::all() as $lesson) {
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
