<?php

namespace Database\Factories;

use App\Models\CoParent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoParent>
 */
class CoParentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'guardian_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'relationship' => fake()->randomElement(['Father', 'Mother', 'Aunt', 'Uncle', 'Guardian']),
            'status' => 'invited',
            'invited_at' => now(),
        ];
    }
}
