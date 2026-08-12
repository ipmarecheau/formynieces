<?php

use App\Models\Lesson;
use App\Models\SyllabusModule;
use App\Support\LessonTemplate;

/**
 * LE-01 — the lesson format is a reusable template for any ELA/Math topic.
 */
it('produces a valid lesson scaffold using only known block types', function () {
    $scaffold = LessonTemplate::scaffold('Adding Fractions', 'adding fractions with the same denominator');

    expect($scaffold['title'])->toBe('Adding Fractions')
        ->and($scaffold['blocks'])->not->toBeEmpty();

    foreach ($scaffold['blocks'] as $block) {
        expect(LessonTemplate::BLOCK_TYPES)->toContain($block['type']);

        if ($block['type'] === 'check') {
            expect($block['options'])->toBeArray()->not->toBeEmpty()
                ->and($block['answer'])->toBeLessThan(count($block['options']))
                ->and($block['answer'])->toBeGreaterThanOrEqual(0);
        }
    }
})->group('scenario:LE-01');

it('can be stored as a module lesson straight from the template', function () {
    $module = SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Number: Fractions', 'sea_section' => 'A',
        'sequence_order' => 1, 'pacing_week' => 1, 'description' => 'Fractions.', 'resources' => [],
    ]);

    $scaffold = LessonTemplate::scaffold('Adding Fractions', 'adding fractions');
    $lesson = Lesson::create(['module_id' => $module->id] + $scaffold);

    expect($lesson->blocks)->toHaveCount(count($scaffold['blocks']))
        ->and($lesson->title)->toBe('Adding Fractions');
})->group('scenario:LE-01');
