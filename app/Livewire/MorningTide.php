<?php

namespace App\Livewire;

use App\Livewire\Concerns\GatesFreePlan;
use App\Models\DailyReadingAssignment;
use App\Models\VocabularyWord;
use App\Services\Motivation\DailyPlanComposer;
use App\Services\Motivation\StreakEconomyService;
use App\Services\Reading\DailyReadingService;
use App\Services\Reading\VocabularyService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The Morning Tide — one guided ritual (DR + DV): read the passage, chart it
 * (comprehension, with a single re-read allowed), then choose two words to build
 * sentences with. Words she uses successfully progress toward mastery and rotate
 * out. Satisfies the single "morning_tide" duty. Feedback is always encouraging.
 */
#[Layout('components.layouts.diagnostic')]
class MorningTide extends Component
{
    use GatesFreePlan;

    /** read | check | pick | vocab | done */
    public string $phase = 'read';

    public ?int $assignmentId = null;

    public bool $noPassage = false;

    // Comprehension check
    public int $qIndex = 0;

    /** @var array<int,mixed> */
    public array $answers = [];

    public mixed $currentAnswer = null;

    public bool $showPassage = false;   // re-read reveal

    public bool $rereadUsed = false;    // the one allowed re-read

    public ?int $score = null;

    // Vocabulary (choose two, master-and-rotate)
    /** @var list<int> */
    public array $candidateIds = [];

    /** @var list<int> */
    public array $chosenIds = [];

    public int $vocabIndex = 0;

    public string $currentSentence = '';

    /** @var array<int,string> word id => her sentence */
    public array $vocabSentences = [];

    /** write | shown — within a word, show her the two examples after she submits. */
    public string $wordStage = 'write';

    /** @var list<string> */
    public array $currentExamples = [];

    private const WORDS_TO_CHOOSE = 2;

    public function mount(): void
    {
        if ($this->gateFreePlan('rituals')) {
            return;
        }

        $assignment = app(DailyReadingService::class)->serve(auth()->user());

        if ($assignment === null) {
            $this->noPassage = true;

            return;
        }

        $this->assignmentId = $assignment->id;

        if ($assignment->completed_at !== null) {
            $this->phase = 'done';
            $this->score = $assignment->comprehension_score;
        }
    }

    public function startCheck(): void
    {
        $this->phase = 'check';
        $this->qIndex = 0;
        $this->currentAnswer = null;
    }

    /** Reveal the passage once during the check — a single allowed re-read. */
    public function reread(): void
    {
        if (! $this->rereadUsed) {
            $this->showPassage = true;
            $this->rereadUsed = true;
        }
    }

    public function nextQuestion(): void
    {
        $this->answers[$this->qIndex] = $this->currentAnswer;
        $this->currentAnswer = null;

        $questions = $this->assignment()->passage->questions ?? [];
        if ($this->qIndex < count($questions) - 1) {
            $this->qIndex++;

            return;
        }

        // Score + keep it (LLM scoring lands in a later increment; MC auto-grade now).
        $scored = app(DailyReadingService::class)->score($this->assignment(), $this->answers);
        $this->score = $scored->comprehension_score;

        $this->candidateIds = app(VocabularyService::class)
            ->candidateWords(auth()->id(), $this->assignment()->passage)
            ->pluck('id')->all();

        if ($this->candidateIds === []) {
            $this->finish();

            return;
        }

        $this->phase = 'pick';
    }

    public function toggleChoose(int $wordId): void
    {
        if (in_array($wordId, $this->chosenIds, true)) {
            $this->chosenIds = array_values(array_diff($this->chosenIds, [$wordId]));
        } elseif (count($this->chosenIds) < self::WORDS_TO_CHOOSE) {
            $this->chosenIds[] = $wordId;
        }
    }

    public function startWriting(): void
    {
        if (count($this->chosenIds) === 0) {
            return;
        }
        $this->vocabIndex = 0;
        $this->currentSentence = '';
        $this->wordStage = 'write';
        $this->currentExamples = [];
        $this->phase = 'vocab';
    }

    /** She submits her sentence for the current word; record it and show examples. */
    public function submitSentence(): void
    {
        $wordId = $this->chosenIds[$this->vocabIndex] ?? null;
        if ($wordId === null) {
            return;
        }

        $word = VocabularyWord::find($wordId);
        if ($word !== null) {
            $correct = app(VocabularyService::class)->usedCorrectly($word->word, $this->currentSentence);
            app(VocabularyService::class)->recordResult(auth()->id(), $wordId, $correct);
            $this->vocabSentences[$wordId] = trim($this->currentSentence);
            $this->currentExamples = app(VocabularyService::class)->exampleSentences($word, auth()->id());
        }

        $this->wordStage = 'shown';
    }

    /** Move on from the examples to the next word, or finish the tide. */
    public function continueWord(): void
    {
        $this->currentSentence = '';
        $this->currentExamples = [];
        $this->wordStage = 'write';

        if ($this->vocabIndex < count($this->chosenIds) - 1) {
            $this->vocabIndex++;

            return;
        }

        $this->finish();
    }

    private function finish(): void
    {
        $studentId = (int) auth()->id();

        if ($this->vocabSentences !== [] && $this->assignmentId !== null) {
            $assignment = $this->assignment();
            $assignment->vocab_sentences = $this->vocabSentences;
            $assignment->save();
        }

        app(DailyPlanComposer::class)->markDuty($studentId, 'morning_tide');
        app(StreakEconomyService::class)->completeDailyMinimumIfMet($studentId);
        $this->phase = 'done';
    }

    /**
     * The honest, encouraging breakdown shown on the done screen (and stored for the
     * diary): each comprehension question with her answer vs the correct one, and
     * each word she built a sentence with.
     *
     * @return array{comprehension: list<array>, wordUsage: list<array>}
     */
    private function buildBreakdown(DailyReadingAssignment $assignment): array
    {
        $questions = $assignment->passage->questions ?? [];
        $answers = $assignment->answers ?? [];

        $comprehension = [];
        foreach ($questions as $i => $q) {
            if (($q['type'] ?? 'mc') === 'mc') {
                $her = $answers[$i] ?? null;
                $correctIndex = $q['correct_index'] ?? null;
                $comprehension[] = [
                    'prompt' => $q['prompt'] ?? '',
                    'type' => 'mc',
                    'yourAnswer' => $her !== null ? ($q['options'][$her] ?? '—') : '—',
                    'correctAnswer' => $correctIndex !== null ? ($q['options'][$correctIndex] ?? '') : '',
                    'correct' => $her !== null && (int) $her === (int) $correctIndex,
                ];
            } else {
                $comprehension[] = [
                    'prompt' => $q['prompt'] ?? '',
                    'type' => 'written',
                    'yourAnswer' => $answers[$i] ?? '',
                    'correct' => null,
                ];
            }
        }

        $wordUsage = [];
        foreach (($assignment->vocab_sentences ?? []) as $wordId => $sentence) {
            $word = VocabularyWord::find($wordId);
            if ($word === null) {
                continue;
            }
            $wordUsage[] = [
                'word' => $word->word,
                'sentence' => (string) $sentence,
                'used' => app(VocabularyService::class)->usedCorrectly($word->word, (string) $sentence),
                'example' => $word->context_sentence,
            ];
        }

        return ['comprehension' => $comprehension, 'wordUsage' => $wordUsage];
    }

    /** Honest but never-defeating message, scaled to the comprehension score. */
    private function messageFor(?int $score): string
    {
        return match (true) {
            $score === null => 'Let\'s look back at your voyage together. 🌊',
            $score >= 95 => 'A brilliant reading, Captain! Treasure earned. 🏆',
            $score >= 75 => 'Strong reading today — well sailed! 🌟',
            $score >= 50 => 'Good sailing! A few to tighten up — see below. ⛵',
            $score >= 1 => 'A brave first voyage. Let\'s see what to notice next time. 🧭',
            default => 'Reading gets easier every day — let\'s look at these together. 🌱',
        };
    }

    private function assignment(): DailyReadingAssignment
    {
        return DailyReadingAssignment::with('passage')->findOrFail($this->assignmentId);
    }

    /** @return Collection<int,VocabularyWord> */
    private function words(array $ids): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return VocabularyWord::whereIn('id', $ids)->get()
            ->sortBy(fn ($w) => array_search($w->id, $ids))->values();
    }

    public function render(): View
    {
        $assignment = $this->assignmentId !== null ? $this->assignment() : null;

        return view('livewire.morning-tide', [
            'passage' => $assignment?->passage,
            'questions' => $assignment?->passage->questions ?? [],
            'candidates' => $this->phase === 'pick' ? $this->words($this->candidateIds) : collect(),
            'currentWord' => $this->phase === 'vocab' ? $this->words($this->chosenIds)[$this->vocabIndex] ?? null : null,
            'chosenTotal' => count($this->chosenIds),
            'breakdown' => $this->phase === 'done' && $assignment !== null ? $this->buildBreakdown($assignment) : null,
            'message' => ($assignment?->comprehension_feedback) ?: $this->messageFor($this->score),
        ]);
    }
}
