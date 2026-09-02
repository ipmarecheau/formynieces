<?php

namespace App\Livewire;

use App\Livewire\Concerns\GatesFreePlan;
use App\Models\Lesson;
use App\Models\ReteachSession;
use App\Models\SyllabusModule;
use App\Services\Practice\Remediation;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * ReteachWalk — the PROVE stage of an AI-assisted re-teach (LL-15/LL-16).
 *
 * After re-walking the lesson, she shows she's got it: she TYPES the answer to a few more of the
 * lesson's OWN practice words (same rules as the lesson taught — never off-lesson bank content, LL-24).
 * Three correct proves understanding and resumes solo practice at D3 (LL-16).
 */
#[Layout('components.layouts.diagnostic')]
class ReteachWalk extends Component
{
    use GatesFreePlan;

    public int $moduleId;

    public string $topic;

    public string $subject;

    /** The current proof word: {prompt, answer, rule} drawn from the lesson's practice items. */
    public ?array $question = null;

    public string $typed = '';

    /** @var array{correct:bool, answer:string, rule:string}|null */
    public ?array $feedback = null;

    public int $proofsDone = 0;

    public int $proofTarget = ReteachSession::PROOF_TARGET;

    public function mount(SyllabusModule $module): void
    {
        // Free plan: Smooth's re-teach is behind the wall (FP-08).
        if ($this->gateFreePlan('reteach')) {
            return;
        }

        $session = app(Remediation::class)->activeSession(auth()->id(), $module->id);

        // Only reachable inside an open re-teach; otherwise back to the module entry.
        if ($session === null) {
            $this->redirectRoute('practice.enter', ['module' => $module->id]);

            return;
        }

        $this->moduleId = $module->id;
        $this->topic = $module->topic;
        $this->subject = $module->subject;
        $this->proofsDone = $session->correct_count;

        $this->loadQuestion();
    }

    /** Grade her typed answer; three correct resumes solo practice at D3 (LL-16). */
    public function submit(): void
    {
        if ($this->question === null || $this->feedback !== null) {
            return;
        }

        $correct = mb_strtolower(trim($this->typed)) === mb_strtolower(trim($this->question['answer']));
        $this->feedback = ['correct' => $correct, 'answer' => $this->question['answer'], 'rule' => $this->question['rule']];

        if (! $correct) {
            return;
        }

        $session = app(Remediation::class)->activeSession(auth()->id(), $this->moduleId);
        if ($session !== null) {
            $session = app(Remediation::class)->recordCorrectProof($session);
            $this->proofsDone = $session->correct_count;

            // Understanding proven — resume solo practice at D3 (LL-16).
            if ($session->isComplete()) {
                $this->redirectRoute('practice.walk', ['module' => $this->moduleId]);
            }
        }
    }

    public function nextQuestion(): void
    {
        $this->loadQuestion();
    }

    /** Load the next lesson practice word, advancing through the pool (wrapping) so words don't repeat back-to-back. */
    private function loadQuestion(): void
    {
        $items = $this->lessonItems();
        $this->feedback = null;
        $this->typed = '';

        if ($items === []) {
            $this->question = null;

            return;
        }

        $idx = 0;
        $current = $this->question['prompt'] ?? null;
        if ($current !== null) {
            $pos = array_search($current, array_column($items, 'prompt'), true);
            $idx = $pos === false ? 0 : ($pos + 1) % count($items);
        }

        $this->question = $items[$idx];
    }

    /**
     * The lesson's OWN practice items (all interactive blocks, flattened) — the proof stays coherent
     * with what the lesson taught, and gives additional same-rule words (LL-24).
     *
     * @return array<int, array{prompt:string, answer:string, rule:string}>
     */
    private function lessonItems(): array
    {
        $lesson = Lesson::where('module_id', $this->moduleId)->where('is_published', true)->first();

        $items = [];
        foreach ($lesson?->blocks ?? [] as $block) {
            foreach (array_values($block['practiceItems'] ?? []) as $it) {
                $items[] = [
                    'prompt' => (string) ($it['prompt'] ?? ''),
                    'answer' => (string) ($it['answer'] ?? ''),
                    'rule' => (string) ($block['rule'] ?? ''),
                ];
            }
        }

        return $items;
    }

    public function render()
    {
        return view('livewire.reteach-walk');
    }
}
