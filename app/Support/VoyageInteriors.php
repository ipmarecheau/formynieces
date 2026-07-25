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
        // 12 waypoints along Feather Isle's stone path (bottom-left start,
        // sweeping right, up the right edge, to the top-right stone). More
        // points than levels for layout flexibility — levels are sampled evenly
        // across them so the stops span the whole trail.
        'feather-isle' => [
            ['x' => 19.5, 'y' => 66.8],
            ['x' => 25.5, 'y' => 63.0],
            ['x' => 31.5, 'y' => 61.3],
            ['x' => 40.0, 'y' => 60.8],
            ['x' => 47.5, 'y' => 64.5],
            ['x' => 55.0, 'y' => 69.5],
            ['x' => 62.5, 'y' => 71.2],
            ['x' => 73.0, 'y' => 67.5],
            ['x' => 76.0, 'y' => 59.0],
            ['x' => 73.8, 'y' => 52.0],
            ['x' => 78.2, 'y' => 43.5],
            ['x' => 77.4, 'y' => 33.5],
        ],
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
        if (count($tuned) >= $count && $count > 0) {
            return self::sampleEvenly($tuned, $count);
        }

        return self::defaultPath($count);
    }

    /**
     * Pick $count waypoints spread evenly across $waypoints, always keeping the
     * first and last so the stops span the whole path (7 levels across 12
     * waypoints -> the trail still runs end to end).
     *
     * @param  array<int, array{x:float, y:float}>  $waypoints
     * @return array<int, array{x:float, y:float}>
     */
    private static function sampleEvenly(array $waypoints, int $count): array
    {
        $waypoints = array_values($waypoints);
        $last = count($waypoints) - 1;

        if ($count === 1) {
            return [$waypoints[intdiv($last, 2)]];
        }

        $picked = [];
        for ($i = 0; $i < $count; $i++) {
            $picked[] = $waypoints[(int) round($i * $last / ($count - 1))];
        }

        return $picked;
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
