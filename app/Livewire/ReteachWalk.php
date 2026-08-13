<?php

namespace App\Livewire;

use App\Models\PracticeQuestion;
use App\Models\ReteachSession;
use App\Models\SyllabusModule;
use App\Services\Practice\PracticeQuestions;
use App\Services\Practice\Remediation;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * ReteachWalk — the PROVE stage of an AI-assisted re-teach (LL-15/LL-16).
 *
 * The relearn stage is the real interactive lesson, re-walked with Smooth reinforcing each block
 * (handled in LessonWalk's re-teach mode). She arrives here afterwards to show she's got it: D1
 * questions, with the teacher chat to expand a missed one and a worked-examples escape if the AI
 * budget runs out (LL-15). Three correct proves understanding and resumes solo practice at D3 (LL-16).
 */
#[Layout('components.layouts.diagnostic')]
class ReteachWalk extends Component
{
    public int $moduleId;

    public string $topic;

    public string $subject;

    /** The current D1 proof question, display-safe (no correct_index leaks to the client). */
    public ?array $question = null;

    public ?array $feedback = null;

    public int $proofsDone = 0;

    public int $proofTarget = ReteachSession::PROOF_TARGET;

    /** True after a missed proof — the teacher chat is offered to expand the solution (LL-15). */
    public bool $teacherOffered = false;

    public function mount(SyllabusModule $module): void
    {
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

    public function choose(int $chosenIndex): void
    {
        if ($this->question === null || $this->feedback !== null) {
            return;
        }

        $question = PracticeQuestion::find($this->question['id']);
        $correct = $question !== null && $chosenIndex === $question->correct_index;
        $this->feedback = ['correct' => $correct, 'explanation' => $question->explanation ?? ''];

        if (! $correct) {
            $this->teacherOffered = true;   // LL-15: expand the solution with the teacher chat

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

    private function loadQuestion(): void
    {
        $d1 = app(PracticeQuestions::class)->forModule($this->moduleId)->where('difficulty', 1)->values();

        $current = $this->question['id'] ?? null;
        $next = $d1->firstWhere('id', '!=', $current) ?? $d1->first();

        $this->question = $next === null ? null : [
            'id' => $next->id,
            'prompt' => strip_tags((string) $next->prompt),
            'options' => $next->options,
        ];
        $this->feedback = null;
        $this->teacherOffered = false;
    }

    public function render()
    {
        return view('livewire.reteach-walk');
    }
}
