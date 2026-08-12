<?php

namespace App\Livewire;

use App\Models\SyllabusModule;
use App\Services\GuidedTime;
use App\Services\Practice\PracticeQuestions;
use App\Services\Practice\QuestionExposure;
use App\Services\Practice\WorkedExampleGenerator;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * TutorialWalk — the tutorial stage of the loop (TU-01..04).
 *
 * Smooth walks the student through a worked example built from an easiest-rung (D1)
 * question, revealing the solution one step at a time. It is NEVER scored — no
 * practice attempt, no progress change — and can be revisited freely. From here she
 * moves on to practise.
 */
#[Layout('components.layouts.diagnostic')]
class TutorialWalk extends Component
{
    public int $moduleId;

    public string $topic;

    public ?string $problem = null;

    /** @var array<int, string> */
    public array $steps = [];

    public int $revealed = 1;

    /** True once her 2-hour daily guided pool is spent — the tutorial locks for the day (AG-06). */
    public bool $guidedLocked = false;

    public function mount(SyllabusModule $module): void
    {
        $this->moduleId = $module->id;
        $this->topic = $module->topic;

        // Lock before any worked-example generation, so a spent pool costs nothing.
        $this->guidedLocked = app(GuidedTime::class)->isExhausted(auth()->id());
        if ($this->guidedLocked) {
            return;
        }

        $d1 = app(PracticeQuestions::class)->forModule($module->id)
            ->where('difficulty', 1)->values();

        $exposure = app(QuestionExposure::class);
        // Tutorials may recycle (they're never scored), so there's always something
        // to teach even once the fresh pool is used up.
        $question = $exposure->pickUnseen(auth()->id(), $d1, allowRecycle: true);

        if ($question === null) {
            return;
        }

        $exposure->record(auth()->id(), $question->content_hash, 'tutorial');

        $this->problem = strip_tags((string) $question->prompt);
        $this->steps = app(WorkedExampleGenerator::class)
            ->forQuestion($question, auth()->user())
            ->steps;
    }

    public function nextStep(): void
    {
        if ($this->revealed < count($this->steps)) {
            $this->revealed++;
        }
    }

    public function revealAll(): void
    {
        $this->revealed = count($this->steps);
    }

    public function render()
    {
        return view('livewire.tutorial-walk');
    }
}
