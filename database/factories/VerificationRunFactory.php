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
}
