<?php

namespace App\Services\Practice;

use App\Models\PracticeAttempt;
use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Services\Motivation\StreakService;
use Illuminate\Support\Facades\DB;

/**
 * RecordPracticeAttempt — the learning-loop mechanic.
 *
 * Takes one answer submission, records it as a PracticeAttempt (the diary),
 * then recomputes the student's climb on that module and projects it onto the
 * student_progress read-model (rung, streak, score%, status).
 *
 * THE RULE (redesigned 2026-08-11):
 *  - Climb three rungs at the bank's real difficulty levels: 1 -> 3 -> 5, bottom-up.
 *  - Every question allows a SECOND attempt (the caller passes the attempt number).
 *  - Below the hardest rung: clear a rung by 3 CONSECUTIVE correct on 3 DISTINCT
 *    questions, where a question answered correctly WITHIN TWO tries counts. A
 *    question failed on the second attempt resets the streak; a first-try miss that
 *    is then recovered does not.
 *  - At the hardest rung (difficulty 5): mastery is reserved for FIRST-TRY success —
 *    3 consecutive first-try-correct on distinct questions. A first-try miss resets
 *    the mastery streak; a second-attempt answer never advances it.
 *  - A repeat of a question already in the live streak does NOT count (and does not
 *    break it). A wrong answer keeps the rung.
 *  - score = derived progress %: (rungIndex*3 + streak) / 9 * 100, integer.
 *
 * student_progress is the projection the heart-gauge UI reads; practice_attempts
 * is the source of truth. previous_score holds the score from before this answer.
 */
class RecordPracticeAttempt
{
    private const STREAK_TO_CLEAR = 3;

    /** The ordered climb rungs — the bank's real difficulty levels. */
    private const RUNGS = [1, 3, 5];

    /** Mastery is earned at the hardest rung. */
    private const MASTERY_DIFFICULTY = 5;

    private const TOTAL_STEPS = 9; // 3 rungs * 3 streak each

    public function __construct(private StreakService $streaks) {}

    /**
     * Process one submission. $attempt is 1 for a first try, 2 for the retry a
     * student gets after a first-try miss. Returns the fresh StudentProgress.
     */
    public function handle(int $studentId, int $questionId, int $chosenIndex, int $attempt = 1): StudentProgress
    {
        $question = PracticeQuestion::findOrFail($questionId);
        $isCorrect = $chosenIndex === $question->correct_index;
        $isFirstTry = $attempt <= 1;

        [$progress, $wasMasteredBefore] = DB::transaction(function () use ($studentId, $question, $isCorrect, $isFirstTry) {
            // 1. Diary: always record the raw attempt.
            PracticeAttempt::create([
                'student_id' => $studentId,
                'practice_question_id' => $question->id,
                'module_id' => $question->module_id,
                'difficulty' => $question->difficulty,
                'is_correct' => $isCorrect,
            ]);

            // 2. Load (or start) the projection row.
            $progress = StudentProgress::firstOrNew([
                'student_id' => $studentId,
                'module_id' => $question->module_id,
            ]);
            $progress->status ??= 'needs_work';
            $progress->current_rung ??= 1;
            $progress->current_streak ??= 0;
            $streakIds = $progress->streak_question_ids ?? [];

            $wasMasteredBefore = ($progress->status === 'mastered');
            $priorScore = $progress->score;

            // 3. Only answers AT the current rung affect the climb.
            if ($question->difficulty === $progress->current_rung) {
                $isMasteryRung = ($progress->current_rung === self::MASTERY_DIFFICULTY);
                $isNewQuestion = ! in_array($question->id, $streakIds, true);

                if ($isMasteryRung) {
                    // Mastery counts first-try success only.
                    if ($isFirstTry && $isCorrect && $isNewQuestion) {
                        $streakIds[] = $question->id;
                        $progress->current_streak++;
                    } elseif ($isFirstTry && ! $isCorrect) {
                        $progress->current_streak = 0;
                        $streakIds = [];
                    }
                    // A second attempt never touches the mastery streak.
                } else {
                    // Lower rungs: a correct answer within two tries advances; only a
                    // failed second attempt resets.
                    if ($isCorrect && $isNewQuestion) {
                        $streakIds[] = $question->id;
                        $progress->current_streak++;
                    } elseif (! $isCorrect && ! $isFirstTry) {
                        $progress->current_streak = 0;
                        $streakIds = [];
                    }
                    // A first-try miss (pending its retry) leaves the streak untouched.
                }

                if ($progress->current_streak >= self::STREAK_TO_CLEAR) {
                    if ($isMasteryRung) {
                        $progress->status = 'mastered';
                        $progress->current_streak = self::STREAK_TO_CLEAR; // cap
                    } else {
                        $progress->current_rung = $this->nextRung($progress->current_rung);
                        $progress->current_streak = 0;
                        $streakIds = [];   // fresh streak at the new rung
                    }
                }
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

            return [$progress->fresh(), $wasMasteredBefore];
        });

        // Practice day-streak: every recorded attempt (correct or wrong) advances it.
        $this->streaks->recordActivity($studentId, 'practice');

        // Mastery day-streak: advances exactly once, the first time a module reaches
        // mastered in this call.
        if ($progress->status === 'mastered' && ! $wasMasteredBefore) {
            $this->streaks->recordActivity($studentId, 'mastery');
        }

        return $progress;
    }

    /** The next difficulty rung up from $rung (unchanged if already the top). */
    private function nextRung(int $rung): int
    {
        $index = array_search($rung, self::RUNGS, true);
        if ($index === false || $index + 1 >= count(self::RUNGS)) {
            return $rung;
        }

        return self::RUNGS[$index + 1];
    }

    private function scorePercent(int $rung, int $streak, bool $mastered): int
    {
        if ($mastered) {
            return 100;
        }
        $index = array_search($rung, self::RUNGS, true);
        $index = $index === false ? 0 : $index;
        $steps = $index * self::STREAK_TO_CLEAR + $streak;

        return (int) floor($steps / self::TOTAL_STEPS * 100);
    }
}
