<?php

namespace App\Livewire;

use App\Models\DailyReadingAssignment;
use App\Models\VocabularyWord;
use App\Services\Motivation\DailyPlanComposer;
use App\Services\Motivation\StreakEconomyService;
use App\Services\Reading\DailyReadingService;
use App\Services\Reading\VocabularyService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The Morning Tide — one guided ritual (DR + DV): read the passage, chart it
 * (comprehension), then meet today's words in context. Satisfies the single
 * "morning_tide" duty on the Captain's Brief. Reached from Smooth's morning offer
 * or the Captain's Orders checklist. Progress is warm, never a grade (DR-03).
 */
#[Layout('components.layouts.diagnostic')]
class MorningTide extends Component
{
    public string $phase = 'read';     // read | check | vocab | done

    public ?int $assignmentId = null;

    public bool $noPassage = false;

    public int $qIndex = 0;

    /** @var array<int,mixed> question index => answer */
    public array $answers = [];

    public mixed $currentAnswer = null;

    /** @var list<int> snapshot of the day's word ids, fixed for the ritual */
    public array $vocabWordIds = [];

    public int $vocabIndex = 0;

    public string $currentSentence = '';

    public ?int $score = null;

    public function mount(): void
    {
        $assignment = app(DailyReadingService::class)->serve(auth()->user());

        if ($assignment === null) {
            $this->noPassage = true;

            return;
        }

        $this->assignmentId = $assignment->id;

        if ($assignment->completed_at !== null) {
            $this->phase = 'done';
            $this->score = $assignment->comprehension_score;
        }
    }

    public function startCheck(): void
    {
        $this->phase = 'check';
        $this->qIndex = 0;
        $this->currentAnswer = null;
    }

    public function nextQuestion(): void
    {
        $this->answers[$this->qIndex] = $this->currentAnswer;
        $this->currentAnswer = null;

        $questions = $this->assignment()->passage->questions ?? [];
        if ($this->qIndex < count($questions) - 1) {
            $this->qIndex++;

            return;
        }

        $scored = app(DailyReadingService::class)->score($this->assignment(), $this->answers);
        $this->score = $scored->comprehension_score;

        $this->vocabWordIds = app(VocabularyService::class)
            ->wordsForToday(auth()->id(), $this->assignment()->passage)
            ->pluck('id')->all();
        $this->vocabIndex = 0;
        $this->phase = $this->vocabWordIds === [] ? 'done' : 'vocab';

        if ($this->phase === 'done') {
            $this->finish();
        }
    }

    public function nextWord(): void
    {
        $wordId = $this->vocabWordIds[$this->vocabIndex] ?? null;
        if ($wordId !== null) {
            // Meeting the word in context is reinforcement (DV-02) — it feeds the schedule.
            app(VocabularyService::class)->recordResult(auth()->id(), $wordId, true);
        }
        $this->currentSentence = '';

        if ($this->vocabIndex < count($this->vocabWordIds) - 1) {
            $this->vocabIndex++;

            return;
        }

        $this->finish();
    }

    private function finish(): void
    {
        $studentId = (int) auth()->id();
        app(DailyPlanComposer::class)->markDuty($studentId, 'morning_tide');
        app(StreakEconomyService::class)->completeDailyMinimumIfMet($studentId);
        $this->phase = 'done';
    }

    private function assignment(): DailyReadingAssignment
    {
        return DailyReadingAssignment::with('passage')->findOrFail($this->assignmentId);
    }

    /** @return Collection<int,VocabularyWord> */
    private function vocabWords(): Collection
    {
        if ($this->vocabWordIds === []) {
            return collect();
        }

        return VocabularyWord::whereIn('id', $this->vocabWordIds)
            ->get()->sortBy(fn ($w) => array_search($w->id, $this->vocabWordIds))->values();
    }

    public function render(): View
    {
        $assignment = $this->assignmentId !== null ? $this->assignment() : null;
        $words = $this->phase === 'vocab' ? $this->vocabWords() : collect();

        return view('livewire.morning-tide', [
            'passage' => $assignment?->passage,
            'questions' => $assignment?->passage->questions ?? [],
            'currentWord' => $words[$this->vocabIndex] ?? null,
            'vocabTotal' => count($this->vocabWordIds),
        ]);
    }
}
