<?php

namespace App\Livewire;

use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Services\Practice\CompetencyCheck;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * ModuleEntry — the front door to a module's learning loop.
 *
 * Opening a level lands here first. It runs three phases:
 *  - explainer: a short, student-language account of how the loop works (LL-19),
 *    with a button that leads into the competency check.
 *  - check: the fast D1/D3/D5 test-out (LL-20) — clear all three first-try and the
 *    module is mastered without ever opening the lesson or tutorial.
 *  - outcome: mastered celebration, or (on a miss) a choice of lesson / tutorial /
 *    practice (LL-21).
 */
#[Layout('components.layouts.diagnostic')]
class ModuleEntry extends Component
{
    /** The maintenance window: a mastered level is locked this many days before re-mastery. */
    public const MAINTENANCE_DAYS = 14;

    public int $moduleId;

    public string $topic;

    /** maintained | explainer | check | outcome */
    public string $phase = 'explainer';

    /** Days until the mastered level's re-mastery comes due (maintained phase only). */
    public int $daysToDue = 0;

    /**
     * The served check questions, display-safe (no correct_index leaks to the client).
     *
     * @var array<int,array{id:int,prompt:string,options:array<int,string>}>
     */
    public array $checkQuestions = [];

    /** Which served question she is currently answering. */
    public int $checkIndex = 0;

    /** Her first-try answers, keyed by question id. */
    public array $checkAnswers = [];

    /** Whether the completed check tested her out. */
    public bool $mastered = false;

    public function mount(SyllabusModule $module): void
    {
        $this->moduleId = $module->id;
        $this->topic = $module->topic;

        // A mastered level is LOCKED for its two-week window: greet her with a
        // "come back in N days" confirmation instead of the loop (LL-23). On or
        // after the due day the re-mastery check unlocks (LL-24).
        $progress = StudentProgress::query()
            ->where('student_id', auth()->id())
            ->where('module_id', $module->id)
            ->first();

        if ($progress?->status === 'mastered' && $progress->mastered_at !== null) {
            $due = $progress->mastered_at->copy()->addDays(self::MAINTENANCE_DAYS);

            if (now()->lt($due)) {
                $this->phase = 'maintained';
                $this->daysToDue = max(1, (int) ceil(now()->diffInDays($due)));
            }
        }
    }

    /** Leave the explainer and serve the D1/D3/D5 competency check. */
    public function beginCheck(): void
    {
        $served = app(CompetencyCheck::class)->serve(auth()->id(), $this->moduleId);

        $this->checkQuestions = $served->map(fn (PracticeQuestion $q): array => [
            'id' => $q->id,
            'prompt' => $q->prompt,
            'options' => $q->options,
        ])->all();

        $this->checkIndex = 0;
        $this->checkAnswers = [];
        $this->phase = 'check';
    }

    /** Record her answer to the current check question and advance, or grade at the end. */
    public function answerCheck(int $chosenIndex): void
    {
        if ($this->phase !== 'check' || ! isset($this->checkQuestions[$this->checkIndex])) {
            return;
        }

        $this->checkAnswers[$this->checkQuestions[$this->checkIndex]['id']] = $chosenIndex;

        if ($this->checkIndex + 1 < count($this->checkQuestions)) {
            $this->checkIndex++;

            return;
        }

        $this->finishCheck();
    }

    /** Grade the whole check against the real questions and land on the outcome. */
    private function finishCheck(): void
    {
        $served = PracticeQuestion::whereIn('id', array_column($this->checkQuestions, 'id'))->get();

        $this->mastered = app(CompetencyCheck::class)
            ->grade(auth()->id(), $this->moduleId, $served, $this->checkAnswers);

        $this->phase = 'outcome';
    }

    public function render()
    {
        return view('livewire.module-entry');
    }
}
