<?php

namespace App\Livewire;

use App\Models\Lesson;
use App\Models\PracticeQuestion;
use App\Models\ReteachSession;
use App\Models\SyllabusModule;
use App\Services\Practice\PracticeQuestions;
use App\Services\Practice\Remediation;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * ReteachWalk — the AI-assisted re-teach a student is pulled into after struggling (LL-14…16, 22).
 *
 * Two soft phases: RELEARN re-walks the lesson with the clarify chat alongside (it asks reinforcing
 * questions about each block — soft, never a gate; she advances freely). Then PROVE serves D1
 * questions; the teacher chat can expand a missed one toward the principle, and if the AI runs out
 * of budget she can drop back to the worked examples (LL-15). Three correct proves understanding and
 * resumes her at D3 (LL-16). Never framed as failure.
 */
#[Layout('components.layouts.diagnostic')]
class ReteachWalk extends Component
{
    public int $moduleId;

    public string $topic;

    public string $subject;

    /** relearn | prove */
    public string $phase = 'relearn';

    /** The lesson blocks re-walked in the relearn phase. */
    public array $lessonBlocks = [];

    public ?string $lessonTitle = null;

    public int $revealed = 1;

    /** The current D1 proof question, display-safe (no correct_index leaks to the client). */
    public ?array $question = null;

    public ?array $feedback = null;

    public int $proofsDone = 0;

    public int $proofTarget = ReteachSession::PROOF_TARGET;

    /** True after a missed proof — the teacher chat is offered to expand the solution (LL-15). */
    public bool $teacherOffered = false;

    public function mount(SyllabusModule $module): void
    {
        // Only reachable inside an open re-teach; otherwise back to the module entry.
        if (app(Remediation::class)->activeSession(auth()->id(), $module->id) === null) {
            $this->redirectRoute('practice.enter', ['module' => $module->id]);

            return;
        }

        $this->moduleId = $module->id;
        $this->topic = $module->topic;
        $this->subject = $module->subject;
        $this->proofsDone = app(Remediation::class)->activeSession(auth()->id(), $module->id)->correct_count;

        $lesson = Lesson::where('module_id', $module->id)->where('is_published', true)->first();
        $this->lessonTitle = $lesson?->title;
        $this->lessonBlocks = $lesson?->blocks ?? [];
    }

    public function nextBlock(): void
    {
        if ($this->revealed < count($this->lessonBlocks)) {
            $this->revealed++;
        }
    }

    public function startProving(): void
    {
        $this->phase = 'prove';
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
