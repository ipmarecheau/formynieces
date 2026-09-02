<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // Default test accounts to a paying plan so the full product is exercised;
            // free-tier gating specs (free_tier.feature) opt in explicitly via ->free().
            'plan' => 'premium',
        ];
    }

    /** A permanently-free (top-of-funnel) account: map + mastery quizzes only. */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => ['plan' => 'free']);
    }

    /** A one-month full-access trial account (falls back to free when it lapses). */
    public function trial(): static
    {
        return $this->state(fn (array $attributes) => ['plan' => 'trial']);
    }

    /** A paying subscriber. */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => ['plan' => 'premium']);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
