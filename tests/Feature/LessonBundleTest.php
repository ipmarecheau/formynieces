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
it('imports the ELA-001 plurals lesson as a coherent, published bundle', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(LessonSeeder::class);

    $module = SyllabusModule::where('code', 'ELA-001')->firstOrFail();
    $lesson = Lesson::where('module_id', $module->id)->first();

    expect($lesson)->not->toBeNull()
        ->and($lesson->is_published)->toBeTrue();

    $blocks = collect($lesson->blocks);
    expect($blocks->count())->toBeGreaterThanOrEqual(6)->toBeLessThanOrEqual(10);

    $interactive = $blocks->whereIn('type', ['check', 'fillblank', 'markwords', 'matchpairs', 'ordersteps']);

    // At least two interactive blocks, each with a one-sentence rule and >=4 same-rule practiceItems.
    expect($interactive->count())->toBeGreaterThanOrEqual(2)
        ->and($interactive->every(fn ($b) => ! empty($b['rule']) && count($b['practiceItems'] ?? []) >= 4))->toBeTrue();
});
