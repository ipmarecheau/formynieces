<?php

namespace App\Services\SchoolJournal;

use App\Models\SyllabusModule;

/**
 * SJ-11 — aligns what the vision model read ("Plurals — consonant + y") with a
 * real syllabus module, so classroom evidence speaks the same language as her
 * voyage. Confidence-gated: a weak match is flagged, never guessed.
 */
class TopicMatcher
{
    /** Minimum similarity (0-1) for a fuzzy label match. */
    private const MATCH_THRESHOLD = 0.72;

    /**
     * Match a question's topic to a syllabus module. Prefers the model's module
     * code when valid; falls back to fuzzy label matching within the subject.
     *
     * @return array{module: ?SyllabusModule, confidence: float}
     */
    public function match(?string $topicLabel, ?string $moduleCode, ?string $subject): array
    {
        $topicLabel = trim((string) $topicLabel);
        $moduleCode = trim((string) $moduleCode);

        if ($moduleCode !== '') {
            $module = SyllabusModule::where('code', $moduleCode)->first();
            if ($module !== null) {
                return ['module' => $module, 'confidence' => 0.98];
            }
        }

        if ($topicLabel === '') {
            return ['module' => null, 'confidence' => 0.0];
        }

        $candidates = SyllabusModule::query()
            ->when($subject !== null && $subject !== '', fn ($q) => $q->where('subject', $subject))
            ->get();

        $best = null;
        $bestScore = 0.0;
        foreach ($candidates as $candidate) {
            $score = $this->similarity($topicLabel, $candidate);

            if ($score > $bestScore) {
                $best = $candidate;
                $bestScore = $score;
            }
        }

        if ($best !== null && $bestScore >= self::MATCH_THRESHOLD) {
            return ['module' => $best, 'confidence' => round($bestScore, 2)];
        }

        return ['module' => null, 'confidence' => $bestScore > 0 ? round($bestScore, 2) : 0.0];
    }

    /**
     * Similarity of a free topic label to a module (0-1), best of topic,
     * description and code, normalised against the longer string.
     */
    private function similarity(string $label, SyllabusModule $module): float
    {
        $haystacks = array_values(array_filter([
            (string) $module->topic,
            (string) $module->description,
        ]));

        $best = 0.0;
        foreach ($haystacks as $haystack) {
            $best = max($best, $this->tokenOverlap($label, $haystack));
        }

        return $best;
    }

    /** Jaccard-style overlap of meaningful words, plus a substring bonus. */
    private function tokenOverlap(string $a, string $b): float
    {
        $tokens = fn (string $s): array => array_filter(
            explode(' ', strtolower(preg_replace('/[^a-z0-9 ]/i', ' ', $s) ?? '')),
            fn (string $t) => strlen($t) > 2 && ! in_array($t, ['the', 'and', 'for', 'with', 'using'], true),
        );

        $ta = $tokens($a);
        $tb = $tokens($b);
        if ($ta === [] || $tb === []) {
            return 0.0;
        }

        $inter = array_intersect($ta, $tb);
        $union = array_unique(array_merge($ta, $tb));
        $jaccard = count($inter) / max(1, count($union));

        // Every meaningful token of the module's topic appears in the label — strong
        // signal even when the label carries extra words (“plurals consonant y → ies”).
        $subset = count($inter) === count(array_unique($tb)) ? 0.15 : 0.0;

        $contains = str_contains(strtolower($b), strtolower($a)) || str_contains(strtolower($a), strtolower($b))
            ? 0.25
            : 0.0;

        return min(1.0, $jaccard + $subset + $contains);
    }
}
