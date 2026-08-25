<?php

declare(strict_types=1);

namespace App\Services\Lessons;

/**
 * LessonUniqueness — the "examples and questions never overlap" guard.
 *
 * Within a single lesson the worked EXAMPLE teaches with one problem; every question the child
 * then answers (`fillblank`, `check`, and every re-teach `practiceItem`) must use a DIFFERENT
 * problem. If the example expands 526, no question may also be about 526 — she would just copy
 * the answer she was shown. This class detects that overlap so the importer and the
 * `lessons:verify` command can reject it before a learner ever sees it.
 *
 * The "problem" a block is about is identified by its SUBJECT NUMBER: the longest number token
 * (by significant-digit count) in the block's text, e.g. `526`, `3.45`, `5,308`. A subject must
 * have at least two significant digits — a lone `1` or `2` (a fraction numerator, a step index)
 * is too common to be a meaningful identity and is ignored. Exact powers of ten (`10`, `100`,
 * `1000`, …) are also ignored as subjects: they are structural constants — the place-value base,
 * a unit-conversion factor, the percent base — that legitimately recur in a lesson's questions
 * without giving any answer away. A question overlaps an example when the example's subject value
 * appears anywhere in the question's text.
 */
class LessonUniqueness
{
    /** A subject number needs at least this many significant digits to count as an identity. */
    private const MIN_SUBJECT_DIGITS = 2;

    /**
     * Every overlap between a worked example and a question in the given lesson blocks.
     *
     * @param  array<int,array<string,mixed>>  $blocks
     * @return array<int,array{subject:string, exampleBlock:int, questionBlock:int, where:string}>
     */
    public function collisions(array $blocks): array
    {
        // Map each example's subject number => the (first) example block index that teaches it.
        $exampleSubjects = [];
        foreach (array_values($blocks) as $i => $block) {
            if (($block['type'] ?? null) !== 'example') {
                continue;
            }
            $subject = $this->subjectOf(array_merge(
                [(string) ($block['content'] ?? '')],
                array_map('strval', (array) ($block['steps'] ?? [])),
            ));
            if ($subject !== null && ! isset($exampleSubjects[$subject])) {
                $exampleSubjects[$subject] = $i;
            }
        }

        if ($exampleSubjects === []) {
            return [];
        }

        $collisions = [];
        foreach (array_values($blocks) as $i => $block) {
            foreach ($this->questionTexts($block) as [$where, $text]) {
                $values = $this->numericValues($text);
                foreach ($exampleSubjects as $subject => $exampleIndex) {
                    // Array keys that look like integers are cast to int by PHP; compare as strings.
                    if (in_array((string) $subject, $values, true)) {
                        $collisions[] = [
                            'subject' => (string) $subject,
                            'exampleBlock' => $exampleIndex,
                            'questionBlock' => $i,
                            'where' => $where,
                        ];
                    }
                }
            }
        }

        return $collisions;
    }

    /**
     * @param  array<int,array<string,mixed>>  $blocks
     */
    public function isClean(array $blocks): bool
    {
        return $this->collisions($blocks) === [];
    }

    /**
     * The subject number of a block: the number token with the most significant digits (ties
     * broken by earliest occurrence), or null when nothing reaches MIN_SUBJECT_DIGITS.
     *
     * @param  array<int,string>  $texts
     */
    private function subjectOf(array $texts): ?string
    {
        $best = null; // [digits, order, offset, normalizedValue]
        foreach ($texts as $order => $text) {
            if (preg_match_all('/\d[\d,]*(?:\.\d+)?/', $text, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as [$token, $offset]) {
                    $normalized = $this->normalize($token);

                    // Skip exact powers of ten (10, 100, 1000, …) — structural constants, not a subject.
                    if (preg_match('/^10+$/', $normalized)) {
                        continue;
                    }

                    $digits = strlen((string) preg_replace('/\D/', '', $token));
                    $candidate = [$digits, -$order, -$offset, $normalized];
                    if ($best === null || $candidate > $best) {
                        $best = $candidate;
                    }
                }
            }
        }

        return ($best !== null && $best[0] >= self::MIN_SUBJECT_DIGITS) ? $best[3] : null;
    }

    /**
     * The searchable text of every question a block poses — the block itself when it is a
     * `fillblank`/`check`, plus each of its re-teach `practiceItems`.
     *
     * @return array<int,array{0:string,1:string}> [where-label, text]
     */
    private function questionTexts(array $block): array
    {
        $texts = [];
        $type = $block['type'] ?? null;

        if ($type === 'fillblank') {
            $texts[] = ['fillblank', (string) ($block['prompt'] ?? '')];
        }
        if ($type === 'check') {
            $texts[] = ['check', trim((string) ($block['content'] ?? '').' '.implode(' ', array_map('strval', (array) ($block['options'] ?? []))))];
        }
        foreach (array_values((array) ($block['practiceItems'] ?? [])) as $j => $item) {
            $texts[] = ["practiceItem #{$j}", (string) ($item['prompt'] ?? '')];
        }

        return array_values(array_filter($texts, fn (array $t): bool => trim($t[1]) !== ''));
    }

    /**
     * The comma-normalized value of every number token in a string.
     *
     * @return array<int,string>
     */
    private function numericValues(string $text): array
    {
        preg_match_all('/\d[\d,]*(?:\.\d+)?/', $text, $matches);

        return array_map(fn (string $t): string => $this->normalize($t), $matches[0]);
    }

    private function normalize(string $token): string
    {
        return str_replace(',', '', $token);
    }
}
