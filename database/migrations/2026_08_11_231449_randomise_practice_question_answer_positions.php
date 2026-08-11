<?php

use App\Models\PracticeQuestion;
use App\Support\OptionShuffler;
use Illuminate\Database\Migrations\Migration;

/**
 * QB-16: the existing bank was authored with the correct answer always first
 * (correct_index 0). Redistribute each question's options so the correct answer
 * lands at a spread of positions. Seeded by source_ref so it is deterministic and
 * re-runnable. Not reversible in a meaningful way (the original order is lost, but
 * correctness is preserved), so down() is a no-op.
 */
return new class extends Migration
{
    public function up(): void
    {
        PracticeQuestion::query()
            ->select(['id', 'options', 'correct_index', 'source_ref'])
            ->chunkById(500, function ($questions) {
                foreach ($questions as $q) {
                    $options = $q->options;
                    if (! is_array($options) || count($options) < 2) {
                        continue;
                    }
                    $seed = $q->source_ref ?: ('id-'.$q->id);
                    $shuffled = OptionShuffler::shuffle($options, (int) $q->correct_index, seed: $seed);

                    $q->options = $shuffled['options'];
                    $q->correct_index = $shuffled['correct_index'];
                    $q->save();
                }
            });
    }

    public function down(): void
    {
        // Irreversible: the original ordering is not retained. Correctness is intact.
    }
};
