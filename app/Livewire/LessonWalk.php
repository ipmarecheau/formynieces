<?php

namespace App\Livewire;

use App\Models\Lesson;
use App\Models\ModuleStageCompletion;
use App\Models\SyllabusModule;
use App\Services\GuidedTime;
use App\Services\Practice\LearningGate;
use App\Services\Practice\Remediation;
use Illuminate\Support\Str;
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
    /** Block types she must answer correctly before "next" reveals the following block (gating). */
    public const INTERACTIVE_TYPES = ['check', 'fillblank', 'markwords', 'matchpairs', 'ordersteps'];

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

    /** True when this lesson is being re-walked as the relearn stage of an AI-assisted re-teach (LL-14/15). */
    public bool $reteach = false;

    public function mount(SyllabusModule $module): void
    {
        $this->moduleId = $module->id;
        $this->topic = $module->topic;
        $this->subject = $module->subject;
        $this->description = $module->description;
        $this->resources = $module->resources ?? [];
        $this->guidedLocked = app(GuidedTime::class)->isExhausted(auth()->id());
        $this->gatedSequence = app(LearningGate::class)->gated($module->id);
        $this->reteach = app(Remediation::class)->activeSession(auth()->id(), $module->id) !== null;

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

            // In a re-teach, Smooth reinforces the block she just finished by popping in with a
            // short question about it as the next one opens (LL-15, soft — never blocks advancing).
            if ($this->reteach) {
                $justFinished = $this->lessonBlocks[$this->revealed - 2] ?? null;
                if ($justFinished !== null) {
                    $this->dispatch('smooth-reinforce', context: $this->blockSnippet($justFinished));
                }
            }
        }

        $this->refreshCompletion();
    }

    /** A short plain-text snippet of a block, for Smooth's per-block reinforcement in a re-teach. */
    private function blockSnippet(array $block): string
    {
        $snippet = $block['content']
            ?? $block['question']
            ?? $block['prompt']
            ?? $block['instruction']
            ?? $this->topic;

        return Str::limit(strip_tags((string) $snippet), 160);
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

    /** Whether "next" is allowed: the current block is not an unanswered/failed interaction. */
    public function canAdvance(): bool
    {
        $current = $this->lessonBlocks[$this->revealed - 1] ?? null;
        if ($current !== null && in_array($current['type'] ?? '', self::INTERACTIVE_TYPES, true)) {
            return ($this->checkResults[$this->revealed - 1] ?? false) === true;
        }

        return true;
    }

    /** Answer a fill-in-the-blank block — correct on a trimmed, case-insensitive match (LE-07). */
    public function answerFillBlank(int $index, string $value): void
    {
        $block = $this->lessonBlocks[$index] ?? null;
        if ($block === null || ($block['type'] ?? '') !== 'fillblank') {
            return;
        }

        $expected = mb_strtolower(trim((string) ($block['answer'] ?? '')));
        $given = mb_strtolower(trim($value));
        $this->checkResults[$index] = ($given !== '' && $given === $expected);
        $this->refreshCompletion();
    }

    /** Answer a mark-the-words block — correct when the tapped tokens are exactly the targets (LE-08). */
    public function answerMarkWords(int $index, array $selected): void
    {
        $block = $this->lessonBlocks[$index] ?? null;
        if ($block === null || ($block['type'] ?? '') !== 'markwords') {
            return;
        }

        $targets = self::markWordTargets((string) ($block['text'] ?? ''));
        $chosen = array_map('intval', array_values($selected));
        sort($chosen);
        sort($targets);
        $this->checkResults[$index] = ($targets !== [] && $chosen === $targets);
        $this->refreshCompletion();
    }

    /** Answer a match-pairs block — correct when every left maps to its authored right value (LE-09). */
    public function answerMatchPairs(int $index, array $mapping): void
    {
        $block = $this->lessonBlocks[$index] ?? null;
        if ($block === null || ($block['type'] ?? '') !== 'matchpairs') {
            return;
        }

        $pairs = array_values($block['pairs'] ?? []);
        $ok = $pairs !== [];
        foreach ($pairs as $i => $pair) {
            if (($mapping[$i] ?? null) !== ($pair['right'] ?? null)) {
                $ok = false;
                break;
            }
        }
        $this->checkResults[$index] = $ok;
        $this->refreshCompletion();
    }

    /** Answer an order-the-steps block — correct when her sequence matches the authored order (LE-10). */
    public function answerOrderSteps(int $index, array $order): void
    {
        $block = $this->lessonBlocks[$index] ?? null;
        if ($block === null || ($block['type'] ?? '') !== 'ordersteps') {
            return;
        }

        $items = array_values($block['items'] ?? []);
        $this->checkResults[$index] = ($items !== [] && array_values($order) === $items);
        $this->refreshCompletion();
    }

    /**
     * The token indices (in a whitespace split) of the *asterisk-marked* target words in a
     * mark-the-words block. The renderer strips the asterisks for display but keeps the same split,
     * so tapped indices line up with these targets.
     *
     * @return array<int, int>
     */
    public static function markWordTargets(string $text): array
    {
        $tokens = preg_split('/\s+/', trim($text)) ?: [];
        $targets = [];
        foreach ($tokens as $i => $token) {
            if (str_contains($token, '*')) {
                $targets[] = $i;
            }
        }

        return $targets;
    }

    private function refreshCompletion(): void
    {
        $atEnd = $this->revealed >= count($this->lessonBlocks);

        // Every revealed interactive block must be answered correctly.
        foreach ($this->lessonBlocks as $index => $block) {
            if ($index >= $this->revealed) {
                break;
            }
            if (in_array($block['type'] ?? '', self::INTERACTIVE_TYPES, true) && ($this->checkResults[$index] ?? false) !== true) {
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
