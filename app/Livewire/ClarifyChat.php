<?php

namespace App\Livewire;

use App\Models\Lesson;
use App\Models\SyllabusModule;
use App\Services\LearningProfile;
use App\Services\LlmBudget;
use App\Services\LlmService;
use App\Services\Practice\Remediation;
use App\Services\Safety\ChildSafetyModerator;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * ClarifyChat (LE-04) — the Socratic tutor beside the lesson, and the driver of a re-teach's
 * structured remediation (LL-15).
 *
 * Outside a re-teach it answers questions about THIS lesson only, grounded + profile-tailored +
 * child-safety screened, never handing over answers. Inside a re-teach it is fully STRUCTURED
 * (tap buttons, bank-sourced, no LLM): when the lesson pauses on a miss it runs a principle check
 * plus one worked example, then hands the baton back; at lesson's end it walks through three fresh
 * examples before the proof. History is ephemeral (component state) — no transcript is stored.
 */
class ClarifyChat extends Component
{
    public int $moduleId;

    public string $topic = '';

    /** @var array<int,array{role:string,content:string}> */
    public array $messages = [];

    public string $draft = '';

    public bool $thinking = false;

    /** True while this lesson is the relearn stage of a re-teach — the chat is structured, not free. */
    public bool $reteach = false;

    /** Which structured flow is driving: 'dormant' | 'remediation' | 'final'. */
    public string $reteachMode = 'dormant';

    /** Remediation step: 'check' (type the same-rule word) | 'sayback' (say the rule in her own words). */
    public string $remStep = 'check';

    /** The rule this remediation is about (from the failed lesson block — LL-24). */
    public string $remRule = '';

    /** @var array{prompt:string,answer:string} The same-rule word she is asked to type this cycle. */
    public array $remItem = [];

    /** How many unclear "say it back" tries so far — after a few, Smooth accepts so she is never stuck. */
    public int $remSaybackTries = 0;

    /** @var array<int,array{prompt:string,answer:string,rule:string}> End-of-lesson review items — from the lesson's OWN practice items (same rules), guided type-the-answer, never the answer given. */
    public array $finalItems = [];

    /** Index of the review item on screen. */
    public int $finalAt = 0;

    /** Wrong tries on the current review item — a hint after one, the answer revealed after two. */
    public int $finalTries = 0;

    private const UNSAFE_INPUT_REPLY = "Let's keep our chat about the lesson 🐢 — which part can I help explain?";

    private const UNSAFE_OUTPUT_REPLY = 'Hmm, let me put that a better way — try asking me about a specific step in the lesson!';

    private const OVER_BUDGET_REPLY = "Smooth's chat is taking a little rest for now — keep going with the lesson and practice, you've got this! 🌊";

    private const CONTINUE_LESSON_REPLY = "You're doing great — tap “Got it — next →” to continue the lesson. 🐢";

    /** Tappable starter prompts shown before she's asked anything (non-re-teach only). */
    public const STARTERS = ['Explain this simply', 'Give me an example', 'Quiz me on this'];

    public function mount(int $moduleId): void
    {
        $module = SyllabusModule::findOrFail($moduleId);
        $this->moduleId = $module->id;
        $this->topic = $module->topic;
        $this->reteach = app(Remediation::class)->activeSession(auth()->id(), $module->id) !== null;
    }

    /** A tapped starter chip — fill the draft and send it. */
    public function ask(string $prompt): void
    {
        $this->draft = $prompt;
        $this->send();
    }

    /** Triggered from the lesson (e.g. "ask for more worked examples") via a Livewire event. */
    #[On('ask-smooth')]
    public function askSmooth(string $prompt): void
    {
        $this->ask($prompt);
    }

    // -------------------------------------------------------- re-teach: structured remediation (C)

    /**
     * The lesson paused on a miss and handed us the baton with the failed block's RULE + a same-rule word
     * (LL-24). We test that word; on a miss we explain the rule and ask her to say it back (LL-25).
     *
     * @param  array{prompt?:string, answer?:string}  $item
     */
    #[On('reteach-miss')]
    public function startRemediation(string $rule, array $item, string $reveal = ''): void
    {
        $this->remRule = $rule;
        $this->remItem = ['prompt' => (string) ($item['prompt'] ?? ''), 'answer' => (string) ($item['answer'] ?? '')];
        $this->remStep = 'check';
        $this->remSaybackTries = 0;
        $this->reteachMode = 'remediation';
        $this->reply($reveal !== ''
            ? "The answer was “{$reveal}”. Let's try another one together 🐢"
            : "Let's work this one out together 🐢");
    }

    /** Grade her typed same-rule answer (LL-24): right → back to the lesson; wrong → explain + say-it-back (LL-25). */
    private function handleRemediationInput(string $typed): void
    {
        if ($this->remStep === 'check') {
            if ($this->normalise($typed) === $this->normalise($this->remItem['answer'] ?? '')) {
                $this->reply("Yes! 🎉 That's it.");
                $this->finishRemediation();

                return;
            }
            $this->reply('Not quite — here\'s the rule 🐢');
            $this->reply($this->remRule);
            $this->remStep = 'sayback';
            $this->reply('Now say that rule back to me in your own words. 🐢');

            return;
        }

        // 'sayback' — she restates the rule; the LLM judges "close enough" (LL-25), with a safe fallback.
        $studentId = auth()->id();
        if (! app(ChildSafetyModerator::class)->moderate($typed, $studentId)->safe) {
            $this->reply(self::UNSAFE_INPUT_REPLY);

            return;
        }

        if ($this->saybackIsCloseEnough($typed, $this->remRule)) {
            $this->reply("That's it — you've got the rule! 🎉");
            $this->finishRemediation();

            return;
        }

        $this->remSaybackTries++;
        if ($this->remSaybackTries >= 3) {
            $this->reply("No worries — the rule is: {$this->remRule} Let's try the question again. 🐢");
            $this->finishRemediation();

            return;
        }
        $this->reply('Close! Try saying it in your own words once more. 🌱');
    }

    /** Remediation step done — hand the lesson back to re-ask the SAME block (LL-15/26). */
    private function finishRemediation(): void
    {
        $this->reteachMode = 'dormant';
        $this->remStep = 'check';
        $this->remRule = '';
        $this->remItem = [];
        $this->remSaybackTries = 0;
        $this->dispatch('remediation-return');
    }

    /** Whether her restated rule captures the main idea — LLM judged, budget-safe (accepts if no budget). */
    private function saybackIsCloseEnough(string $said, string $rule): bool
    {
        $studentId = auth()->id();
        if (! app(LlmBudget::class)->canSpend($studentId, false)) {
            return true;   // budget spent — accept so she is never left stuck
        }

        $result = app(LlmService::class)->completeJson(
            'You judge whether a 10-year-old restated a spelling rule closely enough. Respond ONLY as JSON: {"match": true} or {"match": false}.',
            "The rule: \"{$rule}\"\nThe child said: \"{$said}\"\nDoes the child's answer capture the rule's main idea (different wording and small mistakes are fine)?",
            maxTokens: 40,
            studentId: $studentId,
            essential: false,
        );

        return ($result['match'] ?? false) === true;
    }

    /** Trim + lower-case for a forgiving typed-answer match. */
    private function normalise(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    // ------------------------------------------------------- re-teach: end-of-lesson review (D)

    /**
     * The lesson finished — guide her through a few of the lesson's OWN practice items (same rules) as
     * a type-the-answer review. Smooth explains + guides on a miss, never just gives the answer (LL-15).
     */
    #[On('reteach-final')]
    public function startFinal(): void
    {
        if ($this->reteachMode === 'final') {
            return;
        }

        $this->finalItems = $this->lessonPracticeItems(3);
        if ($this->finalItems === []) {
            $this->dispatch('final-done');   // nothing authored to review — proceed to the proof

            return;
        }

        $this->finalAt = 0;
        $this->finalTries = 0;
        $this->reteachMode = 'final';
        $this->reply("You worked the whole lesson! 🎉 Let's review a few together before you prove it.");
    }

    /** Grade a typed review answer: right → praise + next; wrong → a rule hint, then reveal on the second miss. */
    private function handleFinalInput(string $typed): void
    {
        $item = $this->finalItems[$this->finalAt] ?? null;
        if ($item === null) {
            return;
        }

        if ($this->normalise($typed) === $this->normalise($item['answer'])) {
            $this->reply('Yes! 🎉 Nice work.');
            $this->advanceFinalItem();

            return;
        }

        $this->finalTries++;
        if ($this->finalTries < 2) {
            $this->reply("Not quite — remember: {$item['rule']} Try that one again. 🐢");

            return;
        }
        $this->reply("That one's tricky — it's “{$item['answer']}”. Let's keep going!");
        $this->advanceFinalItem();
    }

    /** Move to the next review item, or finish and hand her to the proof. */
    private function advanceFinalItem(): void
    {
        $this->finalAt++;
        $this->finalTries = 0;
        if ($this->finalAt >= count($this->finalItems)) {
            $this->reteachMode = 'dormant';
            $this->reply("Great reviewing! 🎉 Now let's prove it.");
            $this->dispatch('final-done');
        }
    }

    /**
     * A spread of the lesson's OWN practice items (one per interactive block, so it covers the lesson's
     * rules) as {prompt, answer, rule} — the review stays coherent with what the lesson taught (LL-24).
     *
     * @return array<int,array{prompt:string,answer:string,rule:string}>
     */
    private function lessonPracticeItems(int $limit): array
    {
        $lesson = Lesson::where('module_id', $this->moduleId)->where('is_published', true)->first();

        $items = [];
        foreach ($lesson?->blocks ?? [] as $block) {
            $first = array_values($block['practiceItems'] ?? [])[0] ?? null;
            if ($first !== null) {
                $items[] = [
                    'prompt' => (string) ($first['prompt'] ?? ''),
                    'answer' => (string) ($first['answer'] ?? ''),
                    'rule' => (string) ($block['rule'] ?? ''),
                ];
            }
        }

        return array_slice($items, 0, $limit);
    }

    // ---------------------------------------------------------------------- free chat (LE-04)

    public function send(): void
    {
        $question = trim($this->draft);
        if ($question === '') {
            return;
        }

        $studentId = auth()->id();
        $this->messages[] = ['role' => 'user', 'content' => $question];
        $this->draft = '';

        // In a re-teach, typing IS the remediation input (type the word / say the rule back); when nothing
        // is active she is doing fine, so gently point her back to the lesson (LL-15/24/25).
        if ($this->reteachMode === 'remediation') {
            $this->handleRemediationInput($question);

            return;
        }
        if ($this->reteachMode === 'final') {
            $this->handleFinalInput($question);

            return;
        }
        if ($this->reteach) {
            $this->reply(self::CONTINUE_LESSON_REPLY);

            return;
        }

        // Discretionary + budget-gated (AG-02): no budget → a kind rest message, no calls.
        if (! app(LlmBudget::class)->canSpend($studentId, false)) {
            $this->reply(self::OVER_BUDGET_REPLY);

            return;
        }

        $moderator = app(ChildSafetyModerator::class);

        // AG-12: screen the child's message BEFORE it reaches the tutor.
        if (! $moderator->moderate($question, $studentId)->safe) {
            $this->reply(self::UNSAFE_INPUT_REPLY);

            return;
        }

        $answer = app(LlmService::class)->chat(
            $this->conversation($question),
            maxTokens: 400,
            studentId: $studentId,
            essential: false,
        );

        // AG-13: screen the tutor's reply BEFORE it is shown.
        if (! $moderator->moderate($answer, $studentId)->safe) {
            $this->reply(self::UNSAFE_OUTPUT_REPLY);

            return;
        }

        $this->reply($answer);
    }

    private function reply(string $content): void
    {
        $this->messages[] = ['role' => 'assistant', 'content' => $content];

        // Nudge the UI to glow so she notices Smooth spoke and replies.
        $this->dispatch('smooth-spoke');
    }

    /**
     * The grounded, Socratic, profile-tailored prompt + the running history.
     *
     * @return array<int,array{role:string,content:string}>
     */
    private function conversation(string $question): array
    {
        $lesson = Lesson::where('module_id', $this->moduleId)->where('is_published', true)->first();
        $lessonText = collect($lesson?->blocks ?? [])
            ->pluck('content')
            ->filter()
            ->implode("\n");

        $profile = app(LearningProfile::class)->promptContext(auth()->user());

        $system = 'You are Smooth, a warm sea-turtle tutor helping a 10-to-11-year-old with the '
            ."Trinidad & Tobago SEA exam. You are helping ONLY with this lesson:\n"
            ."Topic: {$this->topic}\nLesson:\n{$lessonText}\n\n"
            .'Teach Socratically: give a small hint or a guiding question first to make her think, '
            .'then confirm once she is close. Keep every reply to 1–2 SHORT sentences, very simple '
            .'words a 10-year-old knows, warm, with one emoji. NEVER give away the answer to a '
            .'practice question. If she asks about anything outside this lesson, gently steer back to it. '
            .($profile !== '' ? "About her: {$profile}" : '');

        $history = [['role' => 'system', 'content' => $system]];
        foreach ($this->messages as $message) {
            $history[] = $message;
        }

        return $history;
    }

    public function render()
    {
        return view('livewire.clarify-chat');
    }
}
