<?php

use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use App\Services\QuestionBank\MoodleQuestionExporter;
use App\Services\QuestionBank\MoodleQuestionImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('exports the practice bank to Moodle XML grouped by module, one correct answer each', function () {
    $module = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Fractions: Equivalent Fractions']);
    PracticeQuestion::factory()->create([
        'module_id' => $module->id,
        'difficulty' => 2,
        'prompt' => 'What is 1/2 + 1/2?',
        'options' => ['1', '1/4', '2', '0'],
        'correct_index' => 0,
    ]);

    $xml = app(MoodleQuestionExporter::class)->export();

    $doc = new DOMDocument;
    expect($doc->loadXML($xml))->toBeTrue(); // well-formed

    $cats = collect(iterator_to_array($doc->getElementsByTagName('question')))
        ->filter(fn ($q) => $q->getAttribute('type') === 'category');
    expect($cats)->toHaveCount(1)
        ->and($cats->first()->getElementsByTagName('text')->item(0)->textContent)->toContain("M{$module->id}");

    $mc = collect(iterator_to_array($doc->getElementsByTagName('question')))
        ->first(fn ($q) => $q->getAttribute('type') === 'multichoice');
    $fractions = collect(iterator_to_array($mc->getElementsByTagName('answer')))
        ->map(fn ($a) => $a->getAttribute('fraction'));
    expect($fractions)->toHaveCount(4)
        ->and($fractions->filter(fn ($f) => $f === '100'))->toHaveCount(1);
})->group('scenario:QB-09');

it('round-trips: an exported question re-imports with its prompt, options and correct answer', function () {
    $module = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Whole Number Operations: Division']);
    PracticeQuestion::factory()->create([
        'module_id' => $module->id,
        'difficulty' => 3,
        'prompt' => 'What is 12 ÷ 4?',
        'options' => ['3', '2', '4', '6'],
        'correct_index' => 0,
        'explanation' => 'Twelve shared into four groups.',
    ]);

    $xml = app(MoodleQuestionExporter::class)->export();

    // Wipe the bank, then re-import the export.
    PracticeQuestion::query()->delete();
    $report = app(MoodleQuestionImporter::class)->import($xml, dryRun: false);

    expect($report->created)->toBe(1)->and($report->skippedCount())->toBe(0);

    $restored = PracticeQuestion::firstOrFail();
    expect($restored->module_id)->toBe($module->id)
        ->and($restored->difficulty)->toBe(3)          // rung survived the D-level round-trip
        ->and($restored->options)->toBe(['3', '2', '4', '6'])
        ->and($restored->correct_index)->toBe(0)
        ->and($restored->prompt)->toContain('12 ÷ 4');
})->group('scenario:QB-10');
