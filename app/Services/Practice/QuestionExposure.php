<?php

declare(strict_types=1);

namespace App\Services\Practice;

use App\Models\PracticeQuestion;
use App\Models\StudentQuestionExposure;
use Illuminate\Support\Collection;

/**
 * The per-student no-repeat ledger. Every question served to a student is recorded
 * by its content hash; selection then excludes anything she has already seen.
 *
 * A `context` ('diagnostic' | 'tutorial' | 'practice' | 'check') records WHY a
 * question was shown. Tutorial exposures are teaching views (the worked example is
 * shown with its answer) and the tutorial RECYCLES the D1 pool, so they must never
 * gate practice/check selection — callers pass `excludeContexts: ['tutorial']` to
 * keep tutorial views from draining the practice pool and dead-ending a student on
 * "more practice coming soon".
 *
 * When a pool is exhausted, `pickUnseen()` can optionally RECYCLE (return the
 * least-recently-seen question) — used only by the maintenance phase; normal
 * practice/check pass `allowRecycle: false` so a question is never repeated.
 */
class QuestionExposure
{
    /**
     * Content hashes this student has already been shown, optionally ignoring
     * exposures recorded under the given contexts.
     *
     * @param  list<string>  $excludeContexts  contexts that should NOT count as "seen"
     * @return list<string>
     */
    public function seenHashes(int $studentId, array $excludeContexts = []): array
    {
        return StudentQuestionExposure::query()
            ->where('student_id', $studentId)
            ->when($excludeContexts !== [], fn ($q) => $q->whereNotIn('context', $excludeContexts))
            ->pluck('content_hash')
            ->all();
    }

    /**
     * Record that a question was shown to a student (idempotent per content hash;
     * a repeat bumps seen_count and touches updated_at for recycle ordering).
     */
    public function record(int $studentId, string $contentHash, string $context): void
    {
        $exposure = StudentQuestionExposure::firstOrNew([
            'student_id' => $studentId,
            'content_hash' => $contentHash,
        ]);

        if ($exposure->exists) {
            $exposure->seen_count++;
        }
        $exposure->context = $context;
        $exposure->save();
    }

    /**
     * The first question from $candidates the student has NOT yet seen. If all are
     * seen and $allowRecycle is true, returns the least-recently-seen one instead
     * (maintenance only); otherwise null.
     *
     * @param  Collection<int, PracticeQuestion>  $candidates
     * @param  list<string>  $excludeContexts  contexts that should NOT count as "seen"
     */
    public function pickUnseen(int $studentId, Collection $candidates, bool $allowRecycle = false, array $excludeContexts = []): ?PracticeQuestion
    {
        $seen = $this->seenHashes($studentId, $excludeContexts);

        $unseen = $candidates->first(fn (PracticeQuestion $q) => ! in_array($q->content_hash, $seen, true));
        if ($unseen !== null) {
            return $unseen;
        }

        if (! $allowRecycle || $candidates->isEmpty()) {
            return null;
        }

        // Exhausted: recycle the least-recently-seen candidate.
        $order = StudentQuestionExposure::query()
            ->where('student_id', $studentId)
            ->whereIn('content_hash', $candidates->pluck('content_hash'))
            ->orderBy('updated_at')
            ->pluck('content_hash')
            ->all();

        $oldestHash = $order[0] ?? null;

        return $candidates->firstWhere('content_hash', $oldestHash) ?? $candidates->first();
    }
}
