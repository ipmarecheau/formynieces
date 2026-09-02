<?php

namespace App\Livewire;

use App\Livewire\Concerns\GatesFreePlan;
use App\Models\ModuleStageCompletion;
use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use App\Services\GuidedTime;
use App\Services\LlmService;
use App\Services\Practice\LearningGate;
use App\Services\Practice\PracticeQuestions;
use App\Services\Practice\QuestionExposure;
use App\Services\Practice\WorkedExampleGenerator;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * TutorialWalk — the tutorial stage of the loop (TU-01..04).
 *
 * Smooth walks the student through THREE worked examples, tutorial-style: he reveals
 * each solution a step at a time, then hands her the wheel — she predicts the answer,
 * and Smooth reacts in a warm, lightly AI-written line before showing the result. It
 * is NEVER scored (no practice attempt, no progress change) and can be revisited
 * freely. Once all three are done she moves on to practise.
 */
#[Layout('components.layouts.diagnostic')]
class TutorialWalk extends Component
{
    use GatesFreePlan;

    /** How many worked examples make up the tutorial. */
    private const EXAMPLE_COUNT = 3;

    public int $moduleId;

    public string $topic;

    /**
     * @var array<int, array{problem:string, steps:list<string>, options:list<string>, correctIndex:int, answer:string}>
     */
    public array $examples = [];

    public int $exampleIndex = 0;

    /** Steps of the current example revealed so far (the final step stays hidden until she predicts). */
    public int $revealed = 1;

    /** 'walk' (revealing steps) | 'predict' (her turn to guess) | 'reveal' (answer + Smooth's reaction) | 'done'. */
    public string $phase = 'walk';

    public ?int $picked = null;

    public bool $pickedCorrect = false;

    public string $remark = '';

    /** True once her 2-hour daily guided pool is spent — the tutorial locks for the day (AG-06). */
    public bool $guidedLocked = false;

    public function mount(SyllabusModule $module): void
    {
        // Free plan: the worked examples are behind the wall (FP-05).
        if ($this->gateFreePlan('tutorial')) {
            return;
        }

        $this->moduleId = $module->id;
        $this->topic = $module->topic;

        // Sequence gate (LE-03): worked examples stay locked until she has finished the lesson.
        $gate = app(LearningGate::class);
        if (! $gate->workedExamplesUnlocked(auth()->id(), $module->id)) {
            session()->flash('lockMessage', $gate->lockMessage(ModuleStageCompletion::STAGE_TUTORIAL));
            $this->redirectRoute('practice.enter', ['module' => $module->id]);

            return;
        }

        // Lock before any worked-example generation, so a spent pool costs nothing.
        $this->guidedLocked = app(GuidedTime::class)->isExhausted(auth()->id());
        if ($this->guidedLocked) {
            return;
        }

        $this->examples = $this->buildExamples($module->id);

        // If nothing could be built, there is no tutorial content to gate on.
        if ($this->examples === []) {
            return;
        }

        // A worked example with a single step is fully shown on arrival — go straight to her turn.
        if (count($this->currentExample()['steps']) <= 1) {
            $this->phase = 'predict';
        }
    }

    /**
     * Pick up to three easiest-rung (D1) questions and build a walkable worked example
     * for each. Tutorials may recycle (never scored), so there is always something to teach.
     *
     * @return array<int, array{problem:string, steps:list<string>, options:list<string>, correctIndex:int, answer:string}>
     */
    private function buildExamples(int $moduleId): array
    {
        $pool = app(PracticeQuestions::class)->forModule($moduleId)->where('difficulty', 1)->values();
        $exposure = app(QuestionExposure::class);
        $generator = app(WorkedExampleGenerator::class);

        $examples = [];
        $usedIds = [];
        for ($i = 0; $i < self::EXAMPLE_COUNT; $i++) {
            $candidates = $pool->reject(fn (PracticeQuestion $q) => in_array($q->id, $usedIds, true))->values();
            // Once the fresh pool is exhausted, recycle so we still reach three examples.
            $question = $exposure->pickUnseen(auth()->id(), $candidates->isNotEmpty() ? $candidates : $pool, allowRecycle: true);
            if ($question === null) {
                break;
            }
            $usedIds[] = $question->id;
            $exposure->record(auth()->id(), $question->content_hash, 'tutorial');

            $options = array_values(array_map(fn ($o) => (string) $o, $question->options ?? []));
            $correctIndex = (int) $question->correct_index;

            $examples[] = [
                'problem' => strip_tags((string) $question->prompt),
                'steps' => $generator->forQuestion($question, auth()->user())->steps,
                'options' => $options,
                'correctIndex' => $correctIndex,
                'answer' => $options[$correctIndex] ?? '',
            ];
        }

        return $examples;
    }

    /**
     * @return array{problem:string, steps:list<string>, options:list<string>, correctIndex:int, answer:string}
     */
    public function currentExample(): array
    {
        return $this->examples[$this->exampleIndex];
    }

    /** Reveal the next step; when only the final (answer) step remains, hand her the wheel. */
    public function nextStep(): void
    {
        $steps = $this->currentExample()['steps'];
        if ($this->revealed < count($steps) - 1) {
            $this->revealed++;

            return;
        }

        $this->phase = 'predict';
    }

    /** Her turn — she predicts the answer (never scored); Smooth reacts warmly, then reveals it. */
    public function predict(int $index): void
    {
        if ($this->phase !== 'predict') {
            return;
        }

        $this->picked = $index;
        $this->pickedCorrect = $index === $this->currentExample()['correctIndex'];
        $this->revealed = count($this->currentExample()['steps']); // show the final step now
        $this->remark = $this->smoothRemark($this->currentExample(), $index, $this->pickedCorrect);
        $this->phase = 'reveal';
    }

    /** Move to the next worked example, or finish the tutorial after the third. */
    public function continueExample(): void
    {
        if ($this->exampleIndex < count($this->examples) - 1) {
            $this->exampleIndex++;
            $this->revealed = 1;
            $this->phase = count($this->currentExample()['steps']) <= 1 ? 'predict' : 'walk';
            $this->picked = null;
            $this->remark = '';

            return;
        }

        $this->phase = 'done';
        app(LearningGate::class)->markCompleted(
            auth()->id(),
            $this->moduleId,
            ModuleStageCompletion::STAGE_TUTORIAL,
        );
    }

    /**
     * A short, warm, conversational line from Smooth about her guess — a touch of AI,
     * always with a safe fallback so it works with no LLM budget.
     *
     * @param  array{problem:string, answer:string}  $example
     */
    private function smoothRemark(array $example, int $picked, bool $correct): string
    {
        $guess = $this->currentExample()['options'][$picked] ?? '';

        try {
            $line = trim(app(LlmService::class)->complete(
                'You are Smooth, a friendly turtle tutor for a 10-year-old. Reply with ONE short, warm sentence (max 14 words) reacting to her guess. Never scold. No preamble.',
                "Problem: {$example['problem']}\nCorrect answer: {$example['answer']}\nHer guess: {$guess} (".($correct ? 'correct' : 'not correct').')',
                maxTokens: 40,
                studentId: auth()->id(),
            ));

            if ($line !== '' && ! str_contains(strtolower($line), 'unable to generate')) {
                return rtrim($line, '.').' 🐢';
            }
        } catch (\Throwable) {
            // fall through to the safe line
        }

        return $correct
            ? "Yes — that's exactly it! 🎉"
            : "Good thinking! Let's see how it works out together 🐢";
    }

    public function render()
    {
        return view('livewire.tutorial-walk');
    }
}
