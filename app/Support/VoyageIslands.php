<?php

declare(strict_types=1);

namespace App\Support;

use App\Services\Pacing\AdventureMapBuilder;

/**
 * The 13 painted islands of the Voyage overworld, in route order along the
 * sea map's dashed trail. Each entry pins a stop onto the artwork:
 *
 *   - slug: stable identifier (tier-2 routing, marker keys)
 *   - name: map-feature label (content is mixed across islands, so the name is
 *           decorative flavour, not a strand)
 *   - icon: emoji echoing the painted landmark
 *   - x/y:  the marker centre as a PERCENTAGE of the map image
 *           (2752 x 1536), so it scales with the aspect-ratio container
 *
 * Coordinates are a first read of the painting and are meant to be nudged
 * against the live page.
 *
 * @see AdventureMapBuilder::buildVoyage()
 */
final class VoyageIslands
{
    /**
     * @var array<int, array{slug:string, name:string, icon:string, x:float, y:float}>
     */
    private const ISLANDS = [
        ['slug' => 'feather-isle', 'name' => 'Feather Isle', 'icon' => '🪶', 'x' => 15.0, 'y' => 74.4],
        ['slug' => 'lantern-rock', 'name' => 'Lantern Rock', 'icon' => '🗼', 'x' => 8.5, 'y' => 43.0],
        ['slug' => 'palm-point', 'name' => 'Palm Point', 'icon' => '🌴', 'x' => 14.0, 'y' => 22.0],
        ['slug' => 'coral-reef', 'name' => 'Coral Reef', 'icon' => '🪸', 'x' => 28.0, 'y' => 28.2],
        ['slug' => 'twin-palms', 'name' => 'Twin Palms', 'icon' => '🌴', 'x' => 37.3, 'y' => 17.9],
        ['slug' => 'flag-bay', 'name' => 'Flag Bay', 'icon' => '🚩', 'x' => 34.0, 'y' => 38.5],
        ['slug' => 'lagoon-isle', 'name' => 'Lagoon Isle', 'icon' => '🏝️', 'x' => 39.5, 'y' => 52.9],
        ['slug' => 'library-isle', 'name' => 'Library Isle', 'icon' => '📚', 'x' => 53.5, 'y' => 22.4],
        ['slug' => 'beacon-shoal', 'name' => 'Beacon Shoal', 'icon' => '🪸', 'x' => 56.0, 'y' => 50.2],
        ['slug' => 'harbour-town', 'name' => 'Harbour Town', 'icon' => '🏘️', 'x' => 54.3, 'y' => 76.2],
        ['slug' => 'sandbar', 'name' => 'Sandbar', 'icon' => '🏝️', 'x' => 69.3, 'y' => 49.7],
        ['slug' => 'sunset-palms', 'name' => 'Sunset Palms', 'icon' => '🌴', 'x' => 91.8, 'y' => 86.9],
        ['slug' => 'crystal-peak', 'name' => 'Crystal Peak', 'icon' => '💎', 'x' => 87.3, 'y' => 23.3],
    ];

    public const COUNT = 13;

    /**
     * @return array<int, array{slug:string, name:string, icon:string, x:float, y:float}>
     */
    public static function all(): array
    {
        return self::ISLANDS;
    }
}
