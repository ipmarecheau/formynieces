<?php

use App\Services\Diagnostic\MasteryInference;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Step 1 of the diagnostic engine — MasteryInference.
 *
 * These tests drive the propagation scenarios from diagnostic.feature against
 * the REAL seeded prerequisite graph, so a regression in the graph or the walk
 * fails here. Each `it()` maps to a named scenario.
 */

beforeEach(function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $edges = DB::table('module_prerequisites')->get(['module_id', 'prerequisite_module_id']);
    $this->inference = MasteryInference::fromEdges($edges);
});

/** Build a responses collection from [module, difficulty, correct] tuples. */
function responses(array $rows): Collection
{
    return collect($rows)->map(fn ($r) => (object) [
        'module_id'  => $r[0],
        'difficulty' => $r[1],
        'is_correct' => $r[2],
    ]);
}

it('infers the direct prerequisites of a correct medium anchor', function () {
    // diagnostic.feature: "A correct anchor infers its direct prerequisites"
    $map = $this->inference->deriveMap(responses([[23, 2, true]]));

    expect($map[23])->toBe(MasteryInference::STATUS_MASTERED)
        ->and($map[22])->toBe(MasteryInference::STATUS_INFERRED)
        ->and($map[15])->toBe(MasteryInference::STATUS_INFERRED);
});

it('infers transitively along a prerequisite chain', function () {
    // diagnostic.feature: "Inference is transitive along a prerequisite chain"
    $map = $this->inference->deriveMap(responses([[27, 3, true]]));

    expect($map[27])->toBe(MasteryInference::STATUS_MASTERED);
    foreach ([25, 21, 26, 49, 23, 22, 12] as $m) {
        expect($map[$m])->toBe(MasteryInference::STATUS_INFERRED);
    }
});

it('does not propagate from an easy correct answer', function () {
    // diagnostic.feature: "Inference requires unambiguous evidence" (D3)
    $map = $this->inference->deriveMap(responses([[23, 1, true]]));

    expect($map[23])->toBe(MasteryInference::STATUS_MASTERED)
        ->and($map)->not->toHaveKey(22);
});

it('walks back inferred mastery when a harder item in the chain is failed', function () {
    // diagnostic.feature: "A failed harder item un-marks a previously inferred module"
    // 13 correct (medium) infers 12; then 16 wrong (hard) contradicts the chain.
    $map = $this->inference->deriveMap(responses([
        [13, 2, true],
        [16, 3, false],
    ]));

    // The directly-earned fact stands; the inference is removed.
    expect($map[13])->toBe(MasteryInference::STATUS_MASTERED);
    expect($map['12'] ?? null)->not->toBe(MasteryInference::STATUS_INFERRED);
});

it('keeps walk-back bounded to the contradicted chain', function () {
    // diagnostic.feature: "Walk-back is bounded to the contradicted chain"
    // Fractions failure must not touch an unrelated Geometry inference.
    $map = $this->inference->deriveMap(responses([
        [34, 2, true],   // Geometry: infers 33, 30, 29, 28, 31...
        [13, 2, true],   // Fractions: infers 12
        [16, 3, false],  // Fractions failure: walks back 12, not Geometry
    ]));

    expect($map[33])->toBe(MasteryInference::STATUS_INFERRED) // Geometry untouched
        ->and($map['12'] ?? null)->not->toBe(MasteryInference::STATUS_INFERRED);
});

it('does not infer a writing node from a poetry module above it', function () {
    // diagnostic.feature: "A writing node is not inferred from a poetry module above it"
    // 86 requires 71 (writing); inference must not reach 71.
    $map = $this->inference->deriveMap(responses([[86, 3, true]]));

    expect($map[86])->toBe(MasteryInference::STATUS_MASTERED)
        ->and($map)->not->toHaveKey(71);
});

it('does not flow mastery through a writing node to its prerequisites', function () {
    // diagnostic.feature: "Mastery does not flow through a writing node"
    // 71 correct masters 71 only; poetry prereqs 81/82 must NOT be inferred.
    $map = $this->inference->deriveMap(responses([[71, 3, true]]));

    expect($map[71])->toBe(MasteryInference::STATUS_MASTERED)
        ->and($map)->not->toHaveKey(81)
        ->and($map)->not->toHaveKey(82);
});

it('marks a directly failed module as needs_work', function () {
    $map = $this->inference->deriveMap(responses([[5, 2, false]]));

    expect($map[5])->toBe(MasteryInference::STATUS_NEEDS_WORK);
});

it('lets a correct result override an earlier wrong on the same module', function () {
    // Re-tested module: the hardest correct evidence wins.
    $map = $this->inference->deriveMap(responses([
        [8, 1, false],
        [8, 2, true],
    ]));

    expect($map[8])->toBe(MasteryInference::STATUS_MASTERED);
});

it('recomputes identically regardless of response order', function () {
    // Determinism: order must not change the final map.
    $rows = [[27, 3, true], [13, 2, true], [16, 3, false], [34, 2, true]];

    $a = $this->inference->deriveMap(responses($rows));
    $b = $this->inference->deriveMap(responses(array_reverse($rows)));

    ksort($a);
    ksort($b);
    expect($a)->toBe($b);
});