<?php

use App\Models\ReadingPassage;
use App\Models\VocabularyWord;
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

it('is idempotent — re-seeding does not duplicate a passage or its vocabulary', function () {
    $this->seed(ReadingPassageSeeder::class);
    $countAfterFirst = ReadingPassage::count();
    $vocabAfterFirst = VocabularyWord::count();

    $this->seed(ReadingPassageSeeder::class);

    expect(ReadingPassage::count())->toBe($countAfterFirst)
        ->and(VocabularyWord::count())->toBe($vocabAfterFirst);
});
