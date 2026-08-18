<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StudentStreak;
use App\Models\WeeklyTarget;
use App\Services\Motivation\StreakEconomyService;
use App\Services\Pacing\AdventureMapBuilder;
use App\Support\VoyageCompanion;
use App\Support\VoyageInteriors;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The Voyage — the student's home. A gamified sea of island-worlds; her one front
 * door into learning.
 *
 * Tier 1 (overworld) is a hub of island-worlds. Deeper tiers (island regions, then
 * the level path) build on the same AdventureMapBuilder data. The map is
 * mastery-gated and always kind: island cards show a conquered COUNT, never a pace
 * percentage. This week's target islands shimmer (SH-02) and her streak rides along
 * the top (SH-04) — everything a child touches is reached from here.
 */
final class VoyageController extends Controller
{
    public function __construct(
        private AdventureMapBuilder $map,
    ) {}

    public function overworld(Request $request): View
    {
        $user = $request->user();
        $islands = $this->map->buildVoyage($user);

        // SH-02: modules named in this week's target — the islands holding them
        // shimmer as "this week", child-kind, no pace or percentage language.
        $thisWeekModuleIds = $this->thisWeekModuleIds($user->id);

        foreach ($islands as &$island) {
            $island['this_week'] = collect($island['levels'])
                ->pluck('id')
                ->intersect($thisWeekModuleIds)
                ->isNotEmpty();
        }
        unset($island);

        // VC-01..03: this week's topics, pulled from the already-loaded island
        // levels (no extra query), for the companion's plan line.
        $thisWeekTopics = collect($islands)
            ->flatMap(fn ($island) => $island['levels'])
            ->whereIn('id', $thisWeekModuleIds)
            ->pluck('topic')
            ->values()
            ->all();

        $streaks = $this->streaksFor($user->id);
        $companion = VoyageCompanion::for($user->name, $streaks, $thisWeekTopics);
        $companion['avatarUrl'] = $this->companionAvatarUrl($companion['avatar']);

        // CE-04: a streak-milestone celebration plays once when she next opens her
        // Voyage — named warmly, never as a metric.
        $streakMilestone = app(StreakEconomyService::class)->claimStreakMilestone($user->id);

        return view('voyage.overworld', [
            'user' => $user,
            'islands' => $islands,
            'streaks' => $streaks,
            'companion' => $companion,
            'streakMilestone' => $streakMilestone,
        ]);
    }

    /**
     * Tier 2 — an island's mini-voyage: a walkable interior path of its levels.
     * Locked islands are never enterable; the student is sailed back to the
     * overworld rather than shown a dead end.
     */
    public function island(Request $request, string $island): View|RedirectResponse
    {
        $user = $request->user();
        $islands = $this->map->buildVoyage($user);

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
            'user' => $user,
            'island' => $current,
            'stops' => $stops,
            'currentStop' => $currentStop,
            'background' => VoyageInteriors::backgroundFor($current['slug']),
            'writingStop' => VoyageInteriors::writingStopFor($current['slug'], count($current['levels'])),
            // SH-02: the levels named in this week's target, highlighted on the island.
            'thisWeekModuleIds' => $this->thisWeekModuleIds($user->id),
        ]);
    }

    /**
     * Resolve a companion pose to Smooth's artwork, gracefully falling back to the
     * waving hero until a given pose has been drawn (the chart pose lands later).
     */
    private function companionAvatarUrl(string $pose): string
    {
        $files = [
            'wave' => 'smooth.webp',
            'cheer' => 'smooth-cheer.webp',
            'chart' => 'smooth-chart.webp',
        ];

        $file = $files[$pose] ?? $files['wave'];
        if (! is_file(public_path("images/voyage/companion/{$file}"))) {
            $file = $files['wave'];
        }

        return asset("images/voyage/companion/{$file}");
    }

    /**
     * Module ids named in the student's current-week target.
     *
     * @return array<int, int>
     */
    private function thisWeekModuleIds(int $studentId): array
    {
        return WeeklyTarget::where('student_id', $studentId)
            ->where('week_start_date', now()->startOfWeek()->toDateString())
            ->pluck('module_id')
            ->all();
    }

    /**
     * The student's active streaks, for the child-facing celebration on the Voyage
     * (SH-04). Streaks live here, never in the guardian's honest layer.
     *
     * @return array{practice:int, login:int, mastery:int}
     */
    private function streaksFor(int $studentId): array
    {
        $counts = StudentStreak::where('student_id', $studentId)
            ->pluck('count', 'type');

        return [
            'practice' => (int) ($counts['practice'] ?? 0),
            'login' => (int) ($counts['login'] ?? 0),
            'mastery' => (int) ($counts['mastery'] ?? 0),
        ];
    }
}
