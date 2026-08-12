<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\StudentLlmUsage;

/**
 * The per-student LLM budget governor (AG-01..04).
 *
 * Meters real token usage into a monthly ledger and gates calls against two caps:
 *  - DISCRETIONARY (clarify chat, re-teach, worked examples) stops at the soft cap.
 *  - ESSENTIAL (essay grading, guardian summaries) runs to the hard cap.
 *
 * Budget is checked BEFORE a call and recorded AFTER, so spend can never overshoot by
 * more than one already-capped call.
 */
class LlmBudget
{
    /** Add one call's real usage to the student's month-to-date ledger. */
    public function record(int $studentId, int $inputTokens, int $outputTokens, ?float $cost = null): void
    {
        $cost ??= $this->costFor($inputTokens, $outputTokens);

        $row = StudentLlmUsage::firstOrNew([
            'student_id' => $studentId,
            'period' => $this->period(),
        ]);

        $row->input_tokens = (int) $row->input_tokens + $inputTokens;
        $row->output_tokens = (int) $row->output_tokens + $outputTokens;
        $row->cost_usd = (float) $row->cost_usd + $cost;
        $row->save();
    }

    /**
     * Whether a call may be made for this student right now. Discretionary features
     * (clarify chat, re-teach, worked examples) are held to the soft cap; essential
     * ones (essay grading, guardian summaries) run to the hard ceiling.
     */
    public function canSpend(int $studentId, bool $essential = false): bool
    {
        $cap = $essential
            ? (float) config('services.llm.monthly_cap_usd')
            : (float) config('services.llm.monthly_soft_cap_usd');

        return $this->spentUsd($studentId) < $cap;
    }

    /** Her spend so far THIS month, in USD. */
    public function spentUsd(int $studentId): float
    {
        return (float) (StudentLlmUsage::query()
            ->where('student_id', $studentId)
            ->where('period', $this->period())
            ->value('cost_usd') ?? 0.0);
    }

    /** Estimate a call's cost from token counts when the provider returns no usage.cost. */
    public function costFor(int $inputTokens, int $outputTokens): float
    {
        return $inputTokens / 1_000_000 * (float) config('services.llm.price_input_per_mtok')
            + $outputTokens / 1_000_000 * (float) config('services.llm.price_output_per_mtok');
    }

    private function period(): string
    {
        return now()->format('Y-m');
    }
}
