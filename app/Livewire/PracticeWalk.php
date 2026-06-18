<?php

namespace App\Livewire;

use App\Models\SyllabusModule;
use App\Models\StudentProgress;
use App\Services\Practice\PracticeQuestions;
use App\Services\Practice\RecordPracticeAttempt;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.diagnostic')]
class PracticeWalk extends Component
{
    public int $moduleId;
    public string $topic;
    public int $currentRung = 1;
    public int $currentStreak = 0;
    public bool $isMastered = false;

    public ?array $question = null;
    public ?array $feedback = null;

    public function mount(SyllabusModule $module): void
    {
        $this->moduleId = $module->id;
        $this->topic    = $module->topic;

        $progress = StudentProgress::query()
            ->where('student_id', auth()->id())
            ->where('module_id', $module->id)
            ->first();

        $this->currentRung   = $progress->current_rung ?? 1;
        $this->currentStreak = $progress->current_streak ?? 0;
        $this->isMastered    = ($progress->status ?? null) === 'mastered';

        $this->loadQuestion();
    }

    private function loadQuestion(): void
    {
        if ($this->isMastered) {
            $this->question = null;   // mastered: nothing to serve, celebration shows
            return;
        }

        $questions = app(PracticeQuestions::class)->forModule($this->moduleId);

        $usedInStreak = StudentProgress::query()
            ->where('student_id', auth()->id())
            ->where('module_id', $this->moduleId)
            ->value('streak_question_ids') ?? [];
        if (is_string($usedInStreak)) {
            $usedInStreak = json_decode($usedInStreak, true) ?: [];
        }

        $atRung = $questions
            ->where('difficulty', $this->currentRung)
            ->first(fn ($q) => ! in_array($q->id, $usedInStreak, true));

        $this->question = $atRung === null ? null : [
            'id'            => $atRung->id,
            'prompt'        => $atRung->prompt,
            'options'       => $atRung->options,
            'correct_index' => $atRung->correct_index,
            'explanation'   => $atRung->explanation,
        ];
    }

    public function choose(int $chosenIndex): void
    {
        if ($this->question === null || $this->feedback !== null) {
            return;
        }

        $wasCorrect = $chosenIndex === $this->question['correct_index'];

        $progress = app(RecordPracticeAttempt::class)
            ->handle(auth()->id(), $this->question['id'], $chosenIndex);

        $this->currentRung   = $progress->current_rung;
        $this->currentStreak = $progress->current_streak;
        $this->isMastered    = $progress->status === 'mastered';

        $this->feedback = [
            'correct'     => $wasCorrect,
            'explanation' => $this->question['explanation'] ?? '',
            'mastered'    => $this->isMastered,   // so the feedback screen can announce it
        ];
    }

    public function next(): void
    {
        $this->feedback = null;
        $this->loadQuestion();
    }

    public function render()
    {
        return view('livewire.practice-walk');
    }
}