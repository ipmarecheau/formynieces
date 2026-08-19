<?php

use App\Models\ReadingPassage;
use App\Models\VocabularyWord;
use App\Services\Content\ContentCoverageService;
use Database\Seeders\ReadingPassageSeeder;

/**
 * The reading pool seeds level-keyed passages, each with comprehension questions
 * and at least three vocabulary words drawn from it (DR-06 / DV-01).
 */
it('seeds passages with questions and vocabulary, keyed by reading level', function () {
    $this->seed(ReadingPassageSeeder::class);

    $passages = ReadingPassage::all();
    expect($passages)->not->toBeEmpty();

    foreach ($passages as $passage) {
        expect($passage->reading_level)->toBeGreaterThanOrEqual(3)->toBeLessThanOrEqual(7)
            ->and($passage->word_count)->toBeGreaterThan(0)
            ->and(count($passage->questions ?? []))->toBeGreaterThanOrEqual(1)
            // DV-01: each passage yields at least three vocabulary words.
            ->and($passage->vocabularyWords()->count())->toBeGreaterThanOrEqual(3);
    }
});

/**
 * The seamless-offline bar: every expected reading level is fully stocked with the
 * target number of active, unseen passages so a term never repeats one (DR-06 / LL-18).
 */
it('stocks the target number of active passages for every expected reading level', function () {
    $this->seed(ReadingPassageSeeder::class);

    foreach (ContentCoverageService::READING_LEVELS as $level) {
        expect(ReadingPassage::where('reading_level', $level)->where('is_active', true)->count())
            ->toBe(ContentCoverageService::PASSAGES_PER_LEVEL, "level {$level} is not fully stocked");
    }
});

it('has no duplicate passage titles across the pool', function () {
    $this->seed(ReadingPassageSeeder::class);

    $titles = ReadingPassage::pluck('title');

    expect($titles->count())->toBe($titles->unique()->count());
});

it('is idempotent — re-seeding does not duplicate a passage or its vocabulary', function () {
    $this->seed(ReadingPassageSeeder::class);
    $countAfterFirst = ReadingPassage::count();
    $vocabAfterFirst = VocabularyWord::count();

    $this->seed(ReadingPassageSeeder::class);

    expect(ReadingPassage::count())->toBe($countAfterFirst)
        ->and(VocabularyWord::count())->toBe($vocabAfterFirst);
});
