<?php

namespace App\Livewire;

use App\Services\LlmBudget;
use App\Services\LlmService;
use App\Services\Safety\ChildSafetyModerator;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Smooth's guide + companion — the child's one turtle on every student screen (SG-01..05).
 *
 * Two abilities, one turtle:
 *  - A contextual how-to that auto-opens on a first visit and never nags again (config/guides.php,
 *    persisted per student in users.seen_guides). Child-layer only — never pace/percentages/targets.
 *  - "Ask Smooth anything": a free, budget-gated, child-safety-screened chat grounded as a warm SEA
 *    guide (never hands over answers). History is ephemeral (component state) — no transcript stored.
 *
 * Focus-lock: a screen mid-focus (the diagnostic, a language assessment) dispatches `smooth:lock`;
 * the chat greys out until `smooth:unlock`, so the child isn't distracted while they need to focus.
 */
class SmoothGuide extends Component
{
    /** The guide key, e.g. "practice" or "voyage". */
    public string $guide;

    public bool $open = false;

    /** An alert guide (e.g. a due review) opens proactively, regardless of prior dismissal. */
    public bool $alert = false;

    /** Whether the free "ask me anything" chat is offered on this placement. */
    public bool $chat = true;

    /** True while the ask-anything panel is open. */
    public bool $chatOpen = false;

    /** True while a focus moment has locked the chat (set by smooth:lock). */
    public bool $locked = false;

    /** @var array<int,array{role:string,content:string}> Ephemeral companion chat history. */
    public array $messages = [];

    public string $draft = '';

    private const OVER_BUDGET_REPLY = "I need a little rest right now — but you've got this! Ask me again a bit later. 🐢";

    private const UNSAFE_INPUT_REPLY = "Let's keep our chat about your learning — ask me about your lessons, your Voyage, or how anything works! 🐢";

    private const UNSAFE_OUTPUT_REPLY = 'Hmm, let me find a better way to say that. Ask me again? 🐢';

    private const POSE_FILES = [
        'wave' => 'smooth.webp',
        'cheer' => 'smooth-cheer.webp',
        'chart' => 'smooth-chart.webp',
    ];

    public function mount(string $guide, bool $alert = false, bool $chat = true, bool $locked = false): void
    {
        $this->guide = $guide;
        $this->alert = $alert;
        $this->chat = $chat;
        $this->locked = $locked;
        // An alert (a due review) always opens; otherwise first visit auto-opens and a
        // previously dismissed guide stays closed (SG-01/02).
        $this->open = $alert || ! (auth()->user()?->hasSeenGuide($guide) ?? true);
    }

    /** Dismiss and remember, so it never nags again (SG-02). */
    public function dismiss(): void
    {
        auth()->user()?->markGuideSeen($this->guide);
        $this->open = false;
    }

    /** Reopen on demand from the help control (SG-02). */
    public function reopen(): void
    {
        $this->open = true;
    }

    /** Open the ask-anything chat panel (never while a focus moment holds the lock). */
    public function openChat(): void
    {
        if ($this->locked || ! $this->chat) {
            return;
        }
        $this->chatOpen = true;
    }

    public function closeChat(): void
    {
        $this->chatOpen = false;
    }

    /** A focus moment (assessment/diagnostic) asks Smooth to wait quietly. */
    #[On('smooth:lock')]
    public function lockChat(): void
    {
        $this->locked = true;
        $this->chatOpen = false;
    }

    /** The focus moment is over — Smooth is available again. */
    #[On('smooth:unlock')]
    public function unlockChat(): void
    {
        $this->locked = false;
    }

    /**
     * Send the child's message to Smooth: budget-gated, child-safety screened both ways, and
     * grounded as a warm guide that never hands over answers (AG-02/12/13). No transcript is stored.
     */
    public function sendToSmooth(): void
    {
        if ($this->locked || ! $this->chat) {
            return;
        }

        $question = trim($this->draft);
        if ($question === '') {
            return;
        }

        $studentId = auth()->id();
        $this->messages[] = ['role' => 'user', 'content' => $question];
        $this->draft = '';

        // Discretionary + budget-gated (AG-02): no budget → a kind rest message, no calls.
        if (! app(LlmBudget::class)->canSpend($studentId, false)) {
            $this->companionReply(self::OVER_BUDGET_REPLY);

            return;
        }

        $moderator = app(ChildSafetyModerator::class);

        // Screen the child's message BEFORE it reaches Smooth (AG-12).
        if (! $moderator->moderate($question, $studentId)->safe) {
            $this->companionReply(self::UNSAFE_INPUT_REPLY);

            return;
        }

        $answer = app(LlmService::class)->chat(
            $this->companionConversation(),
            maxTokens: 300,
            studentId: $studentId,
            essential: false,
        );

        // Screen Smooth's reply BEFORE it is shown (AG-13).
        if (! $moderator->moderate($answer, $studentId)->safe) {
            $this->companionReply(self::UNSAFE_OUTPUT_REPLY);

            return;
        }

        $this->companionReply($answer);
    }

    private function companionReply(string $content): void
    {
        if (trim($content) === '') {
            return;
        }

        $this->messages[] = ['role' => 'assistant', 'content' => $content];
    }

    /**
     * @return array<int,array{role:string,content:string}>
     */
    private function companionConversation(): array
    {
        $system = <<<'PROMPT'
        You are Smooth, a friendly, encouraging sea-turtle guide for a child (aged 9-11) preparing for
        the SEA exam on the SmoothSeas app. Speak warmly and simply, in 2-4 short sentences. You may
        explain how the app works (the Voyage map, islands, lessons, practice) and cheer them on. NEVER
        give away the answer to a test, quiz, or practice question — instead ask a gentle question that
        helps them think it through themselves. Keep everything kind, safe, and age-appropriate.
        PROMPT;

        $conversation = [['role' => 'system', 'content' => $system]];

        // Only the recent turns — history is ephemeral and short by design.
        foreach (array_slice($this->messages, -8) as $message) {
            $conversation[] = $message;
        }

        return $conversation;
    }

    /**
     * @return array{title:string, pose:string, lines:array<int,string>}|null
     */
    public function content(): ?array
    {
        return config("guides.{$this->guide}");
    }

    public function avatarUrl(string $pose): string
    {
        $file = self::POSE_FILES[$pose] ?? self::POSE_FILES['wave'];

        return asset("images/voyage/companion/{$file}");
    }

    public function render()
    {
        return view('livewire.smooth-guide');
    }
}
