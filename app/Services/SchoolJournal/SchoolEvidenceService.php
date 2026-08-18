<?php

namespace App\Services\SchoolJournal;

use App\Models\SchoolJournalEntry;
use App\Models\SchoolStrandSignal;
use Illuminate\Support\Collection;

/**
 * SJ-05/08/09 — what the classroom evidence means to the engine.
 *
 * A guardian-confirmed entry writes strand signals: strong performance is a
 * corroborating confidence signal, a weak one gently weakens it. Signals live
 * in the honest layer only — they steer focus and inform the guardian's trend,
 * but never mark a module mastered, gate the map, or touch the child's
 * motivational world (SJ-06/SJ-08).
 */
class SchoolEvidenceService
{
    /**
     * Record signals for a guardian-confirmed entry (SJ-08). Strand-level from
     * the header score, plus a per-question module signal when questions were
     * digitised (SJ-13) — the sharper steering input. Idempotent per entry.
     */
    public function recordSignals(SchoolJournalEntry $entry): void
    {
        if ($entry->digitisation_status !== SchoolJournalEntry::STATUS_CONFIRMED) {
            return;
        }

        if (! blank($entry->strand)) {
            SchoolStrandSignal::updateOrCreate(
                ['school_journal_entry_id' => $entry->id, 'strand' => $entry->strand],
                [
                    'student_id' => $entry->student_id,
                    'direction' => $this->direction($entry->score),
                    'strength' => $this->strength($entry->score),
                ],
            );
        }

        $byModule = $entry->questions()
            ->whereNotNull('syllabus_module_id')
            ->get()
            ->groupBy('syllabus_module_id');

        foreach ($byModule as $moduleId => $questions) {
            $wrong = $questions->whereStrict('is_correct', false)->count();
            $right = $questions->whereStrict('is_correct', true)->count();

            SchoolStrandSignal::updateOrCreate(
                [
                    'school_journal_entry_id' => $entry->id,
                    'strand' => $questions->first()->module->topic,
                    'syllabus_module_id' => $moduleId,
                ],
                [
                    'student_id' => $entry->student_id,
                    // The paper's verdict on this topic: majority rule, weighted by how
                    // decisive the split was (SJ-13).
                    'direction' => $right >= $wrong ? 'corroborates' : 'weakens',
                    'strength' => round(
                        (float) $questions->avg('topic_confidence') * ($right + $wrong > 0 ? max($right, $wrong) / ($right + $wrong) : 1),
                        2,
                    ),
                ],
            );
        }
    }

    /**
     * SJ-05/SJ-13 — the day's gentle focus: prefer a syllabus topic the school
     * flagged weak (module-level signal), falling back to strand-level. Kind,
     * mark-free, advisory only.
     *
     * @return array{0: string, 1: bool}|null [label, isTopic]
     */
    public function weakestFocus(int $studentId): ?array
    {
        $latest = $this->latestPerStrand($studentId);

        $weakModule = $latest->first(function (SchoolStrandSignal $signal) {
            return $signal->direction === 'weakens' && $signal->syllabus_module_id !== null;
        });
        if ($weakModule !== null) {
            return [$weakModule->strand, true]; // strand column holds the topic for module signals
        }

        $weakStrand = $latest->first(fn (SchoolStrandSignal $signal) => $signal->direction === 'weakens');

        return $weakStrand !== null ? [$weakStrand->strand, false] : null;
    }

    /**
     * True when confirmed school evidence corroborates her understanding of a
     * strand — an extra honest-layer confidence signal, never mastery (SJ-08).
     */
    public function corroborates(int $studentId, string $strand): bool
    {
        return $this->latestPerStrand($studentId)
            ->contains(fn (SchoolStrandSignal $signal) => $signal->strand === $strand && $signal->direction === 'corroborates');
    }

    /**
     * SJ-09 — her per-strand school performance as a trend across terms,
     * newest term first, for the guardian's honest-layer view.
     *
     * @return array<int, array{term: string, strands: array<int, array{strand: string, score: string, assessment: string}>}>
     */
    public function trendByTerm(int $studentId): array
    {
        $entries = SchoolJournalEntry::query()
            ->where('student_id', $studentId)
            ->whereNotNull('strand')
            ->orderByDesc('assessment_date')
            ->get();

        $grouped = $entries->groupBy(fn (SchoolJournalEntry $entry) => $entry->term ?: 'Unlabelled');

        return $grouped->map(fn (Collection $termEntries, string $term) => [
            'term' => $term,
            'strands' => $termEntries->map(fn (SchoolJournalEntry $entry) => [
                'strand' => (string) $entry->strand,
                'score' => (string) $entry->score,
                'assessment' => (string) ($entry->assessment_type ?: 'assessment'),
            ])->values()->all(),
        ])->values()->all();
    }

    /**
     * SJ-04 — school evidence filed this week, for the guardian's weekly summary.
     *
     * @return Collection<int, SchoolJournalEntry>
     */
    public function thisWeek(int $studentId): Collection
    {
        return SchoolJournalEntry::query()
            ->where('student_id', $studentId)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->orderByDesc('created_at')
            ->get();
    }

    /** @return Collection<int, SchoolStrandSignal> */
    private function latestPerStrand(int $studentId): Collection
    {
        return SchoolStrandSignal::query()
            ->where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->get()
            ->unique(fn (SchoolStrandSignal $signal) => $signal->strand.'|'.(string) $signal->syllabus_module_id)
            ->values();
    }

    /**
     * A percentage read of "18/20"-style or "85%" scores; null when unparseable.
     */
    private function percentage(?string $score): ?float
    {
        if (blank($score)) {
            return null;
        }

        if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*%\s*$/', $score, $m)) {
            return (float) $m[1];
        }

        if (preg_match('/^\s*(\d+(?:\.\d+)?)\s*\/\s*(\d+(?:\.\d+)?)\s*$/', $score, $m) && (float) $m[2] > 0) {
            return round(((float) $m[1] / (float) $m[2]) * 100, 1);
        }

        return null;
    }

    private function direction(?string $score): string
    {
        $pct = $this->percentage($score);

        return $pct !== null && $pct >= 75 ? 'corroborates' : 'weakens';
    }

    private function strength(?string $score): float
    {
        $pct = $this->percentage($score);

        return $pct === null ? 0.5 : round(min(1.0, max(0.1, $pct / 100)), 2);
    }
}
