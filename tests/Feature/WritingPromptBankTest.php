<?php

use App\Models\WritingPrompt;
use Database\Seeders\WritingPromptSeeder;
use Illuminate\Support\Carbon;

/**
 * The writing-prompt bank stocks a term of Monday-anchored prompts across the four
 * SEA genres, so the Writer's Log always has this week's prompt (WR-01/06).
 */
it('seeds a term of writing prompts spanning all four genres, current week covered', function () {
    $this->seed(WritingPromptSeeder::class);

    expect(WritingPrompt::count())->toBe(30)
        ->and(WritingPrompt::distinct()->pluck('type')->sort()->values()->all())
        ->toBe(['descriptive', 'expository', 'narrative', 'persuasive'])
        // WR-01: this Monday-anchored week has a prompt to serve.
        ->and(WritingPrompt::forWeek(Carbon::now()))->not->toBeNull();
});

it('is idempotent — re-seeding does not duplicate a week', function () {
    $this->seed(WritingPromptSeeder::class);
    $this->seed(WritingPromptSeeder::class);

    expect(WritingPrompt::count())->toBe(30);
});
