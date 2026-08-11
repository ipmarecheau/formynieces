<?php

use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Practice\RecordPracticeAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/** Helper: make N distinct questions at a given difficulty rung (1/3/5) for a module. */
function rungQuestions(int $moduleId, int $rung, int $n = 3): array
{
    $qs = [];
    for ($i = 0; $i < $n; $i++) {
        $qs[] = PracticeQuestion::factory()->create([
            'module_id' => $moduleId,
            'difficulty' => $rung,
            'options' => ['A', 'B', 'C', 'D'],
            'correct_index' => 1,           // 'B' is always correct in these tests
        ]);
    }

    return $qs;
}

const CORRECT = 1;   // matches correct_index above
const WRONG = 0;
const FIRST_TRY = 1;
const SECOND_TRY = 2;

it('advances the climb D1 -> D3 on three distinct first-try correct answers', function () {
    $student = User::factory()->create();
    $module = SyllabusModule::factory()->create();
    [$q1, $q2, $q3] = rungQuestions($module->id, 1);
    $svc = app(RecordPracticeAttempt::class);

    $svc->handle($student->id, $q1->id, CORRECT);
    $svc->handle($student->id, $q2->id, CORRECT);
    $p = $svc->handle($student->id, $q3->id, CORRECT);   // clears rung 1

    expect($p->current_rung)->toBe(3)          // D1 -> D3
        ->and($p->current_streak)->toBe(0);
})->group('scenario:LL-03');

it('lets a recovered (second-try) answer count toward advancing a lower rung', function () {
    $student = User::factory()->create();
    $module = SyllabusModule::factory()->create();
    [$q1, $q2, $q3] = rungQuestions($module->id, 1);
    $svc = app(RecordPracticeAttempt::class);

    $svc->handle($student->id, $q1->id, CORRECT, FIRST_TRY);       // streak 1
    $svc->handle($student->id, $q2->id, CORRECT, FIRST_TRY);       // streak 2
    $svc->handle($student->id, $q3->id, WRONG, FIRST_TRY);         // first-try miss: no change
    $p = $svc->handle($student->id, $q3->id, CORRECT, SECOND_TRY); // recovered: counts -> advance

    expect($p->current_rung)->toBe(3);
})->group('scenario:LL-12');

it('does NOT count a repeated question toward the live streak', function () {
    $student = User::factory()->create();
    $module = SyllabusModule::factory()->create();
    [$q1] = rungQuestions($module->id, 1, 1);
    $svc = app(RecordPracticeAttempt::class);

    $svc->handle($student->id, $q1->id, CORRECT);   // streak 1
    $p = $svc->handle($student->id, $q1->id, CORRECT);   // same question again → must NOT advance

    expect($p->current_streak)->toBe(1)
        ->and($p->current_rung)->toBe(1);
})->group('scenario:LL-05');

it('resets the streak when a question is failed on both attempts, keeping the rung', function () {
    $student = User::factory()->create();
    $module = SyllabusModule::factory()->create();
    [$q1, $q2, $q3] = rungQuestions($module->id, 1);
    $svc = app(RecordPracticeAttempt::class);

    $svc->handle($student->id, $q1->id, CORRECT);              // streak 1
    $svc->handle($student->id, $q2->id, CORRECT);              // streak 2
    $svc->handle($student->id, $q3->id, WRONG, FIRST_TRY);     // first miss — pending retry
    $p = $svc->handle($student->id, $q3->id, WRONG, SECOND_TRY); // failed both → reset

    expect($p->current_streak)->toBe(0)
        ->and($p->current_rung)->toBe(1);
})->group('scenario:LL-04');

it('masters the module after three first-try-correct at difficulty 5', function () {
    $student = User::factory()->create();
    $module = SyllabusModule::factory()->create();
    $svc = app(RecordPracticeAttempt::class);

    // Climb rungs 1 and 3 first, then master at 5.
    foreach ([1, 3, 5] as $rung) {
        [$a, $b, $c] = rungQuestions($module->id, $rung);
        $svc->handle($student->id, $a->id, CORRECT);
        $svc->handle($student->id, $b->id, CORRECT);
        $p = $svc->handle($student->id, $c->id, CORRECT);
    }

    expect($p->status)->toBe('mastered')
        ->and($p->score)->toBe(100);
})->group('scenario:LL-06');

it('never grants mastery on a retry — a first-try miss at D5 resets the mastery streak', function () {
    $student = User::factory()->create();
    $module = SyllabusModule::factory()->create();
    $svc = app(RecordPracticeAttempt::class);

    // Climb to the hardest rung.
    foreach ([1, 3] as $rung) {
        [$a, $b, $c] = rungQuestions($module->id, $rung);
        $svc->handle($student->id, $a->id, CORRECT);
        $svc->handle($student->id, $b->id, CORRECT);
        $svc->handle($student->id, $c->id, CORRECT);
    }

    [$q1, $q2, $q3] = rungQuestions($module->id, 5);
    $svc->handle($student->id, $q1->id, CORRECT, FIRST_TRY);        // mastery streak 1
    $svc->handle($student->id, $q2->id, CORRECT, FIRST_TRY);        // mastery streak 2
    $svc->handle($student->id, $q3->id, WRONG, FIRST_TRY);          // first-try miss → reset
    $p = $svc->handle($student->id, $q3->id, CORRECT, SECOND_TRY);  // recovery must NOT master

    expect($p->status)->not->toBe('mastered')
        ->and($p->current_streak)->toBe(0);
})->group('scenario:LL-13');
