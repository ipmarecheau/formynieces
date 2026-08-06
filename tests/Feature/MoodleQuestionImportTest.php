<?php

use App\Models\PracticeQuestion;
use App\Services\QuestionBank\MoodleQuestionImporter;
use Database\Seeders\SyllabusModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function sampleMoodleXml(): string
{
    return file_get_contents(base_path('tests/Fixtures/moodle_sample.xml'));
}

beforeEach(function () {
    $this->seed(SyllabusModuleSeeder::class);
    Storage::fake('public');
});

it('dry-runs without writing questions or images', function () {
    $report = app(MoodleQuestionImporter::class)->import(sampleMoodleXml(), dryRun: true);

    expect($report->parsed)->toBe(4)          // 4 multichoice (truefalse is not "parsed")
        ->and($report->created)->toBe(3)      // Q01 D1, Q01 D5, Q04 D3
        ->and($report->updated)->toBe(0)
        ->and($report->skippedCount())->toBe(2); // Q99 unmapped + the truefalse

    expect(PracticeQuestion::count())->toBe(0);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
})->group('scenario:QB-01');

it('imports valid questions with the right module, rung, options and correct index', function () {
    app(MoodleQuestionImporter::class)->import(sampleMoodleXml(), dryRun: false);

    expect(PracticeQuestion::count())->toBe(3);

    $d1 = PracticeQuestion::where('source_ref', 'Q01 · D1 · v1 — Addition')->firstOrFail();
    expect($d1->module_id)->toBe(6)                 // Q01 -> Whole Number Operations: Addition
        ->and($d1->subject)->toBe('Math')
        ->and($d1->difficulty)->toBe(1)             // D1 -> rung 1
        ->and($d1->options)->toBe(['5', '4', '6', '7'])
        ->and($d1->correct_index)->toBe(0)
        ->and($d1->is_active)->toBeTrue()
        ->and($d1->explanation)->toContain('5');

    // D5 collapses to the hard rung.
    expect(PracticeQuestion::where('source_ref', 'Q01 · D5 · v1 — Addition')->value('difficulty'))->toBe(3);

    // D3 (SEA standard) is the medium rung and maps to the Fractions module.
    $frac = PracticeQuestion::where('source_ref', 'Q04 · D3 · v1 — Fraction shaded')->firstOrFail();
    expect($frac->difficulty)->toBe(2)->and($frac->module_id)->toBe(12);
})->group('scenario:QB-02');

it('extracts an embedded figure and rewrites the @@PLUGINFILE@@ reference to a real URL', function () {
    app(MoodleQuestionImporter::class)->import(sampleMoodleXml(), dryRun: false);

    $frac = PracticeQuestion::where('source_ref', 'Q04 · D3 · v1 — Fraction shaded')->firstOrFail();

    expect($frac->prompt)->not->toContain('@@PLUGINFILE@@')
        ->and($frac->prompt)->toContain('/storage/question-media/');

    // The decoded PNG landed on the public disk.
    expect(Storage::disk('public')->allFiles())
        ->toHaveCount(1)
        ->each->toStartWith('question-media/');
})->group('scenario:QB-04');

it('skips unmapped skills and unsupported question types with reasons', function () {
    $report = app(MoodleQuestionImporter::class)->import(sampleMoodleXml(), dryRun: true);

    $reasons = collect($report->skipped);
    expect($reasons->firstWhere('ref', 'Q99 · D1 · v1 — Mystery skill')['reason'])->toContain('No syllabus module');
    expect($reasons->pluck('reason')->implode(' '))->toContain("Unsupported question type 'truefalse'");
})->group('scenario:QB-05');

it('is idempotent — re-importing the same file updates rather than duplicates', function () {
    $importer = app(MoodleQuestionImporter::class);
    $importer->import(sampleMoodleXml(), dryRun: false);
    $second = $importer->import(sampleMoodleXml(), dryRun: false);

    expect(PracticeQuestion::count())->toBe(3)      // no duplicates
        ->and($second->created)->toBe(0)
        ->and($second->updated)->toBe(3);
})->group('scenario:QB-03');
