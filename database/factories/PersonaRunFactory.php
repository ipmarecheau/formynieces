<?php

namespace Database\Factories;

use App\Models\PersonaRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonaRun>
 */
class PersonaRunFactory extends Factory
{
    public function definition(): array
    {
        return [
            'persona' => fake()->randomElement([PersonaRun::PERSONA_ANXIOUS_PARENT, PersonaRun::PERSONA_CHILD_FULL_DAY]),
            'status' => PersonaRun::STATUS_COMPLETED,
            'summary' => fake()->sentence(),
            'cost_usd' => fake()->randomFloat(4, 0.01, 0.20),
            'started_at' => now()->subMinutes(5),
            'finished_at' => now(),
        ];
    }
}
