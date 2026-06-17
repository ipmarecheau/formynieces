<?php

namespace App\Services\Practice;

use App\Models\PracticeQuestion;
use Illuminate\Support\Collection;

/**
 * PracticeQuestions — the question-source seam for the learning loop.
 *
 * The loop mechanic depends on THIS, never on the PracticeQuestion model or the
 * practice_questions table directly. Changing where practice questions come from
 * (a different table, a generated set, anchor reuse) is a change to this class
 * ONLY — the mechanic, the Livewire component, and the tests downstream are
 * insulated.
 *
 * Ordering is the difficulty climb (D: difficulty 1→3), with an optional
 * author-pinned sequence_order as the tiebreak within a difficulty rung.
 */
class PracticeQuestions
{
    /**
     * Active practice questions for a module, easiest-first.
     *
     * @return Collection<int,PracticeQuestion>
     */
    public function forModule(int $moduleId): Collection
    {
        return PracticeQuestion::query()
            ->where('module_id', $moduleId)
            ->where('is_active', true)
            ->orderBy('difficulty')
            ->orderByRaw('sequence_order IS NULL')   // pinned items before unpinned, within rung
            ->orderBy('sequence_order')
            ->orderBy('id')                          // stable final tiebreak
            ->get();
    }

    /** How many active questions a module has — used later to gate practiceability. */
    public function countForModule(int $moduleId): int
    {
        return PracticeQuestion::query()
            ->where('module_id', $moduleId)
            ->where('is_active', true)
            ->count();
    }
}