<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'role' => 'guest',
            'kind' => 'issue',
            'message' => fake()->sentence(),
            'url' => 'https://smoothseas.org/voyage',
            'context' => ['path' => '/voyage'],
            'status' => SupportTicket::STATUS_OPEN,
        ];
    }

    public function withVoiceNote(string $path = 'support-audio/sample.webm'): static
    {
        return $this->state(fn () => ['message' => null, 'audio_path' => $path]);
    }
}
