<?php

namespace App\Services\Practice;

use App\Models\Lesson;
use App\Models\ModuleStageCompletion;

/**
 * LearningGate — the gated learning sequence: lesson -> worked examples -> practice (LE-03/LE-06).
 *
 * When a student does not test out of a module's competency check, the ways in are stepped:
 * worked examples stay locked until she has completed the lesson once, and practice stays locked
 * until she has completed the worked examples once. Completion is recorded permanently, so an
 * unlocked stage stays open for that module forever after.
 *
 * The gate ONLY applies to a module that has BOTH an authored lesson AND worked-example content
 * (at least one easiest-rung / D1 practice question). A module missing either stage is never
 * gated — every way in stays open, exactly as before.
 */
class LearningGate
{
    public function __construct(private PracticeQuestions $practiceQuestions) {}

    /** Whether the sequence gate applies to this module (it has both a lesson and worked examples). */
    public function gated(int $moduleId): bool
    {
        $hasLesson = Lesson::where('module_id', $moduleId)->where('is_published', true)->exists();

        if (! $hasLesson) {
            return false;
        }

        return $this->practiceQuestions->forModule($moduleId)
            ->where('difficulty', 1)
            ->isNotEmpty();
    }

    /** Record that a student has completed a learning stage of a module (permanent, idempotent). */
    public function markCompleted(int $studentId, int $moduleId, string $stage): void
    {
        ModuleStageCompletion::firstOrCreate(
            ['student_id' => $studentId, 'module_id' => $moduleId, 'stage' => $stage],
            ['completed_at' => now()],
        );
    }

    /** Whether a student has completed a given stage of a module at least once. */
    public function stageCompleted(int $studentId, int $moduleId, string $stage): bool
    {
        return ModuleStageCompletion::where('student_id', $studentId)
            ->where('module_id', $moduleId)
            ->where('stage', $stage)
            ->exists();
    }

    /** Worked examples are open unless the module is gated and she has not finished the lesson. */
    public function workedExamplesUnlocked(int $studentId, int $moduleId): bool
    {
        if (! $this->gated($moduleId)) {
            return true;
        }

        return $this->stageCompleted($studentId, $moduleId, ModuleStageCompletion::STAGE_LESSON);
    }

    /** Practice is open unless the module is gated and she has not finished the worked examples. */
    public function practiceUnlocked(int $studentId, int $moduleId): bool
    {
        if (! $this->gated($moduleId)) {
            return true;
        }

        return $this->stageCompleted($studentId, $moduleId, ModuleStageCompletion::STAGE_TUTORIAL);
    }

    /** Child-friendly copy shown when she taps a stage that is still locked (LE-06). */
    public function lockMessage(string $lockedStage): string
    {
        return match ($lockedStage) {
            ModuleStageCompletion::STAGE_TUTORIAL => "Let's do the lesson first — then the worked examples open up! 🐢",
            default => "Let's do the lesson and worked examples first, then practice is all yours! 🐢",
        };
    }
}
