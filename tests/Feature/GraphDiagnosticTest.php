<?php

use App\Services\Diagnostic\GraphDiagnostic;

// A three-module chain: 3 requires 2 requires 1.
function chainDiagnostic(): GraphDiagnostic
{
    return new GraphDiagnostic(
        prerequisites: [1 => [], 2 => [1], 3 => [2]],
        allModuleIds: [1, 2, 3],
    );
}

it('probes the hardest (most-covering) competency first', function () {
    // Module 3 sits atop the chain — probing it can resolve the whole thing.
    expect(chainDiagnostic()->nextModule([]))->toBe(3);
})->group('scenario:DG-18');

it('a correct hardest answer infers the whole prerequisite chain in one question', function () {
    $diag = chainDiagnostic();
    $answers = [['module_id' => 3, 'is_correct' => true]];

    expect($diag->isComplete($answers))->toBeTrue();      // 1,2 inferred — no more questions
    $map = $diag->map($answers);
    expect($map[3])->toBe('mastered');
    expect($map[2])->toBe('inferred_mastered');
    expect($map[1])->toBe('inferred_mastered');
})->group('scenario:DG-18');

it('descends to a prerequisite when the hardest answer is wrong', function () {
    $diag = chainDiagnostic();
    $answers = [['module_id' => 3, 'is_correct' => false]];

    expect($diag->isComplete($answers))->toBeFalse();
    expect($diag->nextModule($answers))->toBe(2);          // descend to the next-down competency
})->group('scenario:DG-18');

it('probes independent competencies individually', function () {
    $diag = new GraphDiagnostic(
        prerequisites: [10 => [], 11 => [], 12 => []],
        allModuleIds: [10, 11, 12],
    );

    // Nothing infers anything else: each must be asked.
    $first = $diag->nextModule([]);
    expect($first)->toBeIn([10, 11, 12]);
    $answers = [['module_id' => $first, 'is_correct' => true]];
    expect($diag->nextModule($answers))->not->toBe($first);
    expect($diag->isComplete([
        ['module_id' => 10, 'is_correct' => true],
        ['module_id' => 11, 'is_correct' => true],
        ['module_id' => 12, 'is_correct' => true],
    ]))->toBeTrue();
})->group('scenario:DG-18');
