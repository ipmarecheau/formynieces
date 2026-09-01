<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $issuedAt = fake()->dateTimeBetween('-6 months', 'now');
        $periodStart = (clone $issuedAt);

        return [
            'user_id' => User::factory(),
            'number' => 'INV-'.fake()->unique()->numberBetween(10000, 99999),
            'amount_cents' => 900,
            'currency' => 'USD',
            'status' => 'paid',
            'period_start' => $periodStart,
            'period_end' => (clone $periodStart)->modify('+1 month'),
            'issued_at' => $issuedAt,
            'due_at' => $issuedAt,
            'paid_at' => $issuedAt,
        ];
    }

    public function due(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'due',
            'paid_at' => null,
        ]);
    }
}
