<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'whatsapp' => null,
            'child_name' => fake()->firstName(),
            'child_level' => fake()->randomElement(['Standard 3', 'Standard 4', 'Standard 5']),
            'source' => 'placement-report',
            'weekly_opt_in' => false,
        ];
    }

    /** A lead with a completed mock + report. */
    public function withReport(): static
    {
        return $this->state(fn () => [
            'mock_score' => fake()->numberBetween(40, 92),
            'placement_band' => fake()->randomElement(['On track for first choice', 'Within reach', 'Needs a catch-up push']),
            'weakest_strands' => ['Fractions', 'Reading Comprehension', 'Spelling'],
            'next_step' => 'Start with Reading Comprehension: Main Idea.',
        ]);
    }
}
