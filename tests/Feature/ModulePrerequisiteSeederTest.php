<?php

use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Slice 2a — structural integrity of the prerequisite graph.
 *
 * These assertions guard the invariants the diagnostic engine relies on. A cycle or
 * a dangling id here would silently corrupt mastery inference, so the test runs the
 * real seeders against a fresh in-memory DB (RefreshDatabase) and inspects the result.
 */

beforeEach(function () {
    // Modules MUST exist first — the edges reference syllabus_modules ids.
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);
});

it('seeds exactly the expected number of edges', function () {
    expect(DB::table('module_prerequisites')->count())
        ->toBe(ModulePrerequisiteSeeder::EXPECTED_EDGE_COUNT);
})->group('scenario:DG-05');

it('references only real module ids on both ends of every edge', function () {
    $moduleIds = DB::table('syllabus_modules')->pluck('id')->all();

    $edges = DB::table('module_prerequisites')
        ->select('module_id', 'prerequisite_module_id')
        ->get();

    foreach ($edges as $edge) {
        expect($moduleIds)->toContain($edge->module_id);
        expect($moduleIds)->toContain($edge->prerequisite_module_id);
    }
})->group('scenario:DG-05');

it('contains no self-loops', function () {
    $selfLoops = DB::table('module_prerequisites')
        ->whereColumn('module_id', 'prerequisite_module_id')
        ->count();

    expect($selfLoops)->toBe(0);
})->group('scenario:DG-05');

it('contains no duplicate edges', function () {
    $total = DB::table('module_prerequisites')->count();

    $distinct = DB::table('module_prerequisites')
        ->distinct()
        ->count(DB::raw('module_id || "-" || prerequisite_module_id'));

    expect($distinct)->toBe($total);
})->group('scenario:DG-05');

it('forms an acyclic graph', function () {
    // Build adjacency: module_id (B) -> prerequisite_module_id (A), i.e. B depends on A.
    $adjacency = [];
    foreach (DB::table('module_prerequisites')->get() as $edge) {
        $adjacency[$edge->module_id][] = $edge->prerequisite_module_id;
    }

    // Iterative DFS three-colour cycle detection over every node.
    $WHITE = 0;
    $GRAY = 1;
    $BLACK = 2;
    $colour = [];

    $detectCycleFrom = function (int $start) use (&$adjacency, &$colour, $WHITE, $GRAY, $BLACK): ?array {
        // Stack frames: [node, indexOfNextNeighbourToVisit]
        $stack = [[$start, 0]];
        $path = [];
        $colour[$start] = $GRAY;
        $path[] = $start;

        while (! empty($stack)) {
            [$node, $i] = $stack[count($stack) - 1];
            $neighbours = $adjacency[$node] ?? [];

            if ($i < count($neighbours)) {
                $stack[count($stack) - 1][1] = $i + 1; // advance the cursor
                $next = $neighbours[$i];
                $state = $colour[$next] ?? $WHITE;

                if ($state === $GRAY) {
                    // Back-edge → cycle. Slice the current path from $next onward.
                    $idx = array_search($next, $path, true);
                    return array_merge(array_slice($path, $idx), [$next]);
                }
                if ($state === $WHITE) {
                    $colour[$next] = $GRAY;
                    $path[] = $next;
                    $stack[] = [$next, 0];
                }
            } else {
                $colour[$node] = $BLACK;
                array_pop($stack);
                array_pop($path);
            }
        }

        return null;
    };

    $nodes = array_keys($adjacency);
    foreach ($nodes as $node) {
        if (($colour[$node] ?? $WHITE) === $WHITE) {
            $cycle = $detectCycleFrom($node);
            expect($cycle)->toBeNull(
                $cycle ? 'Cycle detected: ' . implode(' -> ', $cycle) : ''
            );
        }
    }
})->group('scenario:DG-05');

it('seeds the expected Math/ELA split', function () {
    // Math modules are ids 1..51, ELA 52..90. Every edge's "from" node (module_id)
    // sits in exactly one subject — a quick sanity check that no edge crosses subjects
    // in a way that would indicate a transcription slip.
    $mathFrom = DB::table('module_prerequisites')->whereBetween('module_id', [1, 51])->count();
    $elaFrom = DB::table('module_prerequisites')->whereBetween('module_id', [52, 90])->count();

    expect($mathFrom)->toBe(86)
        ->and($elaFrom)->toBe(64)
        ->and($mathFrom + $elaFrom)->toBe(ModulePrerequisiteSeeder::EXPECTED_EDGE_COUNT);
})->group('scenario:DG-05');
