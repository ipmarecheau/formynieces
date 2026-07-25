<?php

declare(strict_types=1);

namespace App\Support;

use App\Http\Controllers\VoyageController;

/**
 * Tier-2 interior art + level-stop layout for each Voyage island.
 *
 * Bespoke artwork lives at public/images/voyage/interiors/{slug}.png (same
 * 2752 x 1536 canvas as the overworld). Until an island's art exists, the
 * interior view falls back to a themed gradient — the mini-voyage still works.
 *
 * Level stops are positioned as PERCENTAGES of the canvas, so they scale with
 * the aspect-ratio stage. Bespoke coordinates can only be tuned once the art
 * exists; until then STOPS is empty for a slug and a default S-curve path of
 * the right length is generated instead.
 *
 * @see VoyageController::island()
 */
final class VoyageInteriors
{
    /**
     * Per-island, per-level stop coordinates, filled in during the coordinate
     * tuning pass once each island's bespoke art lands. Keyed by island slug;
     * each value is an ordered list of {x, y} percentages, one per level.
     *
     * @var array<string, array<int, array{x:float, y:float}>>
     */
    private const STOPS = [
        // 'feather-isle' => [['x' => 12.0, 'y' => 80.0], ...],
    ];

    /**
     * Web path to an island's bespoke interior art, or null if it has not been
     * generated yet (the view then uses a themed fallback).
     */
    public static function backgroundFor(string $slug): ?string
    {
        $relative = "images/voyage/interiors/{$slug}.png";

        return is_file(public_path($relative)) ? "/{$relative}" : null;
    }

    /**
     * Ordered stop coordinates for an island's $count levels: the tuned bespoke
     * layout if defined, otherwise a generated default S-curve of $count points
     * so the mini-voyage is walkable before its art is tuned.
     *
     * @return array<int, array{x:float, y:float}>
     */
    public static function stopsFor(string $slug, int $count): array
    {
        $tuned = self::STOPS[$slug] ?? [];
        if (count($tuned) >= $count) {
            return array_slice($tuned, 0, $count);
        }

        return self::defaultPath($count);
    }

    /**
     * A gentle S-curve of $count evenly spaced stops across the canvas, used as
     * a walkable placeholder until an island's real path is tuned to its art.
     *
     * @return array<int, array{x:float, y:float}>
     */
    private static function defaultPath(int $count): array
    {
        if ($count <= 0) {
            return [];
        }
        if ($count === 1) {
            return [['x' => 50.0, 'y' => 55.0]];
        }

        $stops = [];
        for ($i = 0; $i < $count; $i++) {
            $t = $i / ($count - 1);              // 0 -> 1 along the path
            $x = 12.0 + $t * 76.0;               // sweep left -> right, 12%..88%
            $y = 55.0 + sin($t * M_PI * 2) * 22.0; // weave up and down the middle
            $stops[] = ['x' => round($x, 1), 'y' => round($y, 1)];
        }

        return $stops;
    }
}
