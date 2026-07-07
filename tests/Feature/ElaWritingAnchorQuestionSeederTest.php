<?php

use Database\Seeders\ElaAnchorQuestionSeeder;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Database\Seeders\WritingAnchorQuestionSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Slice 2c — structural integrity of the ELA and Writing anchor banks.
 *
 * ELA (52-68 + 73-90) and Writing (69-72) are SEPARATE banks. ELA must give
 * >=3x coverage with an even Section I / Section II split; Writing must cover
 * each of its four modules 3x directly. Mastery from Writing anchors must not
 * propagate into the ELA reading modules (asserted at the engine layer, not here).
 */

beforeEach(function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);
    $this->seed(ElaAnchorQuestionSeeder::class);
    $this->seed(WritingAnchorQuestionSeeder::class);
});

it('seeds the full ELA bank', function () {
    expect(DB::table('anchor_questions')->where('subject', 'ELA')->count())->toBe(43);
})->group('scenario:DG-13');

it('seeds the full Writing bank', function () {
    expect(DB::table('anchor_questions')->where('subject', 'Writing')->count())->toBe(12);
})->group('scenario:DG-16');

it('splits ELA evenly between Section I and Section II', function () {
    $secI = DB::table('anchor_questions')->where('subject', 'ELA')->where('sea_section', 'Section I')->count();
    $secII = DB::table('anchor_questions')->where('subject', 'ELA')->where('sea_section', 'Section II')->count();

    // "Roughly even" — allow a small imbalance but no more than a few items apart.
    expect(abs($secI - $secII))->toBeLessThanOrEqual(3);
    expect($secI + $secII)->toBe(43);
})->group('scenario:DG-13');

it('covers each Writing module exactly three times', function () {
    $counts = DB::table('anchor_question_module')
        ->join('anchor_questions', 'anchor_questions.id', '=', 'anchor_question_module.anchor_question_id')
        ->where('anchor_questions.subject', 'Writing')
        ->select('module_id', DB::raw('COUNT(*) as n'))
        ->groupBy('module_id')
        ->pluck('n', 'module_id');

    foreach ([69, 70, 71, 72] as $m) {
        expect($counts[$m] ?? 0)->toBe(3);
    }
})->group('scenario:DG-16');

it('gives every ELA and Writing anchor four options, valid index, and provenance', function () {
    $anchors = DB::table('anchor_questions')->whereIn('subject', ['ELA', 'Writing'])->get();

    foreach ($anchors as $a) {
        $options = json_decode($a->options, true);
        expect($options)->toBeArray()->toHaveCount(4);
        expect(count(array_unique($options)))->toBe(4);          // no duplicate options
        expect($a->correct_index)->toBeGreaterThanOrEqual(0)->toBeLessThanOrEqual(3);

        $meta = json_decode($a->distractor_notes, true)['meta'] ?? [];
        expect($meta['source'] ?? null)->not->toBeNull();
        expect($meta['license'] ?? null)->not->toBeNull();
    }
})->group('scenario:DG-13');

it('covers every ELA module at least three times without propagating through Writing', function () {
    // Exclude edges that point INTO a writing node (69-72): mastery must not
    // flow through Writing when covering ELA reading/mechanics modules.
    $writing = [69, 70, 71, 72];

    $edges = DB::table('module_prerequisites')
        ->whereBetween('module_id', [52, 90])
        ->whereNotIn('prerequisite_module_id', $writing)
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

    $elaModules = array_merge(range(52, 68), range(73, 90));

    $directModules = DB::table('anchor_question_module')
        ->join('anchor_questions', 'anchor_questions.id', '=', 'anchor_question_module.anchor_question_id')
        ->where('anchor_questions.subject', 'ELA')
        ->pluck('anchor_question_module.module_id');

    $coverage = array_fill_keys($elaModules, 0);
    foreach ($directModules as $m) {
        $coverage[$m]++;
        foreach ($closure($m) as $a) {
            if (in_array($a, $elaModules, true)) {
                $coverage[$a]++;
            }
        }
    }

    $under = collect($coverage)->filter(fn ($c) => $c < 3)->keys()->all();
    expect($under)->toBe([], 'ELA modules under 3x coverage: ' . implode(', ', $under));
})->group('scenario:DG-13');
