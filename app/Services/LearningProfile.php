<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

/**
 * LearningProfile (AG-08) — a compact per-student tailoring signal for the AI tutors.
 *
 * It holds only a handful of SHORT derived tags (style, misconceptions) plus her known
 * weak strands — never chat transcripts, never PII. `promptContext()` turns it into a
 * one-or-two-sentence line injected into tutor prompts (worked examples, clarify chat,
 * re-teach) so guidance stays personal across ephemeral sessions.
 */
class LearningProfile
{
    /** Cap the stored profile so it can never grow into a log. */
    private const MAX_TAGS = 8;

    /** Her stored derived tags (style, misconceptions). */
    public function tags(User $student): array
    {
        return is_array($student->learning_profile) ? $student->learning_profile : [];
    }

    /** A short, no-PII tailoring line for an AI tutor prompt. Empty when nothing is known. */
    public function promptContext(User $student): string
    {
        $parts = [];

        $weak = is_array($student->known_weak_areas) ? array_slice($student->known_weak_areas, 0, 3) : [];
        if ($weak !== []) {
            $parts[] = 'She finds these tricky: '.implode(', ', $weak).'.';
        }

        $tags = array_slice($this->tags($student), 0, 4);
        if ($tags !== []) {
            $parts[] = 'What helps her: '.implode('; ', $tags).'.';
        }

        return implode(' ', $parts);
    }

    /** Remember one short derived tag (e.g. from a re-teach), de-duplicated and size-capped. */
    public function remember(User $student, string $tag): void
    {
        $tag = trim($tag);
        $tags = $this->tags($student);

        if ($tag === '' || in_array($tag, $tags, true)) {
            return;
        }

        $tags[] = $tag;
        $student->learning_profile = array_slice($tags, -self::MAX_TAGS);
        $student->save();
    }
}
