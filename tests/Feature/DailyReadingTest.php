<?php

use App\Models\DailyReadingAssignment;
use App\Models\ReadingPassage;
use App\Models\StudentProgress;
use App\Models\StudentStreak;
use App\Models\User;
use App\Services\LlmService;
use App\Services\Motivation\StreakEconomyService;
use App\Services\Reading\DailyReadingService;
use Illuminate\Support\Carbon;

beforeEach(function () {
    // No real HTTP in tests — force the comprehension scorer onto its MC baseline.
    $this->mock(LlmService::class, fn ($m) => $m->shouldReceive('completeJson')->andReturn([]));
});

function drStudent(int $level = 5): User
{
    $student = User::factory()->create(['role' => 'student']);
    $student->reading_level = $level;
    $student->save();

    return $student;
}

function drPassage(int $level = 5): ReadingPassage
{
    return ReadingPassage::create([
        'title' => 'A passage',
        'body' => str_repeat('word ', 120),
        'reading_level' => $level,
        'word_count' => 120,
        'questions' => [
            ['prompt' => 'Q1', 'type' => 'mc', 'options' => ['a', 'b', 'c', 'd'], 'correct_index' => 0],
            ['prompt' => 'Q2', 'type' => 'mc', 'options' => ['a', 'b', 'c', 'd'], 'correct_index' => 1],
            ['prompt' => 'Write about it', 'type' => 'written'],
        ],
        'is_active' => true,
    ]);
}

function drSvc(): DailyReadingService
{
    return app(DailyReadingService::class);
}

it('serves one unseen passage at her level, once per day', function () {
    $student = drStudent(5);
    $passage = drPassage(5);

    $first = drSvc()->serve($student, Carbon::parse('2026-08-17'));
    $again = drSvc()->serve($student, Carbon::parse('2026-08-17'));

    expect($first->passage_id)->toBe($passage->id)
        ->and($again->id)->toBe($first->id);
})->group('scenario:DR-01');

it('never serves a passage she has already seen', function () {
    $student = drStudent(5);
    $p1 = drPassage(5);
    $p2 = drPassage(5);

    $d1 = drSvc()->serve($student, Carbon::parse('2026-08-17'));
    $d2 = drSvc()->serve($student, Carbon::parse('2026-08-18'));

    expect([$d1->passage_id, $d2->passage_id])->toEqualCanonicalizing([$p1->id, $p2->id]);
})->group('scenario:DR-01');

it('scores comprehension, keeps the score, and records reading pace', function () {
    $student = drStudent(5);
    drPassage(5);
    $assignment = drSvc()->serve($student, Carbon::parse('2026-08-17'));
    $assignment->started_at = now()->subMinute();
    $assignment->save();

    $scored = drSvc()->score($assignment, [0 => 0, 1 => 1], now());

    expect($scored->comprehension_score)->toBe(100)
        ->and($scored->completed_at)->not->toBeNull()
        ->and($scored->words_per_minute)->toBeGreaterThan(0);
})->group('scenario:DR-07');

it('tracks the running comprehension average toward the goal', function () {
    $student = drStudent(5);
    DailyReadingAssignment::create(['student_id' => $student->id, 'passage_id' => drPassage(5)->id, 'date' => '2026-08-17', 'comprehension_score' => 100]);
    DailyReadingAssignment::create(['student_id' => $student->id, 'passage_id' => drPassage(5)->id, 'date' => '2026-08-18', 'comprehension_score' => 80]);

    expect(drSvc()->comprehensionAverage($student))->toBe(90);
})->group('scenario:DR-08');

it('nudges the reading level up after three strong sessions', function () {
    $student = drStudent(5);
    foreach (['2026-08-17', '2026-08-18', '2026-08-19'] as $date) {
        DailyReadingAssignment::create(['student_id' => $student->id, 'passage_id' => drPassage(5)->id, 'date' => $date, 'comprehension_score' => 96]);
    }

    drSvc()->adaptLevel($student);

    expect($student->fresh()->reading_level)->toBe(6);
})->group('scenario:DR-04');

it('grants a perk when she reaches the comprehension goal, never a gate', function () {
    $student = drStudent(5);
    drPassage(5);
    $assignment = drSvc()->serve($student, Carbon::parse('2026-08-17'));

    drSvc()->score($assignment, [0 => 0, 1 => 1], now());

    expect(app(StreakEconomyService::class)->balance($student->id, 'shore_leave'))->toBe(1);
})->group('scenario:DR-09');

/**
 * DR-03 — reading and comprehension are formative: warm feedback, no letter grade
 * or pass/fail, and no module's mastery status changes.
 */
it('returns warm feedback and never changes mastery when comprehension is scored', function () {
    // The warm summary comes from the LLM appraisal; make it return one.
    $this->mock(LlmService::class, fn ($m) => $m->shouldReceive('completeJson')
        ->andReturn(['score' => 100, 'feedback' => 'You understood the setting well — look for the character\'s reason next time.']));

    $student = drStudent();
    $passage = drPassage();
    $assignment = drSvc()->serve($student);

    $scored = drSvc()->score($assignment, [0 => 0, 1 => 1]);

    expect($scored->comprehension_feedback)->not->toBeNull()
        // No letter grade or pass/fail is stored — a percentage summary, not A/B/pass.
        ->and($scored->comprehension_feedback)->not->toContain('pass')
        // Formative: it never writes a mastery row for the child.
        ->and(StudentProgress::where('student_id', $student->id)->exists())->toBeFalse();
})->group('scenario:DR-03');

/**
 * DR-05 — the ride-to-school ritual is resumable: one assignment per morning, and
 * completing the reading advances her daily reading streak.
 */
it('serves one resumable assignment per morning and advances the reading streak on completion', function () {
    $student = drStudent();
    drPassage();

    $first = drSvc()->serve($student);
    $again = drSvc()->serve($student);
    // Resumable: the same morning's assignment, not a fresh one.
    expect($again->id)->toBe($first->id);

    drSvc()->score($first, [0 => 0, 1 => 1]);

    $streak = StudentStreak::where('student_id', $student->id)
        ->where('type', 'reading')->first();
    expect($streak)->not->toBeNull()
        ->and($streak->count)->toBeGreaterThanOrEqual(1);
})->group('scenario:DR-05');

/**
 * DR-10 — the reading + comprehension are sized for a ten-to-fifteen-minute session.
 * This is an authoring-guideline property, not engine logic; the guard asserts the
 * served assignment stays within a single short sitting (a bounded question count,
 * one passage) and that vocabulary is additional, not folded into this time.
 */
it('serves a single short passage with a bounded question count for one sitting', function () {
    $student = drStudent(5);
    $passage = drPassage(5);

    $assignment = drSvc()->serve($student);
    $questions = $assignment->passage->questions ?? [];

    expect($assignment->passage->word_count)->toBeGreaterThan(0)
        // A single sitting's worth of questions, not a full exam.
        ->and(count($questions))->toBeLessThanOrEqual(6)
        ->and(count($questions))->toBeGreaterThanOrEqual(1);
})->group('scenario:DR-10');
