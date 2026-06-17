<?php

namespace Database\Factories;

use App\Models\SyllabusModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SyllabusModule>
 */
class SyllabusModuleFactory extends Factory
{
    protected $model = SyllabusModule::class;

    public function definition(): array
    {
        return [
            'subject'        => 'Math',
            'topic'          => 'Number Concepts: Place Value up to One Million',
            'sea_section'    => 'Number Concepts',
            'sequence_order' => 1,
            'pacing_week'    => 1,
            'description'    => 'Test module.',
            'resources'      => null,
        ];
    }
}