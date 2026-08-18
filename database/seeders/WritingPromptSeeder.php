<?php

namespace Database\Seeders;

use App\Models\WritingPrompt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Symfony\Component\Yaml\Yaml;

/**
 * Seeds the writing-prompt bank (WR-01/06) from database/data/writing_prompts.yaml.
 *
 * One shared prompt per Monday-anchored study week: the prompts are assigned to
 * consecutive weeks starting from the current week's Monday, so the Writer's Log
 * always has this week's prompt and a term's worth queued ahead. Idempotent —
 * keyed on week_start_date, so re-running does not duplicate.
 */
class WritingPromptSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/writing_prompts.yaml');

        if (! is_file($path)) {
            $this->command?->error("Writing prompt bank file not found: {$path}");

            return;
        }

        $prompts = Yaml::parseFile($path)['prompts'] ?? [];
        $weekStart = Carbon::now()->startOfWeek();

        foreach ($prompts as $i => $prompt) {
            WritingPrompt::updateOrCreate(
                ['week_start_date' => $weekStart->copy()->addWeeks($i)->toDateString()],
                [
                    'title' => $prompt['title'],
                    'prompt' => $prompt['prompt'],
                    'type' => $prompt['type'],
                ],
            );
        }
    }
}
