<?php

declare(strict_types=1);

namespace App\Services\Practice;

use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Services\Motivation\StreakService;
use Illuminate\Support\Collection;

/**
 * CompetencyCheck — the fast test-out that greets a level.
 *
 * She is served ONE unseen question at each real difficulty — D1, D3, D5. Clear all
 * three on the first (only) try and she has tested out: the module is mastered without
 * ever opening the lesson or tutorial. Anything less and she is handed the choice of
 * lesson / tutorial / practice (LL-21) — the check itself never masters on a miss.
 *
 * Question selection goes through the same PracticeQuestions seam and QuestionExposure
 * no-repeat ledger as practice, so the check only ever serves her questions she has not
 * seen anywhere in the loop (LL-18/LL-20).
 */
class CompetencyCheck
{
    /** One question at each real difficulty, easy → tricky. */
    public const DIFFICULTIES = [1, 3, 5];

    /** The hardest rung — the maintenance re-check draws only these. */
    public const MASTERY_DIFFICULTY = 5;

    /** How many D5 questions the maintenance re-check asks (LL-24). */
    public const MAINTENANCE_QUESTIONS = 3;

    public function __construct(
        private PracticeQuestions $questions,
        private QuestionExposure $exposure,
        private StreakService $streaks,
    ) {}

    /**
     * One unseen active question at each of D1/D3/D5 for the module, in that order.
     * Each served question is recorded on the no-repeat ledger.
     *
     * @return Collection<int,PracticeQuestion>
     */
    public function serve(int $studentId, int $moduleId): Collection
    {
        $pool = $this->questions->forModule($moduleId);
        $served = collect();

        foreach (self::DIFFICULTIES as $difficulty) {
            $candidates = $pool->where('difficulty', $difficulty)->values();
            $question = $this->exposure->pickUnseen($studentId, $candidates, allowRecycle: false);

            if ($question !== null) {
                $this->exposure->record($studentId, $question->content_hash, 'check');
                $served->push($question);
            }
        }

        return $served;
    }

    /**
     * Grade the served check. $answers maps question id => chosen option index.
     * She tests out only by answering ONE question at every difficulty correctly on
     * the first try. On a pass the module is marked mastered.
     *
     * @param  Collection<int,PracticeQuestion>  $served
     * @param  array<int,int>  $answers
     */
    public function grade(int $studentId, int $moduleId, Collection $served, array $answers): bool
    {
        $passed = $served->count() === count(self::DIFFICULTIES)
            && $served->every(fn (PracticeQuestion $q): bool => ($answers[$q->id] ?? null) === $q->correct_index);

        if ($passed) {
            $this->master($studentId, $moduleId);
        }

        return $passed;
    }

    /**
     * The maintenance re-check: three unseen D5 questions to keep a mastered level sharp
     * (LL-24). Recycles the least-recently-seen D5 when the pool is exhausted, so a
     * long-mastered level can always be re-checked.
     *
     * @return Collection<int,PracticeQuestion>
     */
    public function serveMaintenance(int $studentId, int $moduleId): Collection
    {
        $pool = $this->questions->forModule($moduleId)->where('difficulty', self::MASTERY_DIFFICULTY)->values();
        $served = collect();

        for ($i = 0; $i < self::MAINTENANCE_QUESTIONS; $i++) {
            $question = $this->exposure->pickUnseen($studentId, $pool, allowRecycle: true);
            if ($question === null) {
                break;
            }

            $this->exposure->record($studentId, $question->content_hash, 'maintenance');
            $served->push($question);
            $pool = $pool->reject(fn (PracticeQuestion $q): bool => $q->id === $question->id)->values();
        }

        return $served;
    }

    /**
     * Grade the maintenance re-check. Three D5 first-try-correct re-masters the module and
     * resets its two-week window; anything less leaves the window as it was (LL-17 decay
     * still governs the grace).
     *
     * @param  Collection<int,PracticeQuestion>  $served
     * @param  array<int,int>  $answers
     */
    public function gradeMaintenance(int $studentId, int $moduleId, Collection $served, array $answers): bool
    {
        $passed = $served->count() === self::MAINTENANCE_QUESTIONS
            && $served->every(fn (PracticeQuestion $q): bool => ($answers[$q->id] ?? null) === $q->correct_index);

        if ($passed) {
            $this->master($studentId, $moduleId);
        }

        return $passed;
    }

    /** Project a mastered state onto the read-model, mirroring the practice climb. */
    private function master(int $studentId, int $moduleId): void
    {
        $progress = StudentProgress::firstOrNew([
            'student_id' => $studentId,
            'module_id' => $moduleId,
        ]);

        $wasMastered = $progress->status === 'mastered';

        $progress->previous_score = $progress->score;
        $progress->status = 'mastered';
        $progress->mastered_at = now();   // anchors the maintenance window (LL-23/24)
        $progress->current_rung = 5;
        $progress->current_streak = 3;
        $progress->score = 100;
        $progress->save();

        if (! $wasMastered) {
            $this->streaks->recordActivity($studentId, 'mastery');
        }
    }
}
