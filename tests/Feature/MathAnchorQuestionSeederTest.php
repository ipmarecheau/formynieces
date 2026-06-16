<?php

use Database\Seeders\MathAnchorQuestionSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Slice 2b — structural integrity of the Math anchor bank.
 *
 * Asserts the seeded anchors are well-formed and give >=3x coverage of every
 * Math module (direct + indirect through the prerequisite graph). Coverage is
 * the property the diagnostic relies on: each module must be reachable enough
 * times for the adaptive walk to certify it.
 */

beforeEach(function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(\Database\Seeders\ModulePrerequisiteSeeder::class);
    $this->seed(MathAnchorQuestionSeeder::class);
});

it('seeds the full Math anchor bank', function () {
    expect(DB::table('anchor_questions')->where('subject', 'Math')->count())->toBe(65);
});

it('gives every Math anchor exactly four options and a valid correct index', function () {
    $anchors = DB::table('anchor_questions')->where('subject', 'Math')->get();

    foreach ($anchors as $a) {
        $options = json_decode($a->options, true);
        expect($options)->toBeArray()->toHaveCount(4);
        expect($a->correct_index)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(3);
    }
});

it('records a misconception for each distractor', function () {
    $anchors = DB::table('anchor_questions')->where('subject', 'Math')->get();

    foreach ($anchors as $a) {
        $notes = json_decode($a->distractor_notes, true);
        $misconceptions = $notes['misconceptions'] ?? [];

        // Exactly the three non-correct indices carry a misconception note.
        $expectedKeys = collect([0, 1, 2, 3])->reject(fn ($i) => $i === $a->correct_index)->values();
        expect(collect(array_keys($misconceptions))->map(fn ($k) => (int) $k)->sort()->values()->all())
            ->toBe($expectedKeys->sort()->values()->all());
    }
});

it('carries provenance on every anchor', function () {
    $anchors = DB::table('anchor_questions')->where('subject', 'Math')->get();

    foreach ($anchors as $a) {
        $meta = json_decode($a->distractor_notes, true)['meta'] ?? [];
        expect($meta['source'] ?? null)->not->toBeNull();
        expect($meta['license'] ?? null)->not->toBeNull();
    }
});

it('links every anchor to a real module', function () {
    $moduleIds = DB::table('syllabus_modules')->pluck('id')->all();

    $links = DB::table('anchor_question_module')
        ->join('anchor_questions', 'anchor_questions.id', '=', 'anchor_question_module.anchor_question_id')
        ->where('anchor_questions.subject', 'Math')
        ->pluck('anchor_question_module.module_id');

    expect($links)->not->toBeEmpty();
    foreach ($links as $mid) {
        expect($moduleIds)->toContain($mid);
    }
});

it('covers every Math module at least three times, direct plus indirect', function () {
    // Direct: an anchor attached to module m covers m.
    // Indirect: covering m also covers everything m transitively requires.
    $edges = DB::table('module_prerequisites')
        ->whereBetween('module_id', [1, 51])
        ->get();

    $adjacency = [];
    foreach ($edges as $e) {
        $adjacency[$e->module_id][] = $e->prerequisite_module_id;
    }

    $closure = function (int $start) use ($adjacency): array {
        $seen = [];
        $stack = [$start];
        while ($stack) {
            $n = array_pop($stack);
            foreach ($adjacency[$n] ?? [] as $a) {
                if (! isset($seen[$a])) {
                    $seen[$a] = true;
                    $stack[] = $a;
                }
            }
        }

        return array_keys($seen);
    };

    $directModules = DB::table('anchor_question_module')
        ->join('anchor_questions', 'anchor_questions.id', '=', 'anchor_question_module.anchor_question_id')
        ->where('anchor_questions.subject', 'Math')
        ->pluck('anchor_question_module.module_id');

    $coverage = array_fill(1, 51, 0);
    foreach ($directModules as $m) {
        $coverage[$m]++;
        foreach ($closure($m) as $a) {
            if ($a >= 1 && $a <= 51) {
                $coverage[$a]++;
            }
        }
    }

    $under = collect($coverage)->filter(fn ($c) => $c < 3)->keys()->all();
    expect($under)->toBe([], 'Modules under 3x coverage: ' . implode(', ', $under));
});