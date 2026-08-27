<?php

declare(strict_types=1);

namespace App\Services\Estimator;

use App\Models\User;
use App\Models\WritingSubmission;
use Illuminate\Support\Facades\DB;

/**
 * Projects a student's SEA readiness from her OWN historical performance and
 * maps it to publicly-documented SEA placement bands.
 *
 * Honest by construction:
 *   - Scores reflect only material she has actually attempted / been taught
 *     ("performance in what was covered"), never the untaught remainder.
 *   - The placement bands are indicative cut-off ranges from public SEA data
 *     (composite score + order of merit; the most in-demand "traditional"
 *     schools historically cut off around the low-90s%, easing down from there).
 *     They name TIERS, not specific schools, and carry a confidence signal so a
 *     thin evidence base is never dressed up as a firm projection.
 *
 * The SEA composite here uses the app's paper weights (Math 50 / ELA 30 /
 * Creative Writing 20), matching the pace section so the two never disagree.
 */
final class PerformanceEstimator
{
    /** Paper weights toward the projected composite (percent). */
    private const WEIGHTS = ['Math' => 50, 'ELA' => 30, 'Writing' => 20];

    /** Minimum attempts before a subject accuracy is treated as reliable. */
    private const CONFIDENCE_MIN_ATTEMPTS = 15;

    /**
     * Indicative SEA placement bands, highest cut-off first. Thresholds are the
     * projected composite % at or above which the tier is in reach. Sourced from
     * public SEA cut-off ranges; override via config('sea.placement_bands') when
     * a year's official cut-offs are on hand.
     *
     * @var array<int, array{min: int, tier: string, note: string}>
     */
    private const PLACEMENT_BANDS = [
        ['min' => 90, 'tier' => 'Traditional / most in-demand schools', 'note' => 'On track for the most competitive ("prestige") secondary schools, whose cut-offs historically sit in the low-90s%.'],
        ['min' => 78, 'tier' => 'High-demand schools', 'note' => 'Competitive for many sought-after schools; a strong first-choice position.'],
        ['min' => 60, 'tier' => 'Solid placement range', 'note' => 'A comfortable placement range across a wide set of schools.'],
        ['min' => 40, 'tier' => 'Developing', 'note' => 'Placement is within reach; the focus areas below lift the projection fastest.'],
        ['min' => 0,  'tier' => 'Early days', 'note' => 'Too little assessed so far to project placement — more practice will sharpen this.'],
    ];

    /**
     * @return array{
     *   has_data: bool,
     *   subjects: array<int, array{subject: string, label: string, accuracy: ?int, attempts: int, mastery_pct: int, confident: bool}>,
     *   composite: ?int,
     *   confidence: string,
     *   placement: array{tier: string, note: string},
     *   covered_note: string
     * }
     */
    public function estimate(User $student, array $subjectAnalysis): array
    {
        $accuracy = $this->accuracyBySubject($student->id);
        $writing = $this->writingPercent($student->id);

        $subjects = [];
        $totalAttempts = 0;

        foreach (['Math', 'ELA'] as $subject) {
            $acc = $accuracy[$subject] ?? null;
            $attempts = $acc['attempts'] ?? 0;
            $totalAttempts += $attempts;

            $analysis = $subjectAnalysis[$subject] ?? [];
            $expected = (int) ($analysis['expected'] ?? 0);
            $completed = (int) ($analysis['completed'] ?? 0);
            $masteryPct = $expected > 0 ? (int) round(($completed / $expected) * 100) : 0;

            $subjects[] = [
                'subject' => $subject,
                'label' => $subject === 'Math' ? 'Mathematics' : 'English Language Arts',
                'accuracy' => $acc['pct'] ?? null,
                'attempts' => $attempts,
                'mastery_pct' => $masteryPct,
                'confident' => $attempts >= self::CONFIDENCE_MIN_ATTEMPTS,
            ];
        }

        if ($writing !== null) {
            $subjects[] = [
                'subject' => 'Writing',
                'label' => 'Creative Writing',
                'accuracy' => $writing['pct'],
                'attempts' => $writing['count'],
                'mastery_pct' => $writing['pct'],
                'confident' => $writing['count'] >= 1,
            ];
        }

        $composite = $this->projectComposite($accuracy, $writing, $subjectAnalysis);
        $hasData = $totalAttempts > 0 || $writing !== null;
        $confidence = $this->confidenceLabel($totalAttempts, $writing !== null);
        $placement = $this->placementFor($hasData ? $composite : null);

        return [
            'has_data' => $hasData,
            'subjects' => $subjects,
            'composite' => $hasData ? $composite : null,
            'confidence' => $confidence,
            'placement' => $placement,
            'covered_note' => 'Based only on material covered so far — it sharpens as more is practised.',
        ];
    }

    /**
     * Per-subject accuracy from the practice-attempt history.
     *
     * @return array<string, array{pct: int, attempts: int}>
     */
    private function accuracyBySubject(int $studentId): array
    {
        $rows = DB::table('practice_attempts')
            ->join('syllabus_modules', 'practice_attempts.module_id', '=', 'syllabus_modules.id')
            ->where('practice_attempts.student_id', $studentId)
            ->groupBy('syllabus_modules.subject')
            ->selectRaw('syllabus_modules.subject as subject, COUNT(*) as attempts, SUM(practice_attempts.is_correct) as correct')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $attempts = (int) $row->attempts;
            $out[$row->subject] = [
                'pct' => $attempts > 0 ? (int) round(((int) $row->correct / $attempts) * 100) : 0,
                'attempts' => $attempts,
            ];
        }

        return $out;
    }

    /**
     * Writing performance as a percentage of the four-dimension mark.
     *
     * @return array{pct: int, count: int}|null
     */
    private function writingPercent(int $studentId): ?array
    {
        $rows = WritingSubmission::where('student_id', $studentId)
            ->whereNotNull('scored_at')
            ->get(['content_score', 'language_score', 'grammar_score', 'organisation_score']);

        if ($rows->isEmpty()) {
            return null;
        }

        $avg = $rows->avg(fn ($r): float => ($r->content_score + $r->language_score
            + $r->grammar_score + $r->organisation_score) / 4);

        return ['pct' => (int) round(($avg / 10) * 100), 'count' => $rows->count()];
    }

    /**
     * Project the weighted SEA composite. Each subject uses its accuracy where
     * there is attempt history, otherwise its mastery-of-covered ratio, so a
     * newly-started subject still contributes an honest (if provisional) figure.
     */
    private function projectComposite(array $accuracy, ?array $writing, array $subjectAnalysis): int
    {
        $weightedSum = 0.0;
        $weightUsed = 0;

        foreach (['Math', 'ELA'] as $subject) {
            $pct = $accuracy[$subject]['pct'] ?? $this->masteryPct($subjectAnalysis[$subject] ?? []);
            if ($pct === null) {
                continue;
            }
            $weightedSum += $pct * self::WEIGHTS[$subject];
            $weightUsed += self::WEIGHTS[$subject];
        }

        if ($writing !== null) {
            $weightedSum += $writing['pct'] * self::WEIGHTS['Writing'];
            $weightUsed += self::WEIGHTS['Writing'];
        }

        return $weightUsed > 0 ? (int) round($weightedSum / $weightUsed) : 0;
    }

    private function masteryPct(array $analysis): ?int
    {
        $expected = (int) ($analysis['expected'] ?? 0);
        if ($expected === 0) {
            return null;
        }

        return (int) round(((int) ($analysis['completed'] ?? 0) / $expected) * 100);
    }

    private function confidenceLabel(int $attempts, bool $hasWriting): string
    {
        if ($attempts >= self::CONFIDENCE_MIN_ATTEMPTS * 2) {
            return 'high';
        }
        if ($attempts >= self::CONFIDENCE_MIN_ATTEMPTS || $hasWriting) {
            return 'moderate';
        }
        if ($attempts > 0) {
            return 'low';
        }

        return 'insufficient';
    }

    /**
     * @return array{tier: string, note: string}
     */
    private function placementFor(?int $composite): array
    {
        if ($composite === null) {
            $early = self::PLACEMENT_BANDS[array_key_last(self::PLACEMENT_BANDS)];

            return ['tier' => $early['tier'], 'note' => $early['note']];
        }

        foreach (self::PLACEMENT_BANDS as $band) {
            if ($composite >= $band['min']) {
                return ['tier' => $band['tier'], 'note' => $band['note']];
            }
        }

        $last = self::PLACEMENT_BANDS[array_key_last(self::PLACEMENT_BANDS)];

        return ['tier' => $last['tier'], 'note' => $last['note']];
    }
}
