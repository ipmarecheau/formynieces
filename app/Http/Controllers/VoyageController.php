<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Pacing\AdventureMapBuilder;
use App\Support\VoyageInteriors;
use Illuminate\Http\RedirectResponse;
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

    /**
     * Tier 2 — an island's mini-voyage: a walkable interior path of its levels.
     * Locked islands are never enterable; the student is sailed back to the
     * overworld rather than shown a dead end.
     */
    public function island(Request $request, string $island): View|RedirectResponse
    {
        $islands = $this->map->buildVoyage($request->user());

        $current = collect($islands)->firstWhere('slug', $island);
        abort_unless($current !== null, 404);

        if ($current['state'] === 'locked') {
            return redirect()->route('student.voyage');
        }

        $stops = VoyageInteriors::stopsFor($current['slug'], count($current['levels']));

        // The first not-yet-mastered level is where the boat sits; earlier
        // levels are done, later ones wait their turn (sequential, kind).
        $currentStop = collect($current['levels'])->search(fn ($level) => ! $level['mastered']);
        $currentStop = $currentStop === false ? count($current['levels']) - 1 : $currentStop;

        return view('voyage.island', [
            'user' => $request->user(),
            'island' => $current,
            'stops' => $stops,
            'currentStop' => $currentStop,
            'background' => VoyageInteriors::backgroundFor($current['slug']),
        ]);
    }
}
