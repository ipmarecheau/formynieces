<?php

declare(strict_types=1);

namespace App\Support;

/**
 * The strand-world taxonomy shared by the diagnostic (island banner) and the
 * adventure map (island worlds): every strand buckets into one of four named
 * islands, each with its own icon. One source of truth so the diagnostic and
 * the map feel like the same world.
 */
final class IslandTaxonomy
{
    private const STORY_COVE = ['Comprehension', 'Poetry', 'Media'];

    private const WORD_HARBOUR = ['Spelling', 'Punctuation', 'Capitalisation', 'Grammar'];

    /**
     * @return array{0:string,1:string} [island name, icon]
     */
    public static function resolve(string $strand, string $subject): array
    {
        if ($subject === 'Writing' || $strand === 'Writing') {
            return ["Writer's Bay", '🪶'];
        }
        if (in_array($strand, self::STORY_COVE, true)) {
            return ['Story Cove', '📖'];
        }
        if (in_array($strand, self::WORD_HARBOUR, true)) {
            return ['Word Harbour', '✏️'];
        }

        return ['Number Isle', '🔢'];
    }
}
