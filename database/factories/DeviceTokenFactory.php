<?php

namespace Database\Factories;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceToken>
 */
class DeviceTokenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'platform' => fake()->randomElement(['ios', 'android']),
            'token' => fake()->sha256(),
            'last_seen_at' => now(),
        ];
    }

    public function ios(): static
    {
        return $this->state(fn () => ['platform' => 'ios']);
    }

    public function android(): static
    {
        return $this->state(fn () => ['platform' => 'android']);
    }
}
