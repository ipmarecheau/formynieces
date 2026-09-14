<?php

namespace App\Services\PastPapers;

/** Keeps source-paper diagrams self-contained before they reach an admin or child view. */
class SvgSanitizer
{
    public static function clean(mixed $svg): ?string
    {
        if (! is_string($svg)) {
            return null;
        }

        $svg = trim($svg);
        if ($svg === '' || ! preg_match('/^<svg\b/i', $svg) || strlen($svg) > 50000) {
            return null;
        }

        // Diagrams are deliberately limited to inline geometry and text. No scripts, embedded
        // pages, images, CSS imports, or event handlers can travel with an extraction draft.
        if (preg_match('/<(?:script|style|iframe|object|embed|foreignObject|image)\b|<!|\b(?:href|xlink:href|on[a-z]+)\s*=/i', $svg)) {
            return null;
        }

        return $svg;
    }
}
