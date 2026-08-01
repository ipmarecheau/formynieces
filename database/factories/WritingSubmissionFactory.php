<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WritingPrompt;
use App\Models\WritingSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WritingSubmission>
 */
class WritingSubmissionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory(),
            'writing_prompt_id' => WritingPrompt::factory(),
            'body' => $this->faker->paragraphs(3, true),
            'status' => WritingSubmission::STATUS_PENDING,
        ];
    }

    /** A fully-scored submission with a rubric profile and warm feedback. */
    public function scored(): static
    {
        return $this->state(fn () => [
            'status' => WritingSubmission::STATUS_SCORED,
            'content_score' => 7,
            'language_score' => 7,
            'grammar_score' => 8,
            'organisation_score' => 6,
            'did_well' => ['A strong, clear opening', 'Great descriptive words'],
            'try_next' => 'Try varying your sentence lengths for rhythm.',
            'scored_at' => now(),
        ]);
    }
}
