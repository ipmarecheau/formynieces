<?php

use App\Models\Lesson;
use App\Models\PracticeQuestion;
use App\Models\ReadingPassage;
use App\Models\SyllabusModule;
use App\Models\VocabularyWord;
use App\Models\WritingPrompt;
use App\Services\Content\ContentCoverageService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Infrastructure test for the content-coverage tracker (no BDD scenario — content
// is production status, not behaviour). See formynieces-spec/CONTENT_BACKLOG.md.
it('reports authored content against the seamless-operation floors', function () {
    $covered = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);
    $bare = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 2]);

    // Lesson only for the covered module.
    Lesson::create(['module_id' => $covered->id, 'title' => 'Rounding', 'blocks' => [], 'is_published' => true]);

    // Practice: covered clears the masterable floor (≥3 at each rung); bare does not.
    foreach (ContentCoverageService::RUNGS as $rung) {
        PracticeQuestion::factory()->count(3)->create([
            'module_id' => $covered->id, 'difficulty' => $rung, 'is_active' => true,
        ]);
    }
    PracticeQuestion::factory()->create(['module_id' => $bare->id, 'difficulty' => 1, 'is_active' => true]);

    // Reading level 5 with a passage yielding 3 vocab words.
    $passage = ReadingPassage::create([
        'title' => 'The Reef', 'body' => 'body', 'reading_level' => 5,
        'word_count' => 100, 'questions' => [], 'is_active' => true,
    ]);
    foreach (['coral', 'tide', 'shoal'] as $w) {
        VocabularyWord::create(['passage_id' => $passage->id, 'word' => $w, 'definition' => 'd', 'context_sentence' => 'c']);
    }

    WritingPrompt::create([
        'week_start_date' => now()->startOfWeek()->toDateString(),
        'title' => 'A market morning', 'prompt' => 'Write.', 'type' => 'narrative',
    ]);

    $report = app(ContentCoverageService::class)->report();

    // Lessons — 1 of 2 modules.
    expect($report['lessons']['have'])->toBe(1)
        ->and($report['lessons']['need'])->toBe(2)
        ->and($report['lessons']['missing'])->toHaveCount(1)
        ->and($report['lessons']['missing'][0]['code'])->toBe($bare->code);

    // Practice — only the covered module is masterable; the bare one is under-stocked.
    expect($report['practice']['masterable'])->toBe(1)
        ->and($report['practice']['understocked'])->toHaveCount(1)
        ->and($report['practice']['understocked'][0]['rungs'])->toBe([1 => 1, 3 => 0, 5 => 0]);

    // Reading — level 5 has one passage, other expected levels are empty.
    expect($report['reading']['per_level'][5]['have'])->toBe(1)
        ->and($report['reading']['per_level'][3]['have'])->toBe(0)
        ->and($report['reading']['per_level'][5]['need'])->toBe(ContentCoverageService::PASSAGES_PER_LEVEL - 1);

    // Vocabulary + writing.
    expect($report['vocabulary']['words'])->toBe(3)
        ->and($report['writing']['have'])->toBe(1)
        ->and($report['writing']['missing_genres'])->toContain('expository');
})->group('content-coverage');
