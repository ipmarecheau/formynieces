<?php

use App\Services\Lessons\LessonUniqueness;

/**
 * LessonUniqueness — the worked example never pre-answers a question in the same lesson.
 * Enforced at author time (lessons:verify + the importer) so a repeat can't reach a learner.
 */
function uniq(): LessonUniqueness
{
    return app(LessonUniqueness::class);
}

it('flags a lesson whose fill-in-the-blank reuses the worked-example number', function () {
    $blocks = [
        ['type' => 'example', 'content' => 'Expanding a number.', 'steps' => ['Take 526.', 'So 526 = 500 + 20 + 6.']],
        ['type' => 'fillblank', 'prompt' => 'Complete: 526 = 500 + ___ + 6', 'answer' => '20'],
    ];

    expect(uniq()->isClean($blocks))->toBeFalse();
    $collisions = uniq()->collisions($blocks);
    expect($collisions)->toHaveCount(1)
        ->and($collisions[0]['subject'])->toBe('526')
        ->and($collisions[0]['where'])->toBe('fillblank');
});

it('flags a practiceItem that reuses the example number', function () {
    $blocks = [
        ['type' => 'example', 'content' => 'Expanding a number.', 'steps' => ['Take 3.45.', 'So 3.45 = 3 + 0.4 + 0.05.']],
        ['type' => 'fillblank', 'prompt' => 'Complete: 2.7 = 2 + ___', 'answer' => '0.7', 'practiceItems' => [
            ['prompt' => 'the tenths part of 3.45', 'answer' => '0.4'],
        ]],
    ];

    expect(uniq()->isClean($blocks))->toBeFalse();
    expect(uniq()->collisions($blocks)[0]['subject'])->toBe('3.45');
});

it('passes a lesson where the example and every question use different numbers', function () {
    $blocks = [
        ['type' => 'example', 'content' => 'Expanding a number.', 'steps' => ['Take 526.', 'So 526 = 500 + 20 + 6.']],
        ['type' => 'fillblank', 'prompt' => 'Complete: 348 = 300 + ___ + 8', 'answer' => '40', 'practiceItems' => [
            ['prompt' => '712 = 700 + ___ + 2', 'answer' => '10'],
        ]],
    ];

    expect(uniq()->isClean($blocks))->toBeTrue();
});

it('ignores single-digit subjects like a fraction numerator', function () {
    // A lone "1"/"3" (numerator, step index) is too common to be a problem identity.
    $blocks = [
        ['type' => 'example', 'content' => 'Adding fractions.', 'steps' => ['Take 1/4 + 1/4.', 'That is 2/4 = 1/2.']],
        ['type' => 'fillblank', 'prompt' => '1/3 + 1/3 = ___', 'answer' => '2/3'],
    ];

    expect(uniq()->isClean($blocks))->toBeTrue();
});

it('every authored lesson bundle is free of example/question overlap', function () {
    $files = glob(database_path('data/lessons/*.json')) ?: [];
    expect($files)->not->toBeEmpty();

    $offenders = [];
    foreach ($files as $file) {
        $decoded = json_decode((string) file_get_contents($file), true);
        $lessons = (isset($decoded['module']) || isset($decoded['blocks'])) ? [$decoded] : array_values($decoded);
        foreach ($lessons as $lesson) {
            if (! uniq()->isClean(array_values((array) ($lesson['blocks'] ?? [])))) {
                $offenders[] = basename($file);
            }
        }
    }

    expect($offenders)->toBe([]);
});
