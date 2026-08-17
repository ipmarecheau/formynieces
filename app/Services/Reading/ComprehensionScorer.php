<?php

namespace App\Services\Reading;

use App\Models\ReadingPassage;
use App\Services\LlmService;

/**
 * ComprehensionScorer — scores a reading session (DR-07).
 *
 * LLM-first: the LLM weighs both the multiple-choice answers AND the written
 * response and returns an encouraging summary. Baseline fallback: a deterministic
 * multiple-choice auto-grade with no summary. completeJson() already returns []
 * on any failure (budget block, outage, bad JSON), so a missing/invalid score
 * simply falls through to the baseline — the child always gets a working score.
 */
class ComprehensionScorer
{
    public function __construct(private LlmService $llm) {}

    /**
     * @param  array<int,array<string,mixed>>  $questions
     * @param  array<int,mixed>  $answers
     * @return array{score:int, feedback:?string}
     */
    public function score(ReadingPassage $passage, array $questions, array $answers, ?int $studentId = null): array
    {
        $baseline = $this->mcScore($questions, $answers);

        $result = $this->llm->completeJson(
            $this->systemPrompt(),
            $this->userPrompt($passage, $questions, $answers),
            400,
            $studentId,
            essential: false,
        );

        if (isset($result['score']) && is_numeric($result['score'])) {
            return [
                'score' => max(0, min(100, (int) round($result['score']))),
                'feedback' => isset($result['feedback']) ? trim((string) $result['feedback']) : null,
            ];
        }

        return ['score' => $baseline, 'feedback' => null];
    }

    /**
     * The deterministic baseline: percentage of multiple-choice questions correct.
     * Written responses are practice, never scored (DR-03).
     *
     * @param  array<int,array<string,mixed>>  $questions
     * @param  array<int,mixed>  $answers
     */
    public function mcScore(array $questions, array $answers): int
    {
        $gradable = 0;
        $correct = 0;
        foreach ($questions as $i => $question) {
            if (($question['type'] ?? 'mc') !== 'mc') {
                continue;
            }
            $gradable++;
            if (isset($answers[$i]) && (int) $answers[$i] === (int) ($question['correct_index'] ?? -1)) {
                $correct++;
            }
        }

        return $gradable > 0 ? (int) round($correct / $gradable * 100) : 0;
    }

    private function systemPrompt(): string
    {
        return 'You are a warm primary-school reading teacher marking a Standard 5 (age ~10) reading '
            .'comprehension. Weigh both the multiple-choice answers and the short written response. '
            .'Return JSON only: {"score": <integer 0-100>, "feedback": "<one or two warm, specific, '
            .'encouraging sentences; never harsh, never a grade letter>"}.';
    }

    /**
     * @param  array<int,array<string,mixed>>  $questions
     * @param  array<int,mixed>  $answers
     */
    private function userPrompt(ReadingPassage $passage, array $questions, array $answers): string
    {
        $lines = ["PASSAGE: {$passage->title}", $passage->body, '', 'QUESTIONS AND HER ANSWERS:'];

        foreach ($questions as $i => $question) {
            $prompt = $question['prompt'] ?? '';
            if (($question['type'] ?? 'mc') === 'mc') {
                $her = isset($answers[$i]) ? ($question['options'][$answers[$i]] ?? '(blank)') : '(blank)';
                $best = isset($question['correct_index']) ? ($question['options'][$question['correct_index']] ?? '') : '';
                $lines[] = "- {$prompt} | her answer: {$her} | correct: {$best}";
            } else {
                $her = $answers[$i] ?? '(blank)';
                $lines[] = "- {$prompt} | her written answer: {$her}";
            }
        }

        return implode("\n", $lines);
    }
}
