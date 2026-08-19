<?php

namespace Database\Seeders;

use App\Models\ReadingPassage;
use App\Models\VocabularyWord;
use Illuminate\Database\Seeder;
use Symfony\Component\Yaml\Yaml;

/**
 * Seeds the daily reading pool (DR-06) from database/data/reading_passages.yaml.
 *
 * Each passage is stored with its reading level, its comprehension questions, and
 * its vocabulary words (DV-01). word_count is derived from the body so the ritual
 * can be paced (DR-10). Idempotent: keyed on title, so re-running upserts the
 * passage and refreshes its vocabulary rather than duplicating.
 *
 * Depends on nothing but the reading_passages + vocabulary_words tables.
 */
class ReadingPassageSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/reading_passages.yaml');

        if (! is_file($path)) {
            $this->command?->error("Reading passage file not found: {$path}");

            return;
        }

        $passages = Yaml::parseFile($path)['passages'] ?? [];

        foreach ($passages as $entry) {
            $body = (string) $entry['body'];

            $passage = ReadingPassage::updateOrCreate(
                ['title' => $entry['title']],
                [
                    'body' => $body,
                    'reading_level' => (int) $entry['reading_level'],
                    'word_count' => str_word_count($body),
                    'questions' => $entry['questions'] ?? [],
                    'is_active' => $entry['is_active'] ?? true,
                ],
            );

            // Refresh this passage's vocabulary so re-seeding does not duplicate.
            $passage->vocabularyWords()->delete();
            foreach ($entry['vocabulary'] ?? [] as $word) {
                VocabularyWord::create([
                    'passage_id' => $passage->id,
                    'word' => $word['word'],
                    'definition' => $word['definition'],
                    'context_sentence' => $word['context_sentence'],
                ]);
            }
        }
    }
}
