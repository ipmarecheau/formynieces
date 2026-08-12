<?php

namespace App\Livewire;

use App\Models\Lesson;
use App\Models\SyllabusModule;
use App\Services\LearningProfile;
use App\Services\LlmBudget;
use App\Services\LlmService;
use App\Services\Safety\ChildSafetyModerator;
use Livewire\Component;

/**
 * ClarifyChat (LE-04) — the Socratic tutor beside the lesson.
 *
 * It answers questions about THIS lesson only, pushing understanding with a hint or
 * guiding question before confirming, never handing over practice answers. It is:
 *  - grounded in the lesson text and tailored by her learning profile (AG-08),
 *  - discretionary + budget-gated (AG-02), guided-time is counted by the lesson page,
 *  - screened both ways by the child-safety moderator (AG-12/13) — unsafe input is never
 *    forwarded, unsafe output is never shown, and it fails closed.
 *
 * History is ephemeral (component state) — no transcript is stored.
 */
class ClarifyChat extends Component
{
    public int $moduleId;

    public string $topic = '';

    /** @var array<int,array{role:string,content:string}> */
    public array $messages = [];

    public string $draft = '';

    public bool $thinking = false;

    private const UNSAFE_INPUT_REPLY = "Let's keep our chat about the lesson 🐢 — which part can I help explain?";

    private const UNSAFE_OUTPUT_REPLY = 'Hmm, let me put that a better way — try asking me about a specific step in the lesson!';

    private const OVER_BUDGET_REPLY = "Smooth's chat is taking a little rest for now — keep going with the lesson and practice, you've got this! 🌊";

    public function mount(int $moduleId): void
    {
        $module = SyllabusModule::findOrFail($moduleId);
        $this->moduleId = $module->id;
        $this->topic = $module->topic;
    }

    public function send(): void
    {
        $question = trim($this->draft);
        if ($question === '') {
            return;
        }

        $studentId = auth()->id();
        $this->messages[] = ['role' => 'user', 'content' => $question];
        $this->draft = '';

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
            .'then confirm once she is close. Keep replies short, warm and simple. NEVER give away '
            .'the answer to a practice question. If she asks about anything outside this lesson, '
            .'gently steer back to it. '
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
