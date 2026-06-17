<?php

namespace Database\Factories;

use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PracticeQuestion>
 */
class PracticeQuestionFactory extends Factory
{
    protected $model = PracticeQuestion::class;

    public function definition(): array
    {
        return [
            'module_id'      => SyllabusModule::factory(),
            'subject'        => 'Math',
            'sea_section'    => 'Number Concepts',
            'strand'         => null,
            'difficulty'     => 1,
            'sequence_order' => null,
            'prompt'         => 'What is 2 + 2?',
            'options'        => ['3', '4', '5', '6'],
            'correct_index'  => 1,
            'hint'           => 'Count up from two.',
            'explanation'    => 'Two plus two makes four.',
            'is_active'      => true,
        ];
    }
}