<?php

use App\Models\WritingBankPrompt;
use App\Services\QuestionBank\WritingPromptImporter;
use Database\Seeders\SyllabusModuleSeeder;

// Writing prompts key onto syllabus modules 69 (Narrative) / 70 (Report).
beforeEach(fn () => $this->seed(SyllabusModuleSeeder::class));

/**
 * Builds a minimal Moodle XML export mixing essay writing prompts with one
 * multichoice question, mirroring the real SEA writing bank shape.
 */
function wbSampleXml(): string
{
    return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<quiz>
  <question type="category">
    <category><text>$course$/top/SEA/M90 Narrative: Story Including a Given Line</text></category>
  </question>
  <question type="essay">
    <name><text>M90 · D1 · v01 — Narrative: Story Including a Given Line</text></name>
    <questiontext format="html"><text><![CDATA[<p>Write a story which includes this line: <strong>"I opened the box."</strong></p>]]></text></questiontext>
    <generalfeedback format="html"><text><![CDATA[This is a writing task.]]></text></generalfeedback>
    <defaultgrade>20</defaultgrade>
    <graderinfo format="html"><text><![CDATA[<h4>Marking rubric</h4><table><tr><td>Content &amp; Ideas</td></tr></table><!-- RUBRIC_JSON: {"genre":"Narrative","total":20,"level":"D1","criteria":[{"criterion":"Content &amp; Ideas","max":8},{"criterion":"Organisation","max":5},{"criterion":"Language &amp; Vocabulary","max":5},{"criterion":"Mechanics","max":2}]} -->]]></text></graderinfo>
  </question>
  <question type="essay">
    <name><text>M93 · D5 · v01 — Report: Report on an Incident</text></name>
    <questiontext format="html"><text><![CDATA[<p>Write a report on an incident you witnessed.</p>]]></text></questiontext>
    <generalfeedback format="html"><text><![CDATA[This is a writing task.]]></text></generalfeedback>
    <defaultgrade>20</defaultgrade>
    <graderinfo format="html"><text><![CDATA[<!-- RUBRIC_JSON: {"genre":"Report","total":20,"level":"D5","criteria":[{"criterion":"Content","max":8}]} -->]]></text></graderinfo>
  </question>
  <question type="multichoice">
    <name><text>M32 · D1 · v01 — Geometry: Geometric Patterns</text></name>
    <questiontext format="html"><text><![CDATA[<p>How many?</p>]]></text></questiontext>
    <answer fraction="100" format="html"><text><![CDATA[5]]></text></answer>
    <answer fraction="0" format="html"><text><![CDATA[4]]></text></answer>
    <answer fraction="0" format="html"><text><![CDATA[6]]></text></answer>
    <answer fraction="0" format="html"><text><![CDATA[7]]></text></answer>
  </question>
</quiz>
XML;
}

it('imports essay prompts into the writing bank, keyed by genre and difficulty, with the rubric parsed', function () {
    $report = app(WritingPromptImporter::class)->import(wbSampleXml(), dryRun: false);

    // Two essays stored; the multichoice question reported and skipped.
    expect($report->created)->toBe(2);
    expect($report->skipped)->toHaveCount(1);
    expect($report->skipped[0]['reason'])->toContain('multichoice');

    $narrative = WritingBankPrompt::where('source_ref', 'M90 · D1 · v01 — Narrative: Story Including a Given Line')->first();
    expect($narrative)->not->toBeNull();
    expect($narrative->genre)->toBe('narrative');
    expect($narrative->sub_genre)->toBe('Story Including a Given Line');
    expect($narrative->difficulty)->toBe(1);            // D1 -> rung 1
    expect($narrative->module_id)->toBe(69);            // Narrative -> module 69
    expect($narrative->marks)->toBe(20);
    expect($narrative->prompt)->toContain('I opened the box');
    expect($narrative->is_active)->toBeTrue();

    // Rubric parsed from the RUBRIC_JSON block, entities decoded.
    expect($narrative->rubric)->toBeArray();
    expect($narrative->rubric['criteria'][0]['criterion'])->toBe('Content & Ideas');
    expect($narrative->rubric['criteria'][0]['max'])->toBe(8);

    $report2 = WritingBankPrompt::where('source_ref', 'M93 · D5 · v01 — Report: Report on an Incident')->first();
    expect($report2->genre)->toBe('report');
    expect($report2->difficulty)->toBe(3);             // D5 -> rung 3
    expect($report2->module_id)->toBe(70);             // Report -> module 70
})->group('scenario:WB-01');

it('re-imports idempotently — updating rather than duplicating', function () {
    $importer = app(WritingPromptImporter::class);
    $importer->import(wbSampleXml(), dryRun: false);
    $report = $importer->import(wbSampleXml(), dryRun: false);

    expect(WritingBankPrompt::count())->toBe(2);
    expect($report->created)->toBe(0);
    expect($report->updated)->toBe(2);
})->group('scenario:WB-01');

it('a dry run parses without persisting', function () {
    $report = app(WritingPromptImporter::class)->import(wbSampleXml(), dryRun: true);

    expect($report->parsed)->toBe(2);
    expect($report->created)->toBe(2);
    expect(WritingBankPrompt::count())->toBe(0);
})->group('scenario:WB-01');
