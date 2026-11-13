<?php

namespace Database\Factories;

use App\Models\PersonaFinding;
use App\Models\PersonaRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonaFinding>
 */
class PersonaFindingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'persona_run_id' => PersonaRun::factory(),
            'severity' => fake()->randomElement(['low', 'medium', 'high']),
            'area' => fake()->randomElement(['onboarding', 'voyage', 'writing', 'billing']),
            'observation' => fake()->sentence(),
            'suggestion' => fake()->sentence(),
            'admin_verdict' => null,
            'admin_notes' => null,
        ];
    }
}
