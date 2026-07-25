<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Pacing\AdventureMapBuilder;
use Illuminate\Http\Request;
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
        return view('voyage.overworld', [
            'user' => $request->user(),
            'islands' => $this->map->buildVoyage($request->user()),
        ]);
    }
}
