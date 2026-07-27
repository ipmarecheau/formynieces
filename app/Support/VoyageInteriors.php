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
        // Lantern Rock's upper rim: dock, up the left stairs, across the top
        // stones, down the right flat stones, then the staircase to the
        // lighthouse door. Stays high — no dip into the tide-pool basin.
        'lantern-rock' => [
            ['x' => 14.0, 'y' => 80.0],
            ['x' => 28.0, 'y' => 74.5],
            ['x' => 30.0, 'y' => 62.0],
            ['x' => 25.0, 'y' => 55.0],
            ['x' => 32.0, 'y' => 46.0],
            ['x' => 38.5, 'y' => 41.0],
            ['x' => 46.0, 'y' => 37.5],
            ['x' => 53.5, 'y' => 35.0],
            ['x' => 60.5, 'y' => 35.5],
            ['x' => 66.5, 'y' => 39.5],
            ['x' => 73.0, 'y' => 40.5],
            ['x' => 79.0, 'y' => 31.0],
        ],
        // Palm Point's single boardwalk: start disc (lower arm), around the
        // treasure-chest U-turn, across the top past the bonfire, up the S to
        // the sunset stone. 12 arc-length-equidistant points (5x5-grid trace).
        'palm-point' => [
            ['x' => 68.0, 'y' => 65.6],
            ['x' => 57.3, 'y' => 63.2],
            ['x' => 46.8, 'y' => 68.0],
            ['x' => 36.0, 'y' => 67.1],
            ['x' => 29.6, 'y' => 58.2],
            ['x' => 32.8, 'y' => 47.7],
            ['x' => 43.7, 'y' => 45.0],
            ['x' => 55.0, 'y' => 47.4],
            ['x' => 66.2, 'y' => 49.5],
            ['x' => 74.2, 'y' => 42.4],
            ['x' => 70.3, 'y' => 32.1],
            ['x' => 79.0, 'y' => 25.0],
        ],
        // Coral Reef's disc serpentine: bottom-left start, across the bottom,
        // up the right turn, diagonally up the middle, across the top row, to
        // the top-right disc. 12 arc-length-equidistant points (5x5-grid trace).
        'coral-reef' => [
            ['x' => 14.4, 'y' => 82.0],
            ['x' => 23.1, 'y' => 71.3],
            ['x' => 37.1, 'y' => 69.0],
            ['x' => 51.5, 'y' => 79.8],
            ['x' => 64.5, 'y' => 80.3],
            ['x' => 68.0, 'y' => 61.3],
            ['x' => 56.7, 'y' => 53.6],
            ['x' => 43.7, 'y' => 47.9],
            ['x' => 41.9, 'y' => 35.3],
            ['x' => 52.3, 'y' => 26.9],
            ['x' => 66.1, 'y' => 28.7],
            ['x' => 79.0, 'y' => 34.5],
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
