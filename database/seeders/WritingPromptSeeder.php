<?php

namespace Database\Seeders;

use App\Models\WritingPrompt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds a writing prompt for the current study week so the Writer's Log has
 * something to show. Idempotent — keyed on the Monday-anchored week start.
 */
class WritingPromptSeeder extends Seeder
{
    public function run(): void
    {
        WritingPrompt::updateOrCreate(
            ['week_start_date' => Carbon::now()->startOfWeek()->toDateString()],
            [
                'title' => 'The Mystery Door',
                'prompt' => 'One rainy afternoon you find a small door at the back of your'
                    .' classroom cupboard that you have never seen before. Write a story about'
                    .' what happens when you open it.',
                'type' => 'narrative',
            ],
        );
    }
}
