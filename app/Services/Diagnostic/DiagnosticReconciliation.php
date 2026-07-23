<?php

declare(strict_types=1);

namespace App\Services\Diagnostic;

use App\Models\StudentProgress;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Reconciles a completed diagnostic against the guardian's stated weak areas.
 *
 * A guardian's stated weak areas are the strand names in the student's
 * known_weak_areas. A module's strand is the "Strand: Topic" portion before the
 * first colon, matching SyllabusModule::strandsBySubject().
 *
 * A guardian-flagged strand is "confirmed weak" when the diagnostic marked at
 * least one of that strand's modules needs_work; otherwise it is "cleared". A
 * cleared strand means the diagnostic disagreed with the guardian, so a guardian
 * decision is required before the roadmap proceeds. When every flagged strand is
 * confirmed (RR-02), or flagged strands are confirmed and further weak strands
 * are also found (RR-03), nothing is cleared and no decision is required.
 */
final class DiagnosticReconciliation
{
    /**
     * Days a guardian decision may sit unanswered before it auto-proceeds.
     */
    public const HOLD_DAYS = 3;

    /**
     * The guardian-flagged strands the diagnostic did NOT confirm weak.
     *
     * @return array<int, string>
     */
    public function clearedStrands(User $student): array
    {
        $flagged = $this->flaggedStrands($student);

        if ($flagged === []) {
            return [];
        }

        $confirmed = $this->confirmedWeakStrands($student);

        return array_values(array_diff($flagged, $confirmed));
    }

    /**
     * True when at least one flagged strand was cleared — i.e. the diagnostic
     * disagrees with the guardian, so a guardian decision is required.
     */
    public function requiresGuardianDecision(User $student): bool
    {
        return $this->clearedStrands($student) !== [];
    }

    /**
     * True when a guardian decision is required and she has not yet made one.
     * While pending, the student's onboarding and roadmap wait on her choice.
     */
    public function isPending(User $student): bool
    {
        return $student->guardian_reconciled_at === null
            && $this->requiresGuardianDecision($student);
    }

    /**
     * When the hold began — the student's most recent completed diagnostic.
     * Null when she has no completed diagnostic session.
     */
    public function holdStartedAt(User $student): ?Carbon
    {
        $completedAt = $student->diagnosticSessions()
            ->where('status', 'completed')
            ->max('completed_at');

        return $completedAt !== null ? Carbon::parse($completedAt) : null;
    }

    /**
     * True when a decision is still pending and the hold has run for at least
     * HOLD_DAYS — i.e. it may now auto-proceed rather than keep waiting.
     */
    public function hasTimedOut(User $student): bool
    {
        if (! $this->isPending($student)) {
            return false;
        }

        $startedAt = $this->holdStartedAt($student);

        return $startedAt !== null
            && $startedAt->lte(now()->subDays(self::HOLD_DAYS));
    }

    /**
     * Strand names the guardian flagged, normalized to a unique list.
     *
     * @return array<int, string>
     */
    private function flaggedStrands(User $student): array
    {
        return array_values(array_unique(
            array_map('trim', (array) ($student->known_weak_areas ?? [])),
        ));
    }

    /**
     * Strands the diagnostic confirmed weak: those with at least one needs_work
     * module for this student.
     *
     * @return array<int, string>
     */
    private function confirmedWeakStrands(User $student): array
    {
        $rows = StudentProgress::query()
            ->where('student_id', $student->id)
            ->where('status', 'needs_work')
            ->with('module:id,topic')
            ->get();

        $confirmed = [];
        foreach ($rows as $progress) {
            $strand = $this->strandFromTopic($progress->module?->topic);
            if ($strand !== null) {
                $confirmed[] = $strand;
            }
        }

        return array_values(array_unique($confirmed));
    }

    /**
     * The strand portion of a "Strand: Topic" name, or null when the topic has
     * no colon (and therefore no strand).
     */
    private function strandFromTopic(?string $topic): ?string
    {
        if ($topic === null || ! str_contains($topic, ':')) {
            return null;
        }

        return trim(strstr($topic, ':', true));
    }
}
