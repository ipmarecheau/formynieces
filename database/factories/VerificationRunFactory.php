<?php

namespace Database\Factories;

use App\Models\VerificationRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VerificationRun>
 */
class VerificationRunFactory extends Factory
{
    protected $model = VerificationRun::class;

    public function definition(): array
    {
        return [
            'tester_name' => $this->faker->firstName(),
            'feature' => 'HC',
            'scenario_id' => 'HC-02',
            'result' => VerificationRun::RESULT_WORKED,
            'notes' => null,
            'screenshot_path' => null,
        ];
    }

    public function issue(string $notes = 'Something looked off'): static
    {
        return $this->state(fn () => [
            'result' => VerificationRun::RESULT_ISSUE,
            'notes' => $notes,
        ]);
    }

    /**
     * A deferred check awaiting its expected notification, with the reminder windows open
     * around $expectedAt (defaults to one hour out, so no reminder is due yet).
     */
    public function waiting(?\DateTimeInterface $expectedAt = null): static
    {
        return $this->state(fn () => [
            'feature' => 'PN',
            'scenario_id' => 'PN-01',
            'result' => VerificationRun::RESULT_WAITING,
            'tester_timezone' => 'Europe/London',
            'expected_at' => $expectedAt ?? now()->addHour(),
            'first_reminder_sent_at' => null,
            'second_reminder_sent_at' => null,
        ]);
    }
}
