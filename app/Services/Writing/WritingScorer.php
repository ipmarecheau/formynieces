<?php

declare(strict_types=1);

namespace App\Services\Writing;

use App\Models\WritingSubmission;
use App\Services\LlmService;

/**
 * Scores a writing submission against the four SEA criteria and returns warm,
 * child-facing feedback — two things done well and one thing to try next time.
 *
 * Never produces a grade or a pass/fail. When the AI provider is unavailable it
 * throws {@see WritingScoringUnavailable} so the caller can queue the work (WR-03)
 * rather than persist an empty rubric.
 */
final class WritingScorer
{
    public function __construct(private LlmService $llm) {}

    /**
     * @return array{content_score:int, language_score:int, grammar_score:int, organisation_score:int, did_well:array<int, string>, try_next:string}
     *
     * @throws WritingScoringUnavailable
     */
    public function score(WritingSubmission $submission): array
    {
        $prompt = $submission->prompt;

        $system = <<<'PROMPT'
            You are a warm, encouraging primary-school English teacher in Trinidad and
            Tobago, giving a 10-to-11-year-old feedback on SEA writing practice. Score
            each of the four criteria out of 10, then name exactly TWO specific things
            she did well and ONE specific thing to try next time. Be kind and concrete.
            Never give a letter grade, a total, or a pass/fail. Address the child warmly.
            Return exactly this JSON structure and nothing else:
            {
              "content_score": 7,
              "language_score": 7,
              "grammar_score": 8,
              "organisation_score": 6,
              "did_well": ["first thing she did well", "second thing she did well"],
              "try_next": "one specific thing to try next time"
            }
            PROMPT;

        $user = "Writing type: {$prompt->type}.\nPrompt: {$prompt->prompt}.\nHer writing:\n{$submission->body}";

        // Essay grading is ESSENTIAL — runs to the hard ceiling (AG-02).
        $result = $this->llm->completeJson($system, $user, 700, $submission->student_id, essential: true);

        if (! $this->isComplete($result)) {
            throw new WritingScoringUnavailable('The writing scorer returned no usable rubric.');
        }

        return [
            'content_score' => (int) $result['content_score'],
            'language_score' => (int) $result['language_score'],
            'grammar_score' => (int) $result['grammar_score'],
            'organisation_score' => (int) $result['organisation_score'],
            'did_well' => array_values(array_slice($result['did_well'], 0, 2)),
            'try_next' => (string) $result['try_next'],
        ];
    }

    /**
     * A response is only usable when every rubric key is present and the two
     * did-well notes came through — an empty array (provider outage) or a partial
     * object counts as unavailable.
     *
     * @param  array<string, mixed>  $result
     */
    private function isComplete(array $result): bool
    {
        foreach (['content_score', 'language_score', 'grammar_score', 'organisation_score', 'did_well', 'try_next'] as $key) {
            if (! array_key_exists($key, $result)) {
                return false;
            }
        }

        return is_array($result['did_well']) && count($result['did_well']) >= 2;
    }
}
