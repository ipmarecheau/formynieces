<?php

namespace App\Http\Controllers;

use App\Models\QaReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

/**
 * The QA walkthrough surface for the autonomous test agent (token-gated).
 *
 *  GET  /qa/manifest      — features, user stories (Gherkin) and testing guidance,
 *                           parsed live from the spec files so it never goes stale.
 *  GET  /qa/reports       — the daily feedback dashboard (newest first).
 *  POST /qa/report        — the report inbox: one structured finding per scenario.
 *
 * Everything is gated on a shared secret (services.qa.token / QA_ACCESS_TOKEN); with
 * no token configured the routes 404, so the surface is off by default.
 */
class QaController extends Controller
{
    private const OUTCOMES = ['pass', 'fail', 'blocked', 'gap', 'note'];

    /** 404 unless the request carries the configured QA token (header or query). */
    private function guard(Request $request): void
    {
        $expected = (string) config('services.qa.token');
        $given = (string) ($request->header('X-QA-Token') ?: $request->query('token', ''));

        abort_if($expected === '' || ! hash_equals($expected, $given), 404);
    }

    /**
     * The feature/user-story manifest, parsed from formynieces-spec/features/*.feature.
     * Returns JSON by default, or an HTML reading view with ?format=html.
     */
    public function manifest(Request $request): JsonResponse|View
    {
        $this->guard($request);

        $features = $this->parseFeatures();

        $payload = [
            'app' => 'SmoothSeas',
            'generated_at' => now()->toIso8601String(),
            'intent' => 'SEA-exam prep for Trinidad & Tobago primary students. A child works a daily learning loop (lesson → practice → mastery) across a Voyage map; a guardian sees an honest progress dashboard.',
            'how_to_test' => [
                'Log in through the UI only — never touch the database.',
                'Walk each user story end to end as the named actor (student or guardian).',
                'For every scenario, POST a finding to /qa/report with the scenario id and an outcome of '.implode(' | ', self::OUTCOMES).'.',
                'Flag any gap between the story intent and what the build actually does.',
                'Complete the daily exercises fully: morning reading + vocabulary, the learning loop, and writing on Mon/Wed/Fri.',
            ],
            'actors' => ['student (child)', 'guardian (parent)'],
            'feature_count' => count($features),
            'features' => $features,
        ];

        if ($request->query('format') === 'html') {
            return view('qa.manifest', ['payload' => $payload]);
        }

        return response()->json($payload, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /** Record one QA finding. */
    public function storeReport(Request $request): JsonResponse
    {
        $this->guard($request);

        $data = $request->validate([
            'scenario' => ['nullable', 'string', 'max:32'],
            'outcome' => ['required', 'string', 'in:'.implode(',', self::OUTCOMES)],
            'summary' => ['required', 'string', 'max:500'],
            'detail' => ['nullable', 'string', 'max:8000'],
            'actor' => ['nullable', 'string', 'max:120'],
            'screenshot_url' => ['nullable', 'url', 'max:2048'],
            'meta' => ['nullable', 'array'],
        ]);

        $report = QaReport::create($data);

        return response()->json(['ok' => true, 'id' => $report->id], 201);
    }

    /** The feedback dashboard — newest first, optionally filtered by outcome/scenario. */
    public function reports(Request $request): View
    {
        $this->guard($request);

        $reports = QaReport::query()
            ->when($request->filled('outcome'), fn ($q) => $q->where('outcome', $request->query('outcome')))
            ->when($request->filled('scenario'), fn ($q) => $q->where('scenario', $request->query('scenario')))
            ->latest()
            ->limit(500)
            ->get();

        $counts = QaReport::query()
            ->selectRaw('outcome, COUNT(*) as n')
            ->groupBy('outcome')
            ->pluck('n', 'outcome');

        return view('qa.reports', [
            'reports' => $reports,
            'counts' => $counts,
            'token' => (string) $request->query('token', ''),
        ]);
    }

    /**
     * Parse every .feature file into { id, name, mvp, intent, stories:[{id,title}] }.
     *
     * @return list<array<string, mixed>>
     */
    private function parseFeatures(): array
    {
        $dir = base_path('formynieces-spec/features');
        if (! File::isDirectory($dir)) {
            return [];
        }

        $features = [];

        foreach (File::glob($dir.'/*.feature') as $path) {
            $lines = preg_split('/\R/', (string) File::get($path));
            $tags = '';
            $name = '';
            $intent = [];
            $stories = [];
            $pendingId = null;
            $inHeader = true;

            foreach ($lines as $line) {
                $trim = trim($line);

                if (str_starts_with($trim, '@')) {
                    if ($name === '') {
                        $tags .= ' '.$trim;
                    }
                    if (preg_match('/@scenario:([A-Z]{2}-\d+)/', $trim, $m)) {
                        $pendingId = $m[1];
                    }

                    continue;
                }

                if (str_starts_with($trim, 'Feature:')) {
                    $name = trim(substr($trim, strlen('Feature:')));

                    continue;
                }

                if (str_starts_with($trim, 'Scenario')) {
                    $inHeader = false;
                    $title = trim(preg_replace('/^Scenario(?: Outline)?:/', '', $trim));
                    $stories[] = ['id' => $pendingId, 'title' => $title];
                    $pendingId = null;

                    continue;
                }

                // Header prose between the Feature: line and the first scenario.
                if ($inHeader && $name !== '' && $trim !== '' && ! str_starts_with($trim, 'Rule:')) {
                    $intent[] = $trim;
                }
            }

            if ($name === '') {
                continue;
            }

            $features[] = [
                'id' => basename($path, '.feature'),
                'name' => $name,
                'mvp' => str_contains($tags, '@mvp'),
                'intent' => trim(implode(' ', $intent)),
                'story_count' => count($stories),
                'stories' => $stories,
            ];
        }

        usort($features, fn ($a, $b) => [$b['mvp'], $a['name']] <=> [$a['mvp'], $b['name']]);

        return $features;
    }
}
