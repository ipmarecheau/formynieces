<?php

use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Practice\RecordPracticeAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/** Helper: make N distinct questions at a given rung for a module. */
function rungQuestions(int $moduleId, int $rung, int $n = 3): array {
    $qs = [];
    for ($i = 0; $i < $n; $i++) {
        $qs[] = PracticeQuestion::factory()->create([
            'module_id'    => $moduleId,
            'difficulty'   => $rung,
            'options'      => ['A', 'B', 'C', 'D'],
            'correct_index'=> 1,           // 'B' is always correct in these tests
        ]);
    }
    return $qs;
}

const CORRECT = 1;   // matches correct_index above
const WRONG   = 0;

it('advances the streak on distinct correct answers and clears rung 1 at three', function () {
    $student = User::factory()->create();
    $module  = SyllabusModule::factory()->create();
    [$q1, $q2, $q3] = rungQuestions($module->id, 1);
    $svc = new RecordPracticeAttempt();

    $svc->handle($student->id, $q1->id, CORRECT);
    $svc->handle($student->id, $q2->id, CORRECT);
    $p = $svc->handle($student->id, $q3->id, CORRECT);   // third distinct correct → clears rung 1

    expect($p->current_rung)->toBe(2)
        ->and($p->current_streak)->toBe(0);
})->group('scenario:LL-03');

it('does NOT count a repeated question toward the live streak', function () {
    $student = User::factory()->create();
    $module  = SyllabusModule::factory()->create();
    [$q1, $q2] = rungQuestions($module->id, 1, 2);
    $svc = new RecordPracticeAttempt();

    $svc->handle($student->id, $q1->id, CORRECT);   // streak 1
    $p = $svc->handle($student->id, $q1->id, CORRECT);   // same question again → must NOT advance

    expect($p->current_streak)->toBe(1)
        ->and($p->current_rung)->toBe(1);
})->group('scenario:LL-05');

it('resets the streak on a wrong answer but keeps the rung', function () {
    $student = User::factory()->create();
    $module  = SyllabusModule::factory()->create();
    [$q1, $q2, $q3] = rungQuestions($module->id, 1);
    $svc = new RecordPracticeAttempt();

    $svc->handle($student->id, $q1->id, CORRECT);  // streak 1
    $svc->handle($student->id, $q2->id, CORRECT);  // streak 2
    $p = $svc->handle($student->id, $q3->id, WRONG);   // wrong → reset

    expect($p->current_streak)->toBe(0)
        ->and($p->current_rung)->toBe(1);
})->group('scenario:LL-04');

it('marks the module mastered after three distinct correct at rung 3', function () {
    $student = User::factory()->create();
    $module  = SyllabusModule::factory()->create();
    // Climb rungs 1 and 2 first, then rung 3.
    foreach ([1, 2, 3] as $rung) {
        [$a, $b, $c] = rungQuestions($module->id, $rung);
        $svc = new RecordPracticeAttempt();
        $svc->handle($student->id, $a->id, CORRECT);
        $svc->handle($student->id, $b->id, CORRECT);
        $p = $svc->handle($student->id, $c->id, CORRECT);
    }

    expect($p->status)->toBe('mastered')
        ->and($p->score)->toBe(100);
})->group('scenario:LL-06');