<?php

declare(strict_types=1);

namespace App\Services\Practice;

use App\Models\PracticeQuestion;
use App\Models\User;
use App\Models\WorkedExample;
use App\Services\LlmService;
use Illuminate\Support\Str;

/**
 * Produces the step-by-step worked example shown in the tutorial stage.
 *
 * Cached one-per-question: the first student to reach a question triggers an LLM
 * generation (Smooth's voice, tuned to what the child finds tricky); it is stored
 * and reused for every later student — so the LLM cost is paid once per question.
 * If the LLM is unavailable, it degrades to the question's own bank explanation, so
 * the tutorial always has something to show.
 */
class WorkedExampleGenerator
{
    public function __construct(private LlmService $llm) {}

    public function forQuestion(PracticeQuestion $question, ?User $student = null): WorkedExample
    {
        $cached = WorkedExample::where('practice_question_id', $question->id)->first();
        if ($cached !== null) {
            return $cached;
        }

        [$steps, $source] = $this->generate($question, $student);

        return WorkedExample::create([
            'practice_question_id' => $question->id,
            'steps' => $steps,
            'source' => $source,
        ]);
    }

    /**
     * @return array{0: list<string>, 1: string} [steps, source]
     */
    private function generate(PracticeQuestion $question, ?User $student): array
    {
        $prompt = $this->plain($question->prompt);
        $answer = $question->options[$question->correct_index] ?? '';

        // No LLM key configured → skip the call and use the bank explanation.
        if ((string) config('services.llm.key') !== '') {
            $weak = $this->weakAreas($student);
            $system = 'You are Smooth, a warm sea-turtle tutor for an 11-year-old preparing for the '
                .'Trinidad & Tobago SEA exam. Write a SHORT step-by-step worked solution a child can '
                .'follow: 3 to 6 steps, one per line, simple encouraging language, ending with the answer. '
                .'No preamble, no numbering, no markdown.';
            $user = "Question: {$prompt}\nAnswer: {$answer}".
                ($weak !== '' ? "\nThe student finds these tricky: {$weak}." : '');

            // Worked-example generation is DISCRETIONARY — held to the soft cap (AG-02).
            $raw = $this->llm->complete($system, $user, 400, $student?->id, essential: false);
            $steps = $this->toSteps($raw);
            if ($steps !== [] && ! $this->looksLikeFallback($raw)) {
                return [$steps, 'llm'];
            }
        }

        return [$this->explanationSteps($question), 'explanation'];
    }

    /** Split an LLM response (one step per line) into clean step strings. */
    private function toSteps(string $raw): array
    {
        return collect(preg_split('/\r?\n/', trim($raw)) ?: [])
            ->map(fn ($line) => trim(preg_replace('/^\s*\d+[.)]\s*/', '', $line) ?? $line))
            ->filter(fn ($line) => $line !== '')
            ->values()
            ->all();
    }

    /** Fallback steps from the question's own bank explanation, split into sentences. */
    private function explanationSteps(PracticeQuestion $question): array
    {
        $text = $this->plain((string) $question->explanation);
        $answer = $question->options[$question->correct_index] ?? '';

        $sentences = collect(preg_split('/(?<=[.!?])\s+/', $text) ?: [])
            ->map(fn ($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        if ($sentences === []) {
            $sentences = ["Let's look at this one together."];
        }
        if ($answer !== '') {
            $sentences[] = "The answer is {$answer}.";
        }

        return $sentences;
    }

    private function weakAreas(?User $student): string
    {
        $areas = $student?->known_weak_areas;

        return is_array($areas) ? implode(', ', array_slice($areas, 0, 3)) : '';
    }

    private function plain(string $html): string
    {
        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function looksLikeFallback(string $raw): bool
    {
        return Str::contains($raw, 'unable to generate a response');
    }
}
