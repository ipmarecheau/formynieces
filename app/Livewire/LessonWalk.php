<?php

namespace App\Livewire;

use App\Models\Lesson;
use App\Models\ModuleStageCompletion;
use App\Models\SyllabusModule;
use App\Services\GuidedTime;
use App\Services\Practice\LearningGate;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * LessonWalk (LE-01) — the interactive, self-contained lesson.
 *
 * The lesson is revealed one block at a time (chunking): she taps "next" to move on, and
 * where a block is a CHECK she must answer it correctly to continue (retrieval practice +
 * gating). Practice unlocks only once she's worked all the way through. A placeholder
 * lesson (none authored yet) simply shows the blurb with practice open.
 */
#[Layout('components.layouts.diagnostic')]
class LessonWalk extends Component
{
    public int $moduleId;

    public string $topic;

    public string $subject;

    public ?string $description = null;

    public array $resources = [];

    /** True once her 2-hour daily guided pool is spent — the lesson locks for the day (AG-06). */
    public bool $guidedLocked = false;

    /** The authored interactive lesson (LE-01), or null until one is authored. */
    public ?string $lessonTitle = null;

    public array $lessonBlocks = [];

    /** How many blocks have been revealed so far (chunked step-through). */
    public int $revealed = 1;

    /** Per-check-block outcome: block index => true (correct) | false (missed, retry). */
    public array $checkResults = [];

    /** True once she's worked through the whole lesson (or there's no authored lesson). */
    public bool $lessonComplete = false;

    /** True when this module runs the gated sequence — the lesson leads to worked examples next (LE-03). */
    public bool $gatedSequence = false;

    public function mount(SyllabusModule $module): void
    {
        $this->moduleId = $module->id;
        $this->topic = $module->topic;
        $this->subject = $module->subject;
        $this->description = $module->description;
        $this->resources = $module->resources ?? [];
        $this->guidedLocked = app(GuidedTime::class)->isExhausted(auth()->id());
        $this->gatedSequence = app(LearningGate::class)->gated($module->id);

        $lesson = Lesson::where('module_id', $module->id)->where('is_published', true)->first();
        $this->lessonTitle = $lesson?->title;
        $this->lessonBlocks = $lesson?->blocks ?? [];

        // A no-lesson placeholder, or a lesson already at its last block, unlocks practice.
        $this->refreshCompletion();
    }

    /** Reveal the next block, once the current one isn't a still-unanswered check. */
    public function next(): void
    {
        if (! $this->canAdvance()) {
            return;
        }

        if ($this->revealed < count($this->lessonBlocks)) {
            $this->revealed++;
        }

        $this->refreshCompletion();
    }

    /** Answer an inline check block (unscored — pure retrieval practice). */
    public function answerCheck(int $index, int $choice): void
    {
        $block = $this->lessonBlocks[$index] ?? null;
        if ($block === null || ($block['type'] ?? '') !== 'check') {
            return;
        }

        $this->checkResults[$index] = ($choice === (int) ($block['answer'] ?? -1));
        $this->refreshCompletion();
    }

    /** Whether "next" is allowed: the current block is not an unanswered/failed check. */
    public function canAdvance(): bool
    {
        $current = $this->lessonBlocks[$this->revealed - 1] ?? null;
        if ($current !== null && ($current['type'] ?? '') === 'check') {
            return ($this->checkResults[$this->revealed - 1] ?? false) === true;
        }

        return true;
    }

    private function refreshCompletion(): void
    {
        $atEnd = $this->revealed >= count($this->lessonBlocks);

        // Every revealed check must be correct.
        foreach ($this->lessonBlocks as $index => $block) {
            if ($index >= $this->revealed) {
                break;
            }
            if (($block['type'] ?? '') === 'check' && ($this->checkResults[$index] ?? false) !== true) {
                $this->lessonComplete = false;

                return;
            }
        }

        $this->lessonComplete = $atEnd;

        // Finishing an authored lesson records the 'lesson' stage — the first gate in the
        // lesson -> worked examples -> practice sequence (LE-03). Placeholder (no authored
        // blocks) never records: an ungated module has nothing to unlock.
        if ($this->lessonComplete && $this->lessonBlocks !== []) {
            app(LearningGate::class)->markCompleted(
                auth()->id(),
                $this->moduleId,
                ModuleStageCompletion::STAGE_LESSON,
            );
        }
    }

    public function render()
    {
        return view('livewire.lesson-walk');
    }
}
