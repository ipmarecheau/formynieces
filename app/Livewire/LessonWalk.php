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
use Livewire\Attributes\On;
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

    /** SEA objective codes this lesson teaches directly / reinforces indirectly (TR/§6 objective badge). */
    public array $objectivesDirect = [];

    public array $objectivesIndirect = [];

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

    /** In a re-teach: true once she has missed ANY interaction (kept for record/telemetry). */
    public bool $hadFailure = false;

    /** In a re-teach: the lesson is FROZEN while Smooth's chat drives remediation or the final examples. */
    public bool $paused = false;

    /** The interaction block index she must re-answer once remediation finishes, or null. */
    public ?int $pausedBlock = null;

    /** Guards the one-time end-of-lesson hand-off to the chat's three guided examples. */
    public bool $finalTriggered = false;

    /** True once the chat's three final examples are done — the proof CTA appears only then. */
    public bool $finalDone = false;

    /** In a re-teach: a brief hand-off splash shown right after a miss, BEFORE the chat takes focus (LL-15). */
    public bool $handoffSplash = false;

    /** In a re-teach: wrong-answer count per interaction block — she gets TWO tries before Smooth steps in. */
    public array $blockAttempts = [];

    /** Same-rule remediation cycles done on the current block (LL-26); three → the lesson is left in progress. */
    public int $remediationCycle = 0;

    /** True once a block has survived three remediation cycles — the lesson is left "in progress" (LL-27). */
    public bool $lessonInProgress = false;

    /** The correct answer of the block she couldn't land — shown kindly on the "in progress" screen (LL-27). */
    public string $inProgressAnswer = '';

    /** Same-rule remediation cycles a block gets before the lesson is left "in progress" (LL-27). */
    private const MAX_REMEDIATION_CYCLES = 3;

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
        $this->objectivesDirect = $lesson?->objectives_direct ?? [];
        $this->objectivesIndirect = $lesson?->objectives_indirect ?? [];

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
        $this->maybeHandOffFinal();
    }

    /** A short plain-text snippet of a block, for Smooth's remediation in a re-teach. */
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
        if ($this->paused) {
            return;   // frozen while the chat drives — inputs are ignored
        }

        $block = $this->lessonBlocks[$index] ?? null;
        if ($block === null || ($block['type'] ?? '') !== 'check') {
            return;
        }

        $this->checkResults[$index] = ($choice === (int) ($block['answer'] ?? -1));
        $this->afterCheck($index, $block);
    }

    /** Whether "next" is allowed: not paused for the chat, and the current block is not an unanswered/failed interaction. */
    public function canAdvance(): bool
    {
        if ($this->paused || $this->handoffSplash) {
            return false;   // the hand-off splash is up, or the chat is driving
        }

        $current = $this->lessonBlocks[$this->revealed - 1] ?? null;
        if ($current !== null && in_array($current['type'] ?? '', self::INTERACTIVE_TYPES, true)) {
            return ($this->checkResults[$this->revealed - 1] ?? false) === true;
        }

        return true;
    }

    /** Answer a fill-in-the-blank block — correct on a trimmed, case-insensitive match (LE-07). */
    public function answerFillBlank(int $index, string $value): void
    {
        if ($this->paused) {
            return;   // frozen while the chat drives — inputs are ignored
        }

        $block = $this->lessonBlocks[$index] ?? null;
        if ($block === null || ($block['type'] ?? '') !== 'fillblank') {
            return;
        }

        $expected = mb_strtolower(trim((string) ($block['answer'] ?? '')));
        $given = mb_strtolower(trim($value));
        $this->checkResults[$index] = ($given !== '' && $given === $expected);
        $this->afterCheck($index, $block);
    }

    /** Answer a mark-the-words block — correct when the tapped tokens are exactly the targets (LE-08). */
    public function answerMarkWords(int $index, array $selected): void
    {
        if ($this->paused) {
            return;   // frozen while the chat drives — inputs are ignored
        }

        $block = $this->lessonBlocks[$index] ?? null;
        if ($block === null || ($block['type'] ?? '') !== 'markwords') {
            return;
        }

        $targets = self::markWordTargets((string) ($block['text'] ?? ''));
        $chosen = array_map('intval', array_values($selected));
        sort($chosen);
        sort($targets);
        $this->checkResults[$index] = ($targets !== [] && $chosen === $targets);
        $this->afterCheck($index, $block);
    }

    /** Answer a match-pairs block — correct when every left maps to its authored right value (LE-09). */
    public function answerMatchPairs(int $index, array $mapping): void
    {
        if ($this->paused) {
            return;   // frozen while the chat drives — inputs are ignored
        }

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
        $this->afterCheck($index, $block);
    }

    /** Answer an order-the-steps block — correct when her sequence matches the authored order (LE-10). */
    public function answerOrderSteps(int $index, array $order): void
    {
        if ($this->paused) {
            return;   // frozen while the chat drives — inputs are ignored
        }

        $block = $this->lessonBlocks[$index] ?? null;
        if ($block === null || ($block['type'] ?? '') !== 'ordersteps') {
            return;
        }

        $items = array_values($block['items'] ?? []);
        $this->checkResults[$index] = ($items !== [] && array_values($order) === $items);
        $this->afterCheck($index, $block);
    }

    /**
     * After any interactive answer in a re-teach (LL-15/24/26/27):
     *  - first encounter: TWO tries, then a hand-off splash → Smooth's same-rule remediation;
     *  - a RE-ASK after remediation: correct resolves it; wrong reveals the answer and remediates the
     *    same rule with the NEXT word — until three cycles leave the lesson "in progress".
     */
    private function afterCheck(int $index, array $block): void
    {
        $correct = ($this->checkResults[$index] ?? false) === true;

        if ($this->reteach && $correct && $this->remediationCycle > 0) {
            $this->remediationCycle = 0;   // a correct re-ask resolves the remediation
        } elseif ($this->reteach && ! $correct && $this->remediationCycle > 0) {
            if ($this->remediationCycle >= self::MAX_REMEDIATION_CYCLES) {
                $this->inProgressAnswer = $this->correctAnswerText($block);
                $this->markInProgress();
            } else {
                $this->remediationCycle++;
                $this->dispatchRemediation($block);   // reveal the answer + remediate the next same-rule word
            }
        } elseif ($this->reteach && ! $correct) {
            // First encounter: two tries in the lesson, then Smooth steps in on the second miss.
            $this->blockAttempts[$index] = ($this->blockAttempts[$index] ?? 0) + 1;
            if ($this->blockAttempts[$index] >= 2) {
                $this->hadFailure = true;
                $this->pausedBlock = $index;
                $this->handoffSplash = true;   // gentle tap-through splash before the chat takes focus
                $this->dispatch('reteach-splash');
            }
        }

        $this->refreshCompletion();
        $this->maybeHandOffFinal();
    }

    /** She tapped through the hand-off splash — start the first same-rule remediation cycle (LL-15). */
    public function enterRemediation(): void
    {
        if (! $this->handoffSplash || $this->pausedBlock === null) {
            return;
        }

        $this->handoffSplash = false;
        $this->remediationCycle = 1;
        $this->dispatchRemediation($this->lessonBlocks[$this->pausedBlock]);
    }

    /** Freeze the lesson and hand the block's rule + this cycle's same-rule word to Smooth's chat (LL-24). */
    private function dispatchRemediation(array $block): void
    {
        $this->paused = true;
        $this->dispatch('reteach-miss',
            rule: (string) ($block['rule'] ?? ''),
            item: $this->practiceItemFor($block, $this->remediationCycle - 1),
            reveal: $this->remediationCycle > 1 ? $this->correctAnswerText($block) : '',
        );
        $this->dispatch('reteach-splash');   // bring the chat into view
    }

    /**
     * The same-rule word to remediate with for a given cycle — from the block's authored practiceItems,
     * clamped so it never overruns, and falling back to the block's own answer if none are authored.
     *
     * @return array{prompt:string, answer:string}
     */
    private function practiceItemFor(array $block, int $cycleIndex): array
    {
        $items = array_values($block['practiceItems'] ?? []);
        if ($items === []) {
            return ['prompt' => $this->blockSnippet($block), 'answer' => $this->correctAnswerText($block)];
        }
        $item = $items[min($cycleIndex, count($items) - 1)];

        return ['prompt' => (string) ($item['prompt'] ?? ''), 'answer' => (string) ($item['answer'] ?? '')];
    }

    /** The correct answer text for a block, for the kind reveal after a wrong re-ask (LL-26). */
    private function correctAnswerText(array $block): string
    {
        return match ($block['type'] ?? '') {
            'check' => (string) (($block['options'] ?? [])[(int) ($block['answer'] ?? -1)] ?? ''),
            'fillblank' => (string) ($block['answer'] ?? ''),
            default => $this->blockSnippet($block),
        };
    }

    /** Three cycles didn't land: leave the lesson "in progress" and let her move on (LL-27). */
    private function markInProgress(): void
    {
        $this->lessonInProgress = true;
        $this->paused = false;
        $this->pausedBlock = null;

        $session = app(Remediation::class)->activeSession(auth()->id(), $this->moduleId);
        $session?->update(['remediation_cycles' => self::MAX_REMEDIATION_CYCLES, 'left_in_progress_at' => now()]);

        $this->dispatch('lesson-resumed');
    }

    /**
     * When a re-teach lesson finishes, hand off to Smooth's chat to walk through three fresh bank
     * examples together before the proof — ALWAYS, on any path. Fires once; the proof CTA stays frozen
     * until the chat signals final-done (LL-15).
     */
    private function maybeHandOffFinal(): void
    {
        if ($this->reteach && $this->lessonComplete && ! $this->finalTriggered) {
            $this->finalTriggered = true;
            $this->paused = true;
            $this->dispatch('reteach-final');
        }
    }

    /** Smooth's chat finished this remediation step — unfreeze and re-ask the SAME block she missed (LL-15/26). */
    #[On('remediation-return')]
    public function onRemediationReturn(): void
    {
        if ($this->pausedBlock !== null) {
            unset($this->checkResults[$this->pausedBlock]);   // she must answer it again to advance
        }
        $this->paused = false;
        $this->refreshCompletion();
        $this->dispatch('lesson-resumed');   // scroll the lesson back into view
    }

    /** Smooth finished the review — show the "lesson complete, back to practice" popup (LL-15). */
    #[On('final-done')]
    public function onFinalDone(): void
    {
        $this->finalDone = true;
        $this->paused = false;
        // No scroll — a completion popup takes over (see the blade), then she heads back to practice.
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
