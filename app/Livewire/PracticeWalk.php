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

    /** Current question: id, prompt, options, correct_index, explanation. Null if rung empty. */
    public ?array $question = null;

    /** Feedback state: null while answering; set after an answer until "Next". */
    public ?array $feedback = null;   // ['correct'=>bool, 'explanation'=>string]

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

        $this->loadQuestion();
    }

    private function loadQuestion(): void
    {
        $questions = app(PracticeQuestions::class)->forModule($this->moduleId);

        // Question ids already used in the current live streak — skip them so the
        // child doesn't see the same item twice while building a streak.
        $usedInStreak = StudentProgress::query()
            ->where('student_id', auth()->id())
            ->where('module_id', $this->moduleId)
            ->value('streak_question_ids') ?? [];

        // streak_question_ids is JSON; value() returns the raw string on some drivers.
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

    /** Student taps an option: record it, compute feedback, update the climb. */
    public function choose(int $chosenIndex): void
    {
        if ($this->question === null || $this->feedback !== null) {
            return; // ignore taps when not in answering state
        }

        $wasCorrect = $chosenIndex === $this->question['correct_index'];

        $progress = app(RecordPracticeAttempt::class)
            ->handle(auth()->id(), $this->question['id'], $chosenIndex);

        // Refresh the climb from the updated projection.
        $this->currentRung   = $progress->current_rung;
        $this->currentStreak = $progress->current_streak;

        $this->feedback = [
            'correct'     => $wasCorrect,
            'explanation' => $this->question['explanation'] ?? '',
        ];
    }

    /** "Next" tap: clear feedback, load the next question at the (possibly new) rung. */
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