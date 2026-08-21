<?php

use Illuminate\Support\Facades\Blade;

/**
 * LL-08 — the learning-loop rail: a fixed board track (Check → Lesson → Practice →
 * Mastered) shown down the side of every module screen, with the current stage
 * highlighted, earlier stages ticked, and a re-learn loop-back on a practice miss.
 */
function railHtml(string $stage, bool $reteach = false): string
{
    $html = Blade::render('<x-loop-rail :stage="$stage" :reteach="$reteach" />', compact('stage', 'reteach'));

    // Drop the <style> block so class-name assertions don't count CSS selectors.
    return (string) preg_replace('/<style>.*?<\/style>/s', '', $html);
}

it('renders all four named stages for every module, same layout', function () {
    $html = railHtml('check');

    expect($html)->toContain('Check')
        ->and($html)->toContain('Lesson')
        ->and($html)->toContain('Practice')
        ->and($html)->toContain('Mastered')
        ->and($html)->toContain('data-stage="check"');
})->group('scenario:LL-08');

it('highlights the current stage and ticks the earlier ones', function () {
    // On Practice: Check + Lesson are done, Practice is current, Mastered is ahead.
    $html = railHtml('practice');

    expect(substr_count($html, 'is-done'))->toBe(2)   // check, lesson
        ->and(substr_count($html, 'is-now'))->toBe(1) // practice
        ->and($html)->toContain('data-stage="practice"');
})->group('scenario:LL-08');

it('marks nothing done at the Check and everything done at Mastered', function () {
    expect(substr_count(railHtml('check'), 'is-done'))->toBe(0);
    expect(substr_count(railHtml('check'), 'is-now'))->toBe(1);

    $mastered = railHtml('mastered');
    expect(substr_count($mastered, 'is-done'))->toBe(3)  // check, lesson, practice
        ->and(substr_count($mastered, 'is-now'))->toBe(1) // mastered node
        ->and($mastered)->toContain('🏁');
})->group('scenario:LL-08');

it('shows the loop back to Practice when re-learning after a miss', function () {
    $reteach = railHtml('practice', reteach: true);
    $normal = railHtml('practice');

    // Re-learning: the loop-back note is lit and Practice is not the "now" square.
    expect($reteach)->toContain('lr-chute is-on')
        ->and(substr_count($reteach, 'is-now'))->toBe(0)
        ->and($normal)->not->toContain('lr-chute is-on');
})->group('scenario:LL-08');
