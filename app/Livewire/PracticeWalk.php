<?php

namespace App\Livewire;

use App\Models\SyllabusModule;
use App\Models\StudentProgress;
use App\Services\Practice\PracticeQuestions;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.diagnostic')]
class PracticeWalk extends Component
{
    public int $moduleId;
    public string $topic;
    public int $currentRung = 1;
    public int $currentStreak = 0;

    /** The question currently shown (or null if the rung has no questions). */
    public ?array $question = null;

    public function mount(SyllabusModule $module): void
    {
        $this->moduleId = $module->id;
        $this->topic    = $module->topic;

        // Resume the student's climb on this module, if any.
        $progress = StudentProgress::query()
            ->where('student_id', auth()->id())
            ->where('module_id', $module->id)
            ->first();

        $this->currentRung   = $progress->current_rung ?? 1;
        $this->currentStreak = $progress->current_streak ?? 0;

        $this->loadQuestion();
    }

    /** Pick the first active question at the current rung the student hasn't just been served. */
    private function loadQuestion(): void
    {
        $questions = app(PracticeQuestions::class)->forModule($this->moduleId);

        $atRung = $questions->firstWhere('difficulty', $this->currentRung);

        $this->question = $atRung === null ? null : [
            'id'      => $atRung->id,
            'prompt'  => $atRung->prompt,
            'options' => $atRung->options,   // cast to array on the model
        ];
    }

    public function render()
    {
        return view('livewire.practice-walk');
    }
}