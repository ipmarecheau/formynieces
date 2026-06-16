<?php

namespace App\Services\Diagnostic;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * SessionLifecycle — step 2e of the diagnostic engine.
 *
 * Owns the session's lifecycle: START (gated on completed onboarding), RESUME
 * (return the open session), and COMPLETE (derive the mastery map from all
 * recorded responses via MasteryInference and persist it into student_progress).
 *
 * This is the step that closes the loop: it turns a finished walk into the
 * mastery map the adventure screen reads from.
 *
 * DECISION D5 — student_progress.score semantics: the highest difficulty rung
 * the student demonstrated for that module (1 easy / 2 medium / 3 hard) for a
 * directly-tested + correct module; null for inferred or needs_work (no direct
 * difficulty signal). The learning loop later overwrites score with its own.
 *
 * Statuses written (D6): mastered | inferred_mastered | needs_work. Modules the
 * diagnostic never touched are left as-is (default not_started); we do not write
 * a row for every one of the 90 modules, only those the diagnostic informs.
 */
class SessionLifecycle
{
    public function __construct(
        private SessionPlanner $planner,
    ) {}

    /**
     * Start a new diagnostic for a student, or resume an existing open one.
     * Returns the session id. Throws if onboarding is not complete.
     */
    public function startOrResume(int $studentId): int
    {
        $student = DB::table('users')->find($studentId);
        if ($student === null) {
            throw new \RuntimeException("Student {$studentId} not found.");
        }
        if ($student->onboarding_completed_at === null) {
            throw new \DomainException('Cannot start a diagnostic before onboarding is complete.');
        }

        // Resume an in-progress session if one exists.
        $open = DB::table('diagnostic_sessions')
            ->where('student_id', $studentId)
            ->where('status', 'in_progress')
            ->orderByDesc('id')
            ->first();

        if ($open !== null) {
            return $open->id;
        }

        // Otherwise create a fresh session and plan it.
        $sessionId = DB::table('diagnostic_sessions')->insertGetId([
            'student_id'   => $studentId,
            'status'       => 'in_progress',
            'current_item' => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->planner->planForSession($sessionId);

        return $sessionId;
    }

    /**
     * Complete a session: derive the mastery map from every recorded response
     * and write it into student_progress, then mark the session completed.
     * Idempotent — completing an already-completed session re-derives and is safe.
     *
     * @return array<int,string>  the module_id => status map that was written
     */
    public function complete(int $sessionId): array
    {
        $session = DB::table('diagnostic_sessions')->find($sessionId);
        if ($session === null) {
            throw new \RuntimeException("Diagnostic session {$sessionId} not found.");
        }

        $studentId = $session->student_id;

        // Re-completing an already-completed session must not roll score into
        // previous_score again (that would corrupt the prior-attempt signal).
        // Only a first completion shifts previous_score.
        $isRecompletion = $session->status === 'completed';

        // Build the inference engine from the live prerequisite graph.
        $edges = DB::table('module_prerequisites')->get(['module_id', 'prerequisite_module_id']);
        $inference = MasteryInference::fromEdges($edges);

        // Gather responses with the module each anchor targets and its difficulty.
        $responses = DB::table('diagnostic_responses as r')
            ->join('anchor_question_module as m', 'm.anchor_question_id', '=', 'r.anchor_question_id')
            ->join('anchor_questions as a', 'a.id', '=', 'r.anchor_question_id')
            ->where('r.diagnostic_session_id', $sessionId)
            ->get(['m.module_id', 'a.difficulty', 'r.is_correct']);

        $map = $inference->deriveMap($responses);

        // Highest correct difficulty per directly-tested module, for the score (D5).
        $directDifficulty = [];
        foreach ($responses as $r) {
            if ($r->is_correct) {
                $directDifficulty[$r->module_id] = max(
                    $directDifficulty[$r->module_id] ?? 0,
                    (int) $r->difficulty
                );
            }
        }

        $now = now();

        DB::transaction(function () use ($map, $studentId, $directDifficulty, $now, $isRecompletion) {
            foreach ($map as $moduleId => $status) {
                $score = $status === MasteryInference::STATUS_MASTERED
                    ? ($directDifficulty[$moduleId] ?? null)
                    : null;

                $existing = DB::table('student_progress')
                    ->where('student_id', $studentId)
                    ->where('module_id', $moduleId)
                    ->first();

                if ($existing !== null) {
                    DB::table('student_progress')
                        ->where('id', $existing->id)
                        ->update([
                            // Preserve previous_score on a re-completion of the same
                            // session; only a genuine new attempt rolls it forward.
                            'previous_score' => $isRecompletion ? $existing->previous_score : $existing->score,
                            'status'         => $status,
                            'score'          => $score,
                            'updated_at'     => $now,
                        ]);
                } else {
                    DB::table('student_progress')->insert([
                        'student_id'     => $studentId,
                        'module_id'      => $moduleId,
                        'status'         => $status,
                        'score'          => $score,
                        'previous_score' => null,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]);
                }
            }
        });

        DB::table('diagnostic_sessions')->where('id', $sessionId)->update([
            'status'       => 'completed',
            'completed_at' => $now,
            'updated_at'   => $now,
        ]);

        return $map;
    }

    /** Whether a session's plan has been fully walked (ready to complete). */
    public function isReadyToComplete(int $sessionId): bool
    {
        $walk = new ItemWalk($this->planner);

        return $walk->currentQuestion($sessionId) === null;
    }
}
