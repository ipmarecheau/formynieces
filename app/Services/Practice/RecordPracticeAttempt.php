<?php

namespace App\Services\Practice;

use App\Models\PracticeAttempt;
use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use Illuminate\Support\Facades\DB;

/**
 * RecordPracticeAttempt — the learning-loop mechanic.
 *
 * Takes one answer submission, records it as a PracticeAttempt (the diary),
 * then recomputes the student's climb on that module and projects it onto the
 * student_progress read-model (rung, streak, score%, status).
 *
 * THE RULE (locked):
 *  - Climb three rungs (difficulty 1 → 2 → 3), bottom-up.
 *  - Clear a rung by getting 3 CONSECUTIVE correct at that rung, on 3 DISTINCT
 *    questions. A repeat of a question already in the live streak does NOT count
 *    (and does not break it). A WRONG answer resets the streak to 0 but keeps
 *    the rung. Clearing a rung advances to the next; clearing rung 3 = mastered.
 *  - score = derived progress %: ((rung-1)*3 + streak) / 9 * 100, integer.
 *
 * student_progress is the projection the heart-gauge UI reads; practice_attempts
 * is the source of truth. previous_score holds the score from before this answer.
 */
class RecordPracticeAttempt
{
    private const STREAK_TO_CLEAR = 3;
    private const MASTERY_RUNG    = 3;
    private const TOTAL_STEPS     = 9; // 3 rungs * 3 streak each

    /**
     * Process one submission. Returns the fresh StudentProgress projection.
     */
    public function handle(int $studentId, int $questionId, int $chosenIndex): StudentProgress
    {
        $question = PracticeQuestion::findOrFail($questionId);
        $isCorrect = $chosenIndex === $question->correct_index;

        return DB::transaction(function () use ($studentId, $question, $isCorrect) {
            // 1. Diary: always record the raw attempt.
            PracticeAttempt::create([
                'student_id'           => $studentId,
                'practice_question_id' => $question->id,
                'module_id'            => $question->module_id,
                'difficulty'           => $question->difficulty,
                'is_correct'           => $isCorrect,
            ]);

            // 2. Load (or start) the projection row.
            $progress = StudentProgress::firstOrNew([
                'student_id' => $studentId,
                'module_id'  => $question->module_id,
            ]);
            $progress->status            ??= 'needs_work';
            $progress->current_rung      ??= 1;
            $progress->current_streak    ??= 0;
            $streakIds = $progress->streak_question_ids ?? [];

            $priorScore = $progress->score;

            // 3. Only answers AT the current rung affect the climb.
            if ($question->difficulty === $progress->current_rung) {
                if (! $isCorrect) {
                    $progress->current_streak = 0;
                    $streakIds = [];
                } elseif (! in_array($question->id, $streakIds, true)) {
                    // Distinct-within-streak: count only new questions.
                    $streakIds[] = $question->id;
                    $progress->current_streak++;

                    if ($progress->current_streak >= self::STREAK_TO_CLEAR) {
                        if ($progress->current_rung >= self::MASTERY_RUNG) {
                            $progress->status         = 'mastered';
                            $progress->current_streak = self::STREAK_TO_CLEAR; // cap
                        } else {
                            $progress->current_rung++;
                            $progress->current_streak = 0;
                            $streakIds = [];   // fresh streak at the new rung
                        }
                    }
                }
                // A correct repeat of a question already in the streak: no change.
            }

            $progress->streak_question_ids = $streakIds;

            // 4. Project score as a %, flooring at cleared-rung progress.
            $progress->previous_score = $priorScore;
            $progress->score = $this->scorePercent(
                $progress->current_rung,
                $progress->current_streak,
                $progress->status === 'mastered'
            );

            $progress->save();

            return $progress->fresh();
        });
    }

    /**
     * Is this question already part of the student's LIVE streak on its rung?
     * The live streak = the trailing run of correct answers at the current rung
     * since the last wrong answer (or since the rung began). We look back over
     * recent attempts at this rung and collect question ids until we hit a wrong
     * one; if this question is among them, it's a repeat that must not re-count.
     */
    private function questionAlreadyInLiveStreak(int $studentId, PracticeQuestion $question): bool
    {
        $recent = PracticeAttempt::query()
            ->where('student_id', $studentId)
            ->where('module_id', $question->module_id)
            ->where('difficulty', $question->difficulty)
            ->orderByDesc('id')
            ->get(['practice_question_id', 'is_correct']);

        $streakQuestionIds = [];
        foreach ($recent as $attempt) {
            // The just-recorded attempt for THIS question is the first row;
            // skip it so we examine the streak that preceded it.
            if ($attempt->practice_question_id === $question->id
                && $attempt->is_correct
                && ! in_array($question->id, $streakQuestionIds, true)
                && $streakQuestionIds === []) {
                continue;
            }
            if (! $attempt->is_correct) {
                break;
            }
            $streakQuestionIds[] = $attempt->practice_question_id;
        }

        return in_array($question->id, $streakQuestionIds, true);
    }

    private function scorePercent(int $rung, int $streak, bool $mastered): int
    {
        if ($mastered) {
            return 100;
        }
        $steps = ($rung - 1) * self::STREAK_TO_CLEAR + $streak;

        return (int) floor($steps / self::TOTAL_STEPS * 100);
    }
}