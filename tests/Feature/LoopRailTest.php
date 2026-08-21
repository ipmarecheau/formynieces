<?php

use Illuminate\Support\Facades\Blade;

/**
 * LL-08 — the learning-loop route map: three routes (main Check→Lesson→Practice→
 * Mastered, the direct Check→Mastered, and the Practice↺Lesson re-learn loopback),
 * with the nodes and edges she has travelled illuminated.
 */
function railHtml(string $stage, bool $reteach = false, bool $viaCheck = false): string
{
    $html = Blade::render(
        '<x-loop-rail :stage="$stage" :reteach="$reteach" :via-check="$viaCheck" />',
        compact('stage', 'reteach', 'viaCheck'),
    );

    // Drop the <style> block so class-name assertions don't count CSS selectors.
    return (string) preg_replace('/<style>.*?<\/style>/s', '', $html);
}

it('renders all four named stages for every module', function () {
    $html = railHtml('check');

    expect($html)->toContain('Check')->toContain('Lesson')->toContain('Practice')->toContain('Mastered')
        ->and($html)->toContain('data-stage="check"');
})->group('scenario:LL-08');

it('lights the main route and ticks earlier stages as she advances', function () {
    // Practice: Check + Lesson done, Practice current; the first two main edges lit.
    $html = railHtml('practice');

    expect(substr_count($html, 'is-done'))->toBe(2)      // check, lesson
        ->and(substr_count($html, 'is-now'))->toBe(1)    // practice
        ->and(substr_count($html, 'lit main'))->toBe(2); // 1→2 and 2→3 travelled
})->group('scenario:LL-08');

it('lights nothing travelled at the Check and the whole main route at Mastered', function () {
    $check = railHtml('check');
    expect(substr_count($check, 'is-done'))->toBe(0)
        ->and($check)->not->toContain('lit main');

    $mastered = railHtml('mastered');   // full path: all three main edges lit
    expect(substr_count($mastered, 'is-done'))->toBe(3)
        ->and(substr_count($mastered, 'is-now'))->toBe(1)
        ->and(substr_count($mastered, 'lit main'))->toBe(3)
        ->and($mastered)->toContain('🏁');
})->group('scenario:LL-08');

it('lights the direct Check → Mastered route when she aces the check', function () {
    $html = railHtml('mastered', viaCheck: true);

    // Only Check and Mastered are on the travelled route; the direct edge lights,
    // the main edges do not.
    expect($html)->toContain('lit direct')
        ->and($html)->not->toContain('lit main')
        ->and(substr_count($html, 'is-done'))->toBe(1)   // check
        ->and(substr_count($html, 'is-now'))->toBe(1);   // mastered
})->group('scenario:LL-08');

it('lights the re-learn loopback while she is re-learning after a miss', function () {
    $reteach = railHtml('practice', reteach: true);
    $normal = railHtml('practice');

    expect($reteach)->toContain('lit loop')
        ->and($reteach)->toContain('data-reteach')
        ->and($normal)->not->toContain('lit loop');
})->group('scenario:LL-08');
