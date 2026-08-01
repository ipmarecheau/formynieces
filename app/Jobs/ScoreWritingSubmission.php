<?php

namespace App\Jobs;

use App\Models\WritingSubmission;
use App\Services\Writing\WritingScorer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Scores a pending writing submission out of band. Dispatched when synchronous
 * scoring at submit time failed because the AI provider was unavailable (WR-03):
 * the submission is already saved, so this job retries the scoring and applies the
 * rubric when the provider recovers. If scoring is still unavailable it throws and
 * the queue retries later — the submission simply stays pending.
 */
class ScoreWritingSubmission implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $backoff = 60;

    public function __construct(public WritingSubmission $submission) {}

    public function handle(WritingScorer $scorer): void
    {
        // Already scored (e.g. a duplicate dispatch) — nothing to do.
        if ($this->submission->isScored()) {
            return;
        }

        // Lets WritingScoringUnavailable bubble up so the queue retries.
        $rubric = $scorer->score($this->submission);

        $this->submission->applyRubric($rubric);
    }
}
