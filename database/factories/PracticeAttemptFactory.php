<?php

namespace Database\Factories;

use App\Models\PracticeAttempt;
use App\Models\PracticeQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PracticeAttempt>
 */
class PracticeAttemptFactory extends Factory
{
    protected $model = PracticeAttempt::class;

    public function definition(): array
    {
        return [
            'student_id'           => User::factory(),
            'practice_question_id' => PracticeQuestion::factory(),
            'module_id'            => 1,
            'difficulty'           => 1,
            'is_correct'           => true,
        ];
    }
}