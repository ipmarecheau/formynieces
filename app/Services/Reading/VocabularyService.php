<?php

namespace App\Services\Reading;

use App\Models\ReadingPassage;
use App\Models\VocabularyReview;
use App\Models\VocabularyWord;
use App\Services\LlmService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * VocabularyService — the day's vocabulary, drawn from that morning's passage and
 * carried on a spaced-repetition schedule (DV).
 *
 * Today's set = words due for review (DV-03) plus new words from the passage
 * (DV-01), capped so the whole Morning Tide stays a ~15-minute ritual. A word she
 * keeps getting right returns less often; one she misses returns sooner.
 */
class VocabularyService
{
    public const DAILY_CAP = 6;

    /** Successful uses at which a word is considered mastered and rotates out. */
    public const MASTERY_STREAK = 3;

    private const MAX_INTERVAL = 30;

    public function __construct(private LlmService $llm) {}

    /**
     * The words she may choose to build a sentence with today: due (not-yet-mastered)
     * reviews plus new words from the passage, mastered words rotated out, capped so
     * she picks from a small set (she then chooses two).
     *
     * @return Collection<int,VocabularyWord>
     */
    public function candidateWords(int $studentId, ReadingPassage $passage, ?Carbon $on = null): Collection
    {
        $on ??= Carbon::today();

        $mastered = VocabularyReview::where('student_id', $studentId)
            ->where('correct_streak', '>=', self::MASTERY_STREAK)->pluck('word_id');

        $due = VocabularyReview::where('student_id', $studentId)
            ->where('correct_streak', '<', self::MASTERY_STREAK)
            ->whereDate('due_at', '<=', $on->toDateString())
            ->with('word')->get()->pluck('word')->filter();

        $reviewedIds = VocabularyReview::where('student_id', $studentId)->pluck('word_id');
        $new = $passage->vocabularyWords()->whereNotIn('id', $reviewedIds)->get();

        return $due->concat($new)
            ->reject(fn ($w) => $mastered->contains($w->id))
            ->unique('id')->take(5)->values();
    }

    /**
     * Did her sentence actually use the word? (Fallback check; the LLM can judge
     * this more richly later.)
     */
    public function usedCorrectly(string $word, string $sentence): bool
    {
        return str_contains(mb_strtolower($sentence), mb_strtolower(trim($word)));
    }

    /**
     * Two example sentences for a word, shown after her own attempt. LLM-first;
     * baseline fallback is the authored context sentence (one model example).
     *
     * @return list<string>
     */
    public function exampleSentences(VocabularyWord $word, ?int $studentId = null): array
    {
        $result = $this->llm->completeJson(
            'You are a warm primary teacher. Give two simple, correct example sentences using the given '
            .'word, for a Standard 5 pupil (age ~10). Return JSON only: {"examples": ["...", "..."]}.',
            "Word: {$word->word}\nMeaning: {$word->definition}",
            200,
            $studentId,
            essential: false,
        );

        $examples = $result['examples'] ?? null;
        if (is_array($examples)) {
            $examples = array_values(array_filter(array_map(fn ($e) => trim((string) $e), $examples)));
            if (count($examples) >= 1) {
                return array_slice($examples, 0, 2);
            }
        }

        return array_values(array_filter([$word->context_sentence]));
    }

    /**
     * The word set for today: due reviews first, then new words from the passage,
     * de-duplicated and capped.
     *
     * @return Collection<int,VocabularyWord>
     */
    public function wordsForToday(int $studentId, ReadingPassage $passage, ?Carbon $on = null): Collection
    {
        $on ??= Carbon::today();

        $due = VocabularyReview::where('student_id', $studentId)
            ->whereDate('due_at', '<=', $on->toDateString())
            ->with('word')->get()
            ->pluck('word')->filter();

        $reviewedIds = VocabularyReview::where('student_id', $studentId)->pluck('word_id');
        $new = $passage->vocabularyWords()->whereNotIn('id', $reviewedIds)->get();

        return $due->concat($new)->unique('id')->take(self::DAILY_CAP)->values();
    }

    /**
     * DV-06 — the due words she has already met that appear in THIS passage's text,
     * so a later comprehension check can ask her to use or interpret one in the new
     * context. Scan-based: a due word whose spelling occurs in the passage body
     * (case-insensitive). Only words already in her review schedule qualify — these
     * are reinforcement, never freshly-introduced words.
     *
     * @return Collection<int,VocabularyWord>
     */
    public function dueWordsInPassage(int $studentId, ReadingPassage $passage, ?Carbon $on = null): Collection
    {
        $on ??= Carbon::today();
        $body = (string) $passage->body;

        return VocabularyReview::where('student_id', $studentId)
            ->whereDate('due_at', '<=', $on->toDateString())
            ->with('word')->get()
            ->pluck('word')->filter()
            ->filter(fn (VocabularyWord $word): bool => stripos($body, (string) $word->word) !== false)
            ->values();
    }

    /**
     * DV-06 — she used or interpreted a due word correctly inside a later passage's
     * comprehension. This counts toward retaining it, feeding the spaced schedule
     * exactly like a review — it is reinforcement, never a scored grade.
     */
    public function reinforceInContext(int $studentId, int $wordId, bool $correct, ?Carbon $on = null): VocabularyReview
    {
        return $this->recordResult($studentId, $wordId, $correct, $on);
    }

    /**
     * Record how she did on a word and reschedule it (DV-03). Correct → the interval
     * roughly doubles (capped); wrong → it resets to tomorrow.
     */
    public function recordResult(int $studentId, int $wordId, bool $correct, ?Carbon $on = null): VocabularyReview
    {
        $on ??= Carbon::today();

        $review = VocabularyReview::firstOrNew([
            'student_id' => $studentId,
            'word_id' => $wordId,
        ]);
        $review->interval_days ??= 1;
        $review->correct_streak ??= 0;

        if ($correct) {
            $review->correct_streak += 1;
            $review->interval_days = min(max($review->interval_days * 2, 1), self::MAX_INTERVAL);
        } else {
            $review->correct_streak = 0;
            $review->interval_days = 1;
        }

        $review->last_seen_at = $on->toDateString();
        $review->due_at = $on->copy()->addDays($review->interval_days)->toDateString();
        $review->save();

        return $review->fresh();
    }
}
