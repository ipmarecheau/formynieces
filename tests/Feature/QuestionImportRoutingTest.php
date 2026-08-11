<?php

use App\Models\PracticeQuestion;
use App\Models\WritingBankPrompt;
use App\Services\QuestionBank\QuestionImportCoordinator;
use Database\Seeders\SyllabusModuleSeeder;

beforeEach(fn () => $this->seed(SyllabusModuleSeeder::class));

/** A single Moodle export mixing an essay prompt with a multichoice question. */
function mixedImportXml(): string
{
    return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<quiz>
  <question type="essay">
    <name><text>M90 · D1 · v01 — Narrative: Story Including a Given Line</text></name>
    <questiontext format="html"><text><![CDATA[<p>Write a story.</p>]]></text></questiontext>
    <defaultgrade>20</defaultgrade>
    <graderinfo format="html"><text><![CDATA[<!-- RUBRIC_JSON: {"genre":"Narrative","criteria":[{"criterion":"Content","max":8}]} -->]]></text></graderinfo>
  </question>
  <question type="multichoice">
    <name><text>M32 · D1 · v01 — Geometry: Geometric Patterns</text></name>
    <questiontext format="html"><text><![CDATA[<p>How many squares in row 5?</p>]]></text></questiontext>
    <generalfeedback format="html"><text><![CDATA[<p>Five.</p>]]></text></generalfeedback>
    <answer fraction="100" format="html"><text><![CDATA[5]]></text></answer>
    <answer fraction="0" format="html"><text><![CDATA[4]]></text></answer>
    <answer fraction="0" format="html"><text><![CDATA[6]]></text></answer>
    <answer fraction="0" format="html"><text><![CDATA[7]]></text></answer>
  </question>
</quiz>
XML;
}

it('routes essays to the writing bank and multichoice to the practice bank from one file', function () {
    $report = app(QuestionImportCoordinator::class)->import(mixedImportXml(), dryRun: false);

    // Both banks populated from the single file.
    expect(WritingBankPrompt::count())->toBe(1);
    expect(PracticeQuestion::where('source_ref', 'M32 · D1 · v01 — Geometry: Geometric Patterns')->exists())->toBeTrue();

    $essay = WritingBankPrompt::first();
    expect($essay->genre)->toBe('narrative');
    expect($essay->module_id)->toBe(69);

    $mcq = PracticeQuestion::where('source_ref', 'M32 · D1 · v01 — Geometry: Geometric Patterns')->first();
    expect($mcq->module_id)->toBe(32);

    // Merged report counts both created; no cross-type skips.
    expect($report->created)->toBe(2);
    expect($report->skipped)->toBeEmpty();
})->group('scenario:WB-02');

it('a dry run routes and counts both types without persisting either', function () {
    $report = app(QuestionImportCoordinator::class)->import(mixedImportXml(), dryRun: true);

    expect($report->parsed)->toBe(2);
    expect($report->created)->toBe(2);
    expect(WritingBankPrompt::count())->toBe(0);
    expect(PracticeQuestion::count())->toBe(0);
})->group('scenario:WB-02');
