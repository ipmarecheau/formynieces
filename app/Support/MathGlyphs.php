<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Renders question text safely for display, turning bare "empty box" glyphs — which show
 * as tofu (□) on many devices/fonts — into an accessible, CSS-drawn "unknown number" box.
 *
 * Input is our own question bank, but it is HTML-escaped first, then the safe span is
 * injected, so the output is always safe to echo unescaped.
 */
final class MathGlyphs
{
    /** The "unknown" placeholder glyphs authors may have used in the bank. */
    private const BOXES = ['▢', '□', '▯', '◻', '◽', '☐', '⬜', '❑', '⃞'];

    public static function render(string $text): HtmlString
    {
        $safe = e($text);
        $box = '<span class="dw-unknown" role="img" aria-label="unknown number"></span>';

        return new HtmlString(str_replace(self::BOXES, $box, $safe));
    }
}
