<?php

use App\Support\MathGlyphs;

it('replaces the empty-box glyph with an accessible CSS box, not tofu', function () {
    $html = (string) MathGlyphs::render('▢ × 7 = 91');

    expect($html)->toContain('class="dw-unknown"')
        ->and($html)->toContain('aria-label="unknown number"')
        ->and($html)->not->toContain('▢')
        ->and($html)->toContain('× 7 = 91');
});

it('escapes HTML in the question text (safe to echo unescaped)', function () {
    $html = (string) MathGlyphs::render('<script>x</script> ▢');

    expect($html)->toContain('&lt;script&gt;')
        ->and($html)->not->toContain('<script>');
});

it('leaves ordinary text untouched', function () {
    expect((string) MathGlyphs::render('What is 6 × 7?'))->toBe('What is 6 × 7?');
});
