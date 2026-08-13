<?php

namespace App\Services\Practice;

use App\Models\PracticeAttempt;
use App\Models\ReteachSession;
use App\Models\StudentProgress;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Remediation — the AI-assisted re-teach a student is pulled into after struggling in practice
 * (LL-14, LL-22, LL-16). Owns trigger detection, the session lifecycle, and the resume rung.
 *
 * A "hard miss" is a question she got wrong on BOTH attempts (an attempt-2 row still wrong). Only
 * RESOLVED outcomes count — one per question encounter: a first-try-correct (attempt 1, correct)
 * or a final second attempt (attempt 2, either result). Counters read only outcomes AFTER her last
 * completed re-teach on this module, so entering a re-teach resets them.
 */
class Remediation
{
    /** Difficulties that count toward the two-in-a-row trigger (D3/D5). */
    private const HARD_RUNGS = [3, 5];

    private const WINDOW_SIZE = 7;

    private const WINDOW_MISSES = 5;

    private const STREAK_MISSES = 2;

    /** She resumes solo practice here after proving understanding — never the easiest rung (LL-16). */
    public const RESUME_RUNG = 3;

    /** The trigger reason for a re-teach, or null if none. */
    public function triggerFor(int $studentId, int $moduleId): ?string
    {
        if ($this->activeSession($studentId, $moduleId) !== null) {
            return null;   // already in a re-teach — never re-trigger
        }

        $outcomes = $this->resolvedOutcomes($studentId, $moduleId);

        // LL-22: five of the last seven resolved questions were hard misses.
        $recent = $outcomes->take(-self::WINDOW_SIZE);
        if ($recent->count() >= self::WINDOW_SIZE
            && $recent->filter(fn ($o): bool => ! $o->is_correct)->count() >= self::WINDOW_MISSES) {
            return ReteachSession::TRIGGER_WINDOW;
        }

        // LL-14: the last two resolved at D3/D5 were both hard misses.
        $lastTwoHard = $outcomes->whereIn('difficulty', self::HARD_RUNGS)->values()->take(-self::STREAK_MISSES);
        if ($lastTwoHard->count() === self::STREAK_MISSES
            && $lastTwoHard->every(fn ($o): bool => ! $o->is_correct)) {
            return ReteachSession::TRIGGER_STREAK;
        }

        return null;
    }

    public function activeSession(int $studentId, int $moduleId): ?ReteachSession
    {
        return ReteachSession::query()
            ->where('student_id', $studentId)
            ->where('module_id', $moduleId)
            ->whereNull('completed_at')
            ->latest('id')
            ->first();
    }

    /** Begin (or return the open) re-teach session. */
    public function start(int $studentId, int $moduleId, string $trigger): ReteachSession
    {
        return $this->activeSession($studentId, $moduleId)
            ?? ReteachSession::create([
                'student_id' => $studentId,
                'module_id' => $moduleId,
                'trigger' => $trigger,
                'started_at' => now(),
            ]);
    }

    /**
     * Record a correct D1 proof answer. On reaching the target (3 — LL-16) the session completes and
     * she resumes solo practice at D3.
     */
    public function recordCorrectProof(ReteachSession $session): ReteachSession
    {
        if ($session->isComplete()) {
            return $session;
        }

        $session->increment('correct_count');

        if ($session->correct_count >= ReteachSession::PROOF_TARGET) {
            $this->complete($session);
        }

        return $session->refresh();
    }

    private function complete(ReteachSession $session): void
    {
        $session->completed_at = now();
        $session->save();

        // Resume solo practice at D3 — never the easiest rung (LL-16).
        $progress = StudentProgress::firstOrNew([
            'student_id' => $session->student_id,
            'module_id' => $session->module_id,
        ]);
        $progress->status ??= 'needs_work';
        $progress->current_rung = self::RESUME_RUNG;
        $progress->current_streak = 0;
        $progress->streak_question_ids = [];
        $progress->save();
    }

    /**
     * Resolved question outcomes for this module since her last completed re-teach, in order.
     *
     * @return Collection<int, PracticeAttempt>
     */
    private function resolvedOutcomes(int $studentId, int $moduleId): Collection
    {
        $since = $this->resetBoundary($studentId, $moduleId);

        return PracticeAttempt::query()
            ->where('student_id', $studentId)
            ->where('module_id', $moduleId)
            ->when($since !== null, fn ($q) => $q->where('created_at', '>', $since))
            ->where(function ($q) {
                $q->where('attempt', '>=', 2)
                    ->orWhere(fn ($q2) => $q2->where('attempt', 1)->where('is_correct', true));
            })
            ->orderBy('id')
            ->get(['id', 'difficulty', 'is_correct']);
    }

    private function resetBoundary(int $studentId, int $moduleId): ?Carbon
    {
        return ReteachSession::query()
            ->where('student_id', $studentId)
            ->where('module_id', $moduleId)
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->value('completed_at');
    }
}
