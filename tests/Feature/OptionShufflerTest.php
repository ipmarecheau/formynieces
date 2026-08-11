<?php

use App\Models\PracticeQuestion;
use App\Services\QuestionBank\MoodleQuestionImporter;
use App\Support\OptionShuffler;
use Database\Seeders\SyllabusModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shuffles options while preserving which one is correct', function () {
    $result = OptionShuffler::shuffle(['A', 'B', 'C', 'D'], 0, seed: 'q-42');

    // The correct option's text still sits at the returned index.
    expect($result['options'][$result['correct_index']])->toBe('A');
    expect($result['options'])->toHaveCount(4);
    expect(collect($result['options'])->sort()->values()->all())->toBe(['A', 'B', 'C', 'D']);
});

it('is deterministic for a given seed (stable across re-imports)', function () {
    $a = OptionShuffler::shuffle(['A', 'B', 'C', 'D'], 0, seed: 'same-seed');
    $b = OptionShuffler::shuffle(['A', 'B', 'C', 'D'], 0, seed: 'same-seed');

    expect($a)->toBe($b);
});

it('spreads the correct answer across positions, not always the first', function () {
    // Many distinct questions whose correct answer starts at index 0.
    $indexes = collect(range(1, 60))->map(
        fn ($n) => OptionShuffler::shuffle(['A', 'B', 'C', 'D'], 0, seed: "q-{$n}")['correct_index']
    );

    expect($indexes->unique()->count())->toBeGreaterThan(1); // not all the same position
    expect($indexes->every(fn ($i) => $i === 0))->toBeFalse();
})->group('scenario:QB-16');

it('randomises the correct-answer position on import', function () {
    $this->seed(SyllabusModuleSeeder::class);

    // 8 multichoice questions, correct answer ALWAYS the first option.
    $questions = collect(range(1, 8))->map(fn ($n) => <<<XML
      <question type="multichoice">
        <name><text>M6 · D1 · v{$n} — Whole Number Operations: Addition and Subtraction</text></name>
        <questiontext format="html"><text><![CDATA[<p>Q{$n}?</p>]]></text></questiontext>
        <answer fraction="100" format="html"><text><![CDATA[right-{$n}]]></text></answer>
        <answer fraction="0" format="html"><text><![CDATA[w1-{$n}]]></text></answer>
        <answer fraction="0" format="html"><text><![CDATA[w2-{$n}]]></text></answer>
        <answer fraction="0" format="html"><text><![CDATA[w3-{$n}]]></text></answer>
      </question>
      XML)->implode("\n");

    $xml = "<?xml version=\"1.0\"?><quiz>{$questions}</quiz>";
    app(MoodleQuestionImporter::class)->import($xml, dryRun: false);

    $rows = PracticeQuestion::all();
    expect($rows)->toHaveCount(8);

    // Every stored correct_index still points at the "right-*" option.
    $rows->each(function (PracticeQuestion $q) {
        expect($q->options[$q->correct_index])->toStartWith('right-');
    });

    // And the correct answers are not all at position 0.
    expect($rows->pluck('correct_index')->every(fn ($i) => $i === 0))->toBeFalse();
})->group('scenario:QB-16');
