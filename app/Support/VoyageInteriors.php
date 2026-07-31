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
        // Twin Palms' boardwalk: ramp start (bottom-left), across the bottom,
        // around the horseshoe wrapping the central oval, up the bridge to the
        // sunset dock. 12 arc-length-equidistant points (5x5-grid trace).
        'twin-palms' => [
            ['x' => 18.0, 'y' => 72.0],
            ['x' => 27.0, 'y' => 69.4],
            ['x' => 36.5, 'y' => 68.7],
            ['x' => 42.9, 'y' => 61.9],
            ['x' => 50.1, 'y' => 56.0],
            ['x' => 58.4, 'y' => 60.6],
            ['x' => 67.1, 'y' => 63.9],
            ['x' => 74.2, 'y' => 58.7],
            ['x' => 72.7, 'y' => 49.4],
            ['x' => 78.2, 'y' => 42.2],
            ['x' => 84.2, 'y' => 35.0],
            ['x' => 90.0, 'y' => 27.5],
        ],
        // Flag Bay's pier S: start disc (bottom-left), across the bottom, around
        // the corner disc, up the middle curve, across the top pier to the
        // top-right disc. 12 disc-snapped points (5x5-grid trace).
        'flag-bay' => [
            ['x' => 10.2, 'y' => 88.2],
            ['x' => 18.9, 'y' => 80.7],
            ['x' => 28.5, 'y' => 74.6],
            ['x' => 39.4, 'y' => 79.6],
            ['x' => 50.8, 'y' => 75.2],
            ['x' => 59.0, 'y' => 66.0],
            ['x' => 57.1, 'y' => 57.4],
            ['x' => 42.9, 'y' => 43.1],
            ['x' => 51.8, 'y' => 30.2],
            ['x' => 69.4, 'y' => 31.7],
            ['x' => 78.1, 'y' => 30.6],
            ['x' => 87.8, 'y' => 22.4],
        ],
        // Lagoon Isle's winding trail: left-bank discs, up to the rope bridge
        // (entrance -> dip -> far side), the plank bridge, around the bamboo dip
        // on the right bank, then up the stone discs to the top-right cave.
        // 12 waypoints (5x5-grid trace, bridge decks tuned by hand).
        'lagoon-isle' => [
            ['x' => 15.0, 'y' => 84.0],
            ['x' => 24.0, 'y' => 73.1],
            ['x' => 20.0, 'y' => 57.0],
            ['x' => 33.0, 'y' => 45.0],
            ['x' => 42.0, 'y' => 50.0],
            ['x' => 60.5, 'y' => 66.5],
            ['x' => 72.0, 'y' => 83.0],
            ['x' => 84.0, 'y' => 72.5],
            ['x' => 85.4, 'y' => 56.1],
            ['x' => 76.9, 'y' => 40.5],
            ['x' => 75.4, 'y' => 23.6],
            ['x' => 90.0, 'y' => 14.5],
        ],
        // Library Isle's book-and-stone trail: beach start disc (lower-left), up
        // past the coral tome, over the mossy mound, down to the sleeping-cat
        // boardwalk, along the bottom stepping stones, then up the right-hand
        // stones and books to the top-right disc. 12 waypoints snapped to the
        // flat book-tops and round stones (5x5-grid trace; leaning shelf books
        // are scenery, kept off the path).
        'library-isle' => [
            ['x' => 16.0, 'y' => 84.0],
            ['x' => 24.0, 'y' => 70.0],
            ['x' => 21.0, 'y' => 53.0],
            ['x' => 31.0, 'y' => 44.0],
            ['x' => 45.0, 'y' => 51.0],
            ['x' => 58.0, 'y' => 67.0],
            ['x' => 67.0, 'y' => 80.0],
            ['x' => 79.0, 'y' => 78.0],
            ['x' => 85.0, 'y' => 64.0],
            ['x' => 79.0, 'y' => 45.0],
            ['x' => 75.0, 'y' => 28.0],
            ['x' => 84.0, 'y' => 14.0],
        ],
        // Beacon Shoal's plank-and-disc trail: beach start disc (lower-left),
        // up the first plank bridge, around the left arch bridge (hugging the
        // planks past the tide-pool), across the central bridge below the
        // lighthouse, then up the right-hand discs and the right arch to the
        // top-right disc. 12 waypoints snapped to disc tops and
        // plank surfaces (per-point high-zoom audit; arches ride the planks).
        'beacon-shoal' => [
            ['x' => 9.0, 'y' => 86.0],
            ['x' => 22.0, 'y' => 71.0],
            ['x' => 23.0, 'y' => 52.0],
            ['x' => 27.0, 'y' => 44.0],
            ['x' => 31.0, 'y' => 44.0],
            ['x' => 45.0, 'y' => 52.0],
            ['x' => 55.0, 'y' => 66.0],
            ['x' => 68.0, 'y' => 72.0],
            ['x' => 70.0, 'y' => 43.0],
            ['x' => 70.0, 'y' => 29.0],
            ['x' => 83.0, 'y' => 18.0],
            ['x' => 89.0, 'y' => 12.0],
        ],
        // Harbour Town's cobblestone-disc street: start disc (lower-left), a
        // diagonal up the left of the market square, across the middle row of
        // paving discs past the stalls and well, then up the right-hand discs
        // to the top-right corner by the harbour shops. 12 waypoints centred on
        // the round cobble discs (per-point high-zoom audit).
        'harbour-town' => [
            ['x' => 10.0, 'y' => 88.0],
            ['x' => 14.0, 'y' => 83.0],
            ['x' => 23.0, 'y' => 73.0],
            ['x' => 31.0, 'y' => 67.0],
            ['x' => 40.0, 'y' => 51.0],
            ['x' => 45.0, 'y' => 47.0],
            ['x' => 56.0, 'y' => 48.0],
            ['x' => 66.0, 'y' => 49.0],
            ['x' => 73.0, 'y' => 49.0],
            ['x' => 80.0, 'y' => 41.0],
            ['x' => 85.0, 'y' => 22.0],
            ['x' => 90.0, 'y' => 10.0],
        ],
        // Sandbar's stepping-stone arc: a single unbroken line of 12 sea-worn
        // stones, from the lower-left corner up across the open middle of the bar
        // and up the right edge to the top-right corner. Deliberately simple art
        // (regenerated to drop the stray stones/branches); 12 waypoints centred
        // on the stones (per-point high-zoom audit).
        'sandbar' => [
            ['x' => 13.0, 'y' => 81.0],
            ['x' => 22.0, 'y' => 63.0],
            ['x' => 28.0, 'y' => 51.0],
            ['x' => 37.0, 'y' => 45.0],
            ['x' => 46.0, 'y' => 44.0],
            ['x' => 52.0, 'y' => 48.0],
            ['x' => 60.0, 'y' => 52.0],
            ['x' => 68.0, 'y' => 52.0],
            ['x' => 75.0, 'y' => 43.0],
            ['x' => 80.0, 'y' => 35.0],
            ['x' => 84.0, 'y' => 27.0],
            ['x' => 87.0, 'y' => 20.0],
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
     * The number of stop slots reserved on every island — more than the levels
     * in play, so future content (a writing challenge, a boss level, a revision
     * stop) drops into an already-placed, evenly-spread slot without shuffling
     * the levels a student already sees.
     */
    public const RESERVED_SLOTS = 12;

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
            return array_map(
                fn (int $i): array => $tuned[$i],
                self::evenIndices(count($tuned), $count),
            );
        }

        return self::defaultPath($count);
    }

    /**
     * The reserved waypoint an island's Writing stop occupies: the most central
     * slot the $levelCount level stops leave free, so the Writer's Log sits in a
     * gap on the trail rather than on top of a level. Every island has one — the
     * writing strand straddles the whole voyage — even before the writing track
     * is built (the stop renders as a calm "coming soon" marker until then).
     *
     * @return array{x:float, y:float}
     */
    public static function writingStopFor(string $slug, int $levelCount): array
    {
        $waypoints = array_values(self::STOPS[$slug] ?? self::defaultPath(self::RESERVED_SLOTS));
        $count = count($waypoints);

        $used = self::evenIndices($count, $levelCount);
        $reserved = array_values(array_diff(range(0, $count - 1), $used));

        if ($reserved === []) {
            return $waypoints[intdiv($count - 1, 2)];
        }

        // The reserved slot nearest the path centre — evenly placed, never
        // clustered against the start or the end.
        $centre = ($count - 1) / 2;
        usort($reserved, fn (int $a, int $b): int => abs($a - $centre) <=> abs($b - $centre));

        return $waypoints[$reserved[0]];
    }

    /**
     * The waypoint indices an evenly-spread selection of $count stops occupies
     * across $waypointCount points — always keeping the first and last so the
     * trail spans end to end (7 levels across 12 waypoints -> the trail still
     * runs end to end). Shared by the level sampler and the reserved-slot
     * placement so "used" and "reserved" are always computed the same way.
     *
     * @return array<int, int>
     */
    private static function evenIndices(int $waypointCount, int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $last = $waypointCount - 1;

        if ($count === 1) {
            return [intdiv($last, 2)];
        }

        $indices = [];
        for ($i = 0; $i < $count; $i++) {
            $indices[] = (int) round($i * $last / ($count - 1));
        }

        return $indices;
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
