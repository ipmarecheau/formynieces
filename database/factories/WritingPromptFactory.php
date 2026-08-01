<?php

namespace Database\Factories;

use App\Models\WritingPrompt;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<WritingPrompt>
 */
class WritingPromptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'week_start_date' => now()->startOfWeek()->toDateString(),
            'title' => 'The Mystery Door',
            'prompt' => 'Write a story about a door that should not have been opened.',
            'type' => 'narrative',
        ];
    }

    /** A prompt anchored to the study week containing $date. */
    public function forWeek(Carbon $date): static
    {
        return $this->state(fn () => [
            'week_start_date' => $date->copy()->startOfWeek()->toDateString(),
        ]);
    }
}
