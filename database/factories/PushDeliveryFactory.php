<?php

namespace Database\Factories;

use App\Models\PushDelivery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PushDelivery>
 */
class PushDeliveryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'essay.feedback',
            'criticality' => 'standard',
            'platform' => 'ios',
            'outcome' => PushDelivery::OUTCOME_DELIVERED,
            'reason' => null,
            'detail' => null,
        ];
    }
}
