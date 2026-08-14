<?php

namespace App\Livewire;

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Services\Practice\LearningGate;
use App\Services\Practice\PracticeQuestions;
use App\Services\Practice\QuestionExposure;
use App\Services\Practice\RecordPracticeAttempt;
use App\Services\Practice\Remediation;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.diagnostic')]
class PracticeWalk extends Component
{
    public int $moduleId;

    public string $topic;

    public int $currentRung = 1;

    /** Display ordinal 1..3 for the difficulty rung (1->1, 3->2, 5->3). */
    public int $rungOrdinal = 1;

    public int $currentStreak = 0;

    public bool $isMastered = false;

    /** Attempts used on the current question (a student gets two). */
    public int $attemptsUsed = 0;

    /** True after a first-try miss, while she takes her second attempt. */
    public bool $awaitingRetry = false;

    /** After a re-teach trigger: show the warm hand-off splash before routing to the lesson (LL-14). */
    public bool $reteachSplash = false;

    public ?array $question = null;

    public ?array $feedback = null;

    /** A milestone celebration to play over the flow: ['type'=>'levelup'|'mastery', ...]. */
    public ?array $celebration = null;

    public function mount(SyllabusModule $module): void
    {
        $this->moduleId = $module->id;
        $this->topic = $module->topic;

        // Sequence gate (LE-03): practice stays locked until she has finished the worked examples.
        // Send her back to the module entry with a kind message (LE-06) rather than in.
        $gate = app(LearningGate::class);
        if (! $gate->practiceUnlocked(auth()->id(), $module->id)) {
            session()->flash('lockMessage', $gate->lockMessage('practice'));
            $this->redirectRoute('practice.enter', ['module' => $module->id]);

            return;
        }

        $progress = StudentProgress::query()
            ->where('student_id', auth()->id())
            ->where('module_id', $module->id)
            ->first();

        $this->currentRung = $progress->current_rung ?? 1;
        $this->rungOrdinal = $this->ordinalFor($this->currentRung);
        $this->currentStreak = $progress->current_streak ?? 0;
        $this->isMastered = ($progress->status ?? null) === 'mastered';

        $this->loadQuestion();
    }

    private function loadQuestion(): void
    {
        if ($this->isMastered) {
            $this->question = null;   // mastered: nothing to serve, celebration shows

            return;
        }

        $questions = app(PracticeQuestions::class)->forModule($this->moduleId);

        $usedInStreak = StudentProgress::query()
            ->where('student_id', auth()->id())
            ->where('module_id', $this->moduleId)
            ->value('streak_question_ids') ?? [];
        if (is_string($usedInStreak)) {
            $usedInStreak = json_decode($usedInStreak, true) ?: [];
        }

        // Global no-repeat: never serve a question this student has already seen
        // anywhere in the loop (LL-18), on top of the within-streak distinctness.
        $exposure = app(QuestionExposure::class);
        $seenHashes = $exposure->seenHashes(auth()->id());

        $atRung = $questions
            ->where('difficulty', $this->currentRung)
            ->first(fn ($q) => ! in_array($q->id, $usedInStreak, true)
                && ! in_array($q->content_hash, $seenHashes, true));

        if ($atRung !== null) {
            $exposure->record(auth()->id(), $atRung->content_hash, 'practice');
        }

        $this->question = $atRung === null ? null : [
            'id' => $atRung->id,
            'prompt' => $atRung->prompt,
            'options' => $atRung->options,
            'correct_index' => $atRung->correct_index,
            'explanation' => $atRung->explanation,
        ];
    }

    public function choose(int $chosenIndex): void
    {
        if ($this->question === null || $this->feedback !== null) {
            return;
        }

        $this->attemptsUsed++;
        $wasCorrect = $chosenIndex === $this->question['correct_index'];
        $priorRung = $this->currentRung;
        $wasMastered = $this->isMastered;

        $progress = app(RecordPracticeAttempt::class)
            ->handle(auth()->id(), $this->question['id'], $chosenIndex, $this->attemptsUsed);

        $this->currentRung = $progress->current_rung;
        $this->rungOrdinal = $this->ordinalFor($this->currentRung);
        $this->currentStreak = $progress->current_streak;
        $this->isMastered = $progress->status === 'mastered';

        // A first-try miss earns a second attempt before the explanation is revealed —
        // framed as "not yet," never failure.
        if (! $wasCorrect && $this->attemptsUsed < 2) {
            $this->awaitingRetry = true;

            return;
        }

        $this->awaitingRetry = false;

        // A bad-enough run (two hard misses in a row at D3/D5, or five of the last seven) pulls her
        // into an AI-assisted re-teach — kindly, never framed as failure (LL-14/LL-22).
        if (! $wasCorrect) {
            $remediation = app(Remediation::class);
            if ($trigger = $remediation->triggerFor(auth()->id(), $this->moduleId)) {
                $remediation->start(auth()->id(), $this->moduleId, $trigger);
                // Don't snap straight into the lesson — show a warm hand-off splash first so the
                // switch never feels abrupt (LL-14). She taps through when she's ready, and the
                // re-teach then re-walks the real interactive lesson (LessonWalk sees the open
                // session and leads on to the D1 proof afterwards).
                $this->reteachSplash = true;

                return;
            }
        }

        // CE-02/03: a milestone this answer just crossed plays a big celebration
        // instead of the plain feedback screen.
        if ($this->isMastered && ! $wasMastered) {
            $this->celebration = [
                'type' => 'mastery',
                'title' => 'You mastered it! 🎉',
                'sub' => "You climbed all three levels of {$this->topic}. Brilliant work, explorer!",
            ];

            return;
        }
        if ($this->currentRung > $priorRung) {
            $this->celebration = [
                'type' => 'levelup',
                'title' => 'Level up! ⭐',
                'sub' => 'You cleared Level '.($this->rungOrdinal - 1)." — on to Level {$this->rungOrdinal}!",
            ];

            return;
        }

        $this->feedback = [
            'correct' => $wasCorrect,
            'explanation' => $this->question['explanation'] ?? '',
            'mastered' => $this->isMastered,   // so the feedback screen can announce it
        ];
    }

    /** Dismiss a level-up celebration and continue to the next question (CE-02). */
    public function continueAfterCelebration(): void
    {
        $this->celebration = null;
        $this->next();
    }

    public function next(): void
    {
        $this->feedback = null;
        $this->awaitingRetry = false;
        $this->attemptsUsed = 0;
        $this->loadQuestion();
    }

    /** Leave the re-teach hand-off splash and begin re-walking the real interactive lesson (LL-14). */
    public function enterReteach(): void
    {
        $this->redirectRoute('practice.lesson', ['module' => $this->moduleId]);
    }

    /** Map a difficulty rung (1/3/5) to its display ordinal (1/2/3). */
    private function ordinalFor(int $rung): int
    {
        return match ($rung) {
            3 => 2,
            5 => 3,
            default => 1,
        };
    }

    public function render()
    {
        return view('livewire.practice-walk');
    }
}
