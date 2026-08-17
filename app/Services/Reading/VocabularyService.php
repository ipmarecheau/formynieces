<?php

namespace App\Services\Reading;

use App\Models\ReadingPassage;
use App\Models\VocabularyReview;
use App\Models\VocabularyWord;
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
