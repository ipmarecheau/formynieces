<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Pacing\AdventureMapBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * The Voyage — a gamified, standalone alternative to the student dashboard.
 *
 * Tier 1 (overworld) is a hub of island-worlds. Deeper tiers (island regions,
 * then the level path) build on the same AdventureMapBuilder data. The map is
 * mastery-gated and always kind: island cards show a conquered COUNT, never a
 * pace percentage.
 */
final class VoyageController extends Controller
{
    public function __construct(
        private AdventureMapBuilder $map,
    ) {}

    public function overworld(Request $request): View
    {
        $islands = $this->map->build($request->user());

        $hubs = [];
        foreach ($islands as $name => $island) {
            $levels = collect($island['levels']);
            $hubs[] = [
                'name' => $name,
                'slug' => Str::slug($name),
                'icon' => $island['icon'],
                'conquered' => $levels->where('state', 'mastered')->count(),
                'total' => $levels->count(),
            ];
        }

        return view('voyage.overworld', [
            'user' => $request->user(),
            'hubs' => $hubs,
        ]);
    }
}
