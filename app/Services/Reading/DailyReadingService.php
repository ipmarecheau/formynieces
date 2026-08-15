<?php

namespace App\Services\Reading;

use App\Models\DailyReadingAssignment;
use App\Models\ReadingPassage;
use App\Models\User;
use App\Services\Motivation\StreakEconomyService;
use App\Services\Motivation\StreakService;
use Illuminate\Support\Carbon;

/**
 * DailyReadingService — serves the daily passage, scores comprehension, tracks
 * reading pace, adapts the reading level, and rewards progress (DR).
 *
 * Comprehension is scored and kept (DR-07), tracked toward a 95% goal and a
 * healthy pace (DR-08). Reaching the goal earns a perk (DR-09); the numbers are
 * an honest-layer signal and never a gate or a grade shown to the child.
 */
class DailyReadingService
{
    public const DEFAULT_LEVEL = 5;

    public const COMPREHENSION_GOAL = 95;

    public const MIN_LEVEL = 1;

    public const MAX_LEVEL = 10;

    private const STRUGGLE_THRESHOLD = 70;

    public function __construct(
        private StreakService $streaks,
        private StreakEconomyService $economy,
    ) {}

    public function readingLevel(User $student): int
    {
        return $student->reading_level ?? self::DEFAULT_LEVEL;
    }

    /**
     * Serve today's assignment: one unseen passage at (or near) her level, one per
     * day (DR-01). Returns null if the pool near her level is exhausted.
     */
    public function serve(User $student, ?Carbon $on = null): ?DailyReadingAssignment
    {
        $on ??= Carbon::today();

        $existing = DailyReadingAssignment::where('student_id', $student->id)
            ->where('date', $on->toDateString())->first();
        if ($existing !== null) {
            return $existing;
        }

        $seen = DailyReadingAssignment::where('student_id', $student->id)->pluck('passage_id');
        $level = $this->readingLevel($student);

        $passage = ReadingPassage::where('is_active', true)
            ->whereNotIn('id', $seen)
            ->where('reading_level', $level)
            ->inRandomOrder()->first()
            ?? ReadingPassage::where('is_active', true)
                ->whereNotIn('id', $seen)
                ->whereBetween('reading_level', [$level - 1, $level + 1])
                ->orderByRaw('ABS(reading_level - ?)', [$level])
                ->first();

        if ($passage === null) {
            return null;
        }

        return DailyReadingAssignment::create([
            'student_id' => $student->id,
            'passage_id' => $passage->id,
            'date' => $on->toDateString(),
            'answers' => [],
            'started_at' => now(),
        ]);
    }

    /**
     * Grade the comprehension answers, keep the score, compute reading pace, and
     * mark the day complete (DR-07). MC questions are graded; written responses are
     * writing practice, never scored (DR-03). Advances the reading streak and grants
     * a perk when she hits the goal (DR-09).
     *
     * @param  array<int,int>  $answers  question index => chosen option index
     */
    public function score(DailyReadingAssignment $assignment, array $answers, ?Carbon $finishedAt = null): DailyReadingAssignment
    {
        $finishedAt ??= now();
        $questions = $assignment->passage->questions ?? [];

        $gradable = 0;
        $correct = 0;
        foreach ($questions as $i => $question) {
            if (($question['type'] ?? 'mc') !== 'mc') {
                continue; // written response — reinforcement, not scored
            }
            $gradable++;
            if (isset($answers[$i]) && (int) $answers[$i] === (int) ($question['correct_index'] ?? -1)) {
                $correct++;
            }
        }
        $scorePct = $gradable > 0 ? (int) round($correct / $gradable * 100) : 0;

        $wpm = null;
        if ($assignment->started_at !== null) {
            $minutes = max($assignment->started_at->diffInSeconds($finishedAt) / 60, 0.1);
            $wpm = (int) round($assignment->passage->word_count / $minutes);
        }

        $assignment->update([
            'answers' => $answers,
            'comprehension_score' => $scorePct,
            'words_per_minute' => $wpm,
            'completed_at' => $finishedAt,
        ]);

        $this->streaks->recordActivity($assignment->student_id, 'reading', Carbon::parse($assignment->date));

        if ($scorePct >= self::COMPREHENSION_GOAL) {
            $this->economy->grantReward($assignment->student_id, 'shore_leave', 'milestone');
        }

        $this->adaptLevel($assignment->student);

        return $assignment->fresh();
    }

    /**
     * Her running comprehension average across scored assignments (DR-08), tracked
     * toward the 95% goal. Null when she has no scored reading yet.
     */
    public function comprehensionAverage(User $student): ?int
    {
        $avg = DailyReadingAssignment::where('student_id', $student->id)
            ->whereNotNull('comprehension_score')->avg('comprehension_score');

        return $avg === null ? null : (int) round($avg);
    }

    /**
     * Nudge the reading level up after three straight strong sessions, ease it back
     * after three weak ones (DR-04).
     */
    public function adaptLevel(User $student): void
    {
        $recent = DailyReadingAssignment::where('student_id', $student->id)
            ->whereNotNull('comprehension_score')
            ->orderByDesc('date')->take(3)->pluck('comprehension_score');

        if ($recent->count() < 3) {
            return;
        }

        $level = $this->readingLevel($student);
        if ($recent->min() >= self::COMPREHENSION_GOAL && $level < self::MAX_LEVEL) {
            $student->reading_level = $level + 1;
            $student->save();
        } elseif ($recent->max() < self::STRUGGLE_THRESHOLD && $level > self::MIN_LEVEL) {
            $student->reading_level = $level - 1;
            $student->save();
        }
    }
}
