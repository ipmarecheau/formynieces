<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\StudentGuidedTime;

/**
 * The 2-hour daily guided-time pool (AG-05..07).
 *
 * Guided, LLM-tailored learning — lessons, tutorials, clarify chat, re-teach — draws
 * from this pool while the student is ACTIVELY engaged (a client heartbeat records a
 * fixed increment per active interval; idle time never beats, so it never counts —
 * AG-07). Practice uses no LLM and is UNLIMITED, so it never records here. When the
 * pool is spent, guided activities lock for the day; practice stays open (AG-06).
 */
class GuidedTime
{
    /** Seconds credited per active heartbeat from the client. */
    public const BEAT_SECONDS = 30;

    /** Add one active interval to today's pool, capped at the daily limit. */
    public function beat(int $studentId, int $seconds = self::BEAT_SECONDS): void
    {
        $row = StudentGuidedTime::firstOrNew([
            'student_id' => $studentId,
            'day' => now()->toDateString(),
        ]);

        $row->active_seconds = min($this->capSeconds(), (int) $row->active_seconds + max(0, $seconds));
        $row->save();
    }

    public function usedSecondsToday(int $studentId): int
    {
        return (int) (StudentGuidedTime::query()
            ->where('student_id', $studentId)
            ->where('day', now()->toDateString())
            ->value('active_seconds') ?? 0);
    }

    public function remainingSecondsToday(int $studentId): int
    {
        return max(0, $this->capSeconds() - $this->usedSecondsToday($studentId));
    }

    /** True once she has spent her whole daily guided pool. */
    public function isExhausted(int $studentId): bool
    {
        return $this->remainingSecondsToday($studentId) <= 0;
    }

    /** True when guided time is running low (some left, but at/under the warn threshold) — AG-11. */
    public function isRunningLow(int $studentId): bool
    {
        $remaining = $this->remainingSecondsToday($studentId);

        return $remaining > 0 && $remaining <= $this->warnSeconds();
    }

    /** The remaining-time threshold at which we warn her (default 10 minutes). */
    public function warnSeconds(): int
    {
        return (int) config('services.llm.guided_warn_seconds', 600);
    }

    /** The daily guided-time cap in seconds (default 2 hours). */
    public function capSeconds(): int
    {
        return (int) config('services.llm.guided_daily_seconds', 7200);
    }
}
