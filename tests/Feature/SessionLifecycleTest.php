<?php

use App\Services\Diagnostic\ItemWalk;
use App\Services\Diagnostic\MasteryInference;
use App\Services\Diagnostic\SessionLifecycle;
use App\Services\Diagnostic\SessionPlanner;
use Database\Seeders\ElaAnchorQuestionSeeder;
use Database\Seeders\MathAnchorQuestionSeeder;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Database\Seeders\WritingAnchorQuestionSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Step 2e of the diagnostic engine — SessionLifecycle.
 *
 * Covers the start/resume/complete scenarios from diagnostic.feature and the
 * write-out of the mastery map into student_progress.
 */
beforeEach(function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);
    $this->seed(MathAnchorQuestionSeeder::class);
    $this->seed(ElaAnchorQuestionSeeder::class);
    $this->seed(WritingAnchorQuestionSeeder::class);

    $this->planner = new SessionPlanner;
    $this->walk = new ItemWalk($this->planner);
    $this->lifecycle = app(SessionLifecycle::class);
});

/** Create a student; onboarding complete unless $onboarded is false. */
function makeStudent(bool $onboarded = true): int
{
    return DB::table('users')->insertGetId([
        'name' => 'Life Student',
        'email' => 'life-'.uniqid().'@example.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => $onboarded ? now() : null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

/** Walk a whole session to the end, answering correctly. */
function walkToEnd(ItemWalk $walk, int $sessionId): void
{
    for ($i = 0; $i < 40; $i++) {
        $q = $walk->currentQuestion($sessionId);
        if ($q === null) {
            break;
        }
        $anchor = DB::table('anchor_questions')->find($q['anchor_id']);
        $walk->submitAnswer($sessionId, $q['anchor_id'], $anchor->correct_index);
    }
}

it('lets a not-yet-onboarded student start the diagnostic', function () {
    // Onboarding is completed BY doing the diagnostic, so a not-yet-onboarded
    // student must be able to start it (the prior guard wrongly blocked this).
    $studentId = makeStudent(onboarded: false);

    $sessionId = $this->lifecycle->startOrResume($studentId);

    $session = DB::table('diagnostic_sessions')->find($sessionId);
    expect($session->status)->toBe('in_progress');
})->group('scenario:GO-05');

it('marks the student onboarded when the diagnostic completes', function () {
    $studentId = makeStudent(onboarded: false);
    $sessionId = $this->lifecycle->startOrResume($studentId);
    walkToEnd($this->walk, $sessionId);

    $this->lifecycle->complete($sessionId);

    expect(DB::table('users')->find($studentId)->onboarding_completed_at)->not->toBeNull();
})->group('scenario:GO-05');

it('starts a planned session when onboarding is complete', function () {
    $studentId = makeStudent();

    $sessionId = $this->lifecycle->startOrResume($studentId);

    $session = DB::table('diagnostic_sessions')->find($sessionId);
    expect($session->status)->toBe('in_progress');
    expect(json_decode($session->item_plan, true))->not->toBeEmpty();
})->group('scenario:DG-13');

it('resumes the same session instead of creating a second', function () {
    $studentId = makeStudent();

    $first = $this->lifecycle->startOrResume($studentId);
    $second = $this->lifecycle->startOrResume($studentId);

    expect($second)->toBe($first);
    expect(DB::table('diagnostic_sessions')->where('student_id', $studentId)->count())->toBe(1);
})->group('scenario:DG-15');

it('reports ready to complete only once the plan is walked', function () {
    $studentId = makeStudent();
    $sessionId = $this->lifecycle->startOrResume($studentId);

    expect($this->lifecycle->isReadyToComplete($sessionId))->toBeFalse();

    walkToEnd($this->walk, $sessionId);

    expect($this->lifecycle->isReadyToComplete($sessionId))->toBeTrue();
})->group('scenario:RR-01');

it('writes the mastery map into student_progress on completion', function () {
    $studentId = makeStudent();
    $sessionId = $this->lifecycle->startOrResume($studentId);
    walkToEnd($this->walk, $sessionId);

    $map = $this->lifecycle->complete($sessionId);

    expect($map)->not->toBeEmpty();

    $rows = DB::table('student_progress')->where('student_id', $studentId)->get();
    expect($rows->count())->toBe(count($map));

    // Every written status is one of the engine's vocabulary.
    foreach ($rows as $row) {
        expect($row->status)->toBeIn([
            MasteryInference::STATUS_MASTERED,
            MasteryInference::STATUS_INFERRED,
            MasteryInference::STATUS_NEEDS_WORK,
        ]);
    }
})->group('scenario:RR-01');

it('marks the session completed with a timestamp', function () {
    $studentId = makeStudent();
    $sessionId = $this->lifecycle->startOrResume($studentId);
    walkToEnd($this->walk, $sessionId);

    $this->lifecycle->complete($sessionId);

    $session = DB::table('diagnostic_sessions')->find($sessionId);
    expect($session->status)->toBe('completed');
    expect($session->completed_at)->not->toBeNull();
})->group('scenario:RR-01');

it('records a difficulty score for directly-mastered modules', function () {
    $studentId = makeStudent();
    $sessionId = $this->lifecycle->startOrResume($studentId);
    walkToEnd($this->walk, $sessionId);
    $this->lifecycle->complete($sessionId);

    // At least one directly-mastered module should carry a difficulty score 1-3.
    $scored = DB::table('student_progress')
        ->where('student_id', $studentId)
        ->where('status', MasteryInference::STATUS_MASTERED)
        ->whereNotNull('score')
        ->first();

    expect($scored)->not->toBeNull();
    expect($scored->score)->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(3);
})->group('scenario:RR-01');

it('inferred modules carry no direct score', function () {
    $studentId = makeStudent();
    $sessionId = $this->lifecycle->startOrResume($studentId);
    walkToEnd($this->walk, $sessionId);
    $this->lifecycle->complete($sessionId);

    $inferred = DB::table('student_progress')
        ->where('student_id', $studentId)
        ->where('status', MasteryInference::STATUS_INFERRED)
        ->get();

    foreach ($inferred as $row) {
        expect($row->score)->toBeNull();
    }
})->group('scenario:RR-01');

it('preserves the prior score as previous_score on a retake', function () {
    $studentId = makeStudent();

    // First diagnostic.
    $s1 = $this->lifecycle->startOrResume($studentId);
    walkToEnd($this->walk, $s1);
    $this->lifecycle->complete($s1);

    $before = DB::table('student_progress')
        ->where('student_id', $studentId)
        ->where('status', MasteryInference::STATUS_MASTERED)
        ->whereNotNull('score')
        ->first();

    // Second diagnostic (retake): new session, walk, complete.
    $s2 = $this->lifecycle->startOrResume($studentId);
    walkToEnd($this->walk, $s2);
    $this->lifecycle->complete($s2);

    $after = DB::table('student_progress')
        ->where('student_id', $studentId)
        ->where('module_id', $before->module_id)
        ->first();

    expect($after->previous_score)->toBe($before->score);
})->group('scenario:DG-17');

it('completion is idempotent', function () {
    $studentId = makeStudent();
    $sessionId = $this->lifecycle->startOrResume($studentId);
    walkToEnd($this->walk, $sessionId);

    $first = $this->lifecycle->complete($sessionId);
    $second = $this->lifecycle->complete($sessionId);

    ksort($first);
    ksort($second);
    expect($second)->toBe($first);

    // No duplicate progress rows from the second completion.
    $studentRows = DB::table('student_progress')->where('student_id', $studentId)->count();
    expect($studentRows)->toBe(count($first));
})->group('scenario:RR-01');
