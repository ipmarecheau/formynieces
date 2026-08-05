<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * The Voyage companion — a warm, deterministic voice on the student's home screen.
 *
 * It composes a greeting only from facts already true on her Voyage: her name, her
 * streak, and this week's topics. It never invents progress she has not earned, and
 * it never speaks the guardian's honest gauge — no pace, no percentage, no deficit
 * ever reaches the child through it. Absent facts simply fall silent (null lines),
 * so nothing false is ever said (VC-01, VC-02, VC-03).
 *
 * Pure and side-effect free — the caller hands it facts, it hands back display
 * strings. The optional @roadmap AI voice (VC-04/05) will layer on top of these
 * same facts later; this template is the always-on fallback.
 */
final class VoyageCompanion
{
    /**
     * @param  array{practice?:int, login?:int, mastery?:int}  $streaks
     * @param  array<int, string>  $thisWeekTopics
     * @return array{greeting:string, streak:?string, plan:?string, avatar:string}
     */
    public static function for(string $name, array $streaks, array $thisWeekTopics): array
    {
        $bestStreak = max(
            (int) ($streaks['practice'] ?? 0),
            (int) ($streaks['login'] ?? 0),
            (int) ($streaks['mastery'] ?? 0),
        );

        $plan = self::planLine($thisWeekTopics);

        return [
            'greeting' => $bestStreak > 0 ? "Welcome back, {$name}!" : "Ahoy, {$name}!",
            'streak' => self::streakLine($bestStreak),
            'plan' => $plan,
            'avatar' => self::avatarPose($bestStreak > 0, $plan !== null),
        ];
    }

    /**
     * Which pose of Smooth greets her — he celebrates a live streak first, points
     * to the chart when there's a plan, and simply waves hello otherwise.
     */
    private static function avatarPose(bool $hasStreak, bool $hasPlan): string
    {
        return match (true) {
            $hasStreak => 'cheer',
            $hasPlan => 'chart',
            default => 'wave',
        };
    }

    /**
     * Her running streak, framed as warmth — a day count is the motivational layer,
     * never a pace metric. Silent when she has no streak yet.
     */
    private static function streakLine(int $days): ?string
    {
        if ($days < 1) {
            return null;
        }

        $unit = $days === 1 ? 'day' : 'days';
        $cheer = $days >= 5 ? "you're on fire!" : 'keep it going!';

        return "🔥 {$days} {$unit} in a row — {$cheer}";
    }

    /**
     * This week's focus, named by topic in child-kind language — the "Strand: "
     * prefix is dropped so she reads the specific skill, never a count or a week
     * number. Silent when no target names anything for this week.
     *
     * @param  array<int, string>  $topics
     */
    private static function planLine(array $topics): ?string
    {
        $short = array_values(array_filter(array_map(
            static fn (string $topic): string => trim(Str::contains($topic, ': ') ? Str::after($topic, ': ') : $topic),
            $topics,
        )));

        if ($short === []) {
            return null;
        }

        return "This week we're charting ".self::naturalList(array_slice($short, 0, 3)).'.';
    }

    /**
     * "a", "a and b", or "a, b and c" — a friendly comma list.
     *
     * @param  array<int, string>  $items
     */
    private static function naturalList(array $items): string
    {
        if (count($items) === 1) {
            return $items[0];
        }

        $last = array_pop($items);

        return implode(', ', $items).' and '.$last;
    }
}
