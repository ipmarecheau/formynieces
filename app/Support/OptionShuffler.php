<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Randomises the position of a multiple-choice question's options so the correct
 * answer is not always the first one (banks are often authored with the correct
 * option first). The correct answer's INDEX is tracked through the shuffle, never
 * its text, so duplicate option texts are handled correctly.
 *
 * When a seed is given the order is deterministic for that seed — so re-importing
 * the same question keeps a stable (but non-first) position, while different
 * questions spread across positions.
 */
final class OptionShuffler
{
    /**
     * @param  list<string>  $options
     * @return array{options: list<string>, correct_index: int}
     */
    public static function shuffle(array $options, int $correctIndex, ?string $seed = null): array
    {
        $order = array_keys($options);

        if ($seed === null) {
            shuffle($order);
        } else {
            // Deterministic order: sort original indexes by a hash of seed+index.
            usort($order, static fn (int $a, int $b): int => self::hashIndex($seed, $a) <=> self::hashIndex($seed, $b));
        }

        $shuffled = [];
        $newIndex = $correctIndex;
        foreach ($order as $position => $originalIndex) {
            $shuffled[] = $options[$originalIndex];
            if ($originalIndex === $correctIndex) {
                $newIndex = $position;
            }
        }

        return ['options' => $shuffled, 'correct_index' => $newIndex];
    }

    /** A stable 32-bit hash for (seed, index) used to order options deterministically. */
    private static function hashIndex(string $seed, int $index): int
    {
        return (int) hexdec(substr(md5($seed.'#'.$index), 0, 8));
    }
}
