<?php

use App\Livewire\MorningTide;
use App\Models\DailyPlan;
use App\Models\DailyReadingAssignment;
use App\Models\ReadingPassage;
use App\Models\User;
use App\Models\VocabularyWord;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

function mtStudent(): User
{
    $student = User::factory()->create(['role' => 'student']);
    $student->reading_level = 5;
    $student->save();

    return $student;
}

function mtPassage(): ReadingPassage
{
    $passage = ReadingPassage::create([
        'title' => 'The Lighthouse',
        'body' => str_repeat('word ', 100),
        'reading_level' => 5,
        'word_count' => 100,
        'questions' => [
            ['prompt' => 'Q1', 'type' => 'mc', 'options' => ['a', 'b'], 'correct_index' => 0],
            ['prompt' => 'Q2', 'type' => 'mc', 'options' => ['a', 'b'], 'correct_index' => 1],
        ],
        'is_active' => true,
    ]);
    VocabularyWord::create(['passage_id' => $passage->id, 'word' => 'beacon', 'definition' => 'a light', 'context_sentence' => 'The beacon shone.']);
    VocabularyWord::create(['passage_id' => $passage->id, 'word' => 'weary', 'definition' => 'tired', 'context_sentence' => 'A weary crew.']);

    return $passage;
}

it('walks read → comprehension → vocab and completes the Morning Tide duty', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-18 08:00')); // Tuesday
    $student = mtStudent();
    mtPassage();
    $this->actingAs($student);

    $words = VocabularyWord::pluck('id')->all();

    Livewire::test(MorningTide::class)
        ->assertSet('phase', 'read')
        ->assertSee('The Lighthouse')
        ->call('startCheck')->assertSet('phase', 'check')
        ->set('currentAnswer', 0)->call('nextQuestion')
        ->set('currentAnswer', 1)->call('nextQuestion')
        ->assertSet('phase', 'pick')
        ->assertSee('beacon')
        ->call('toggleChoose', $words[0])
        ->call('toggleChoose', $words[1])
        ->call('startWriting')->assertSet('phase', 'vocab')
        ->set('currentSentence', 'The beacon guided the ship.')->call('nextWord')
        ->set('currentSentence', 'I felt weary after the climb.')->call('nextWord')
        ->assertSet('phase', 'done');

    $plan = DailyPlan::where('student_id', $student->id)->where('date', '2026-08-18')->first();
    expect($plan->duties['morning_tide'])->toBeTrue();

    $assignment = DailyReadingAssignment::where('student_id', $student->id)->first();
    expect($assignment->comprehension_score)->toBe(100)
        ->and($assignment->completed_at)->not->toBeNull();

    Carbon::setTestNow();
})->group('scenario:DR-02');

it('serves the Morning Tide page', function () {
    $student = mtStudent();
    mtPassage();

    $this->actingAs($student)
        ->get(route('student.morning-tide'))
        ->assertOk()
        ->assertSee('Morning Tide');
})->group('scenario:DR-02');
