<?php

use App\Models\DailyReadingAssignment;
use App\Models\ReadingPassage;
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
