<?php

declare(strict_types=1);

namespace App\Services\Safety;

use App\Models\SafetyFlag;
use App\Services\LlmService;
use Illuminate\Support\Str;

/**
 * ChildSafetyModerator (AG-12..15) — screens what a child sends to an AI tutor and what the
 * tutor sends back, using a moderation classifier (Llama Guard via OpenRouter).
 *
 *  - Unsafe input is never forwarded to the tutor; unsafe output is never shown (AG-12/13).
 *  - Fails CLOSED: if the classifier is unavailable, the content is withheld, not passed
 *    through unmoderated (AG-14).
 *  - Concerning categories (self-harm, abuse, exploitation) also raise a SafetyFlag for a
 *    trusted adult to follow up — care, not just a block (AG-15).
 */
class ChildSafetyModerator
{
    /** Llama Guard category code -> human label. */
    private const LABELS = [
        'S1' => 'violent crimes', 'S2' => 'non-violent crimes', 'S3' => 'sex crimes',
        'S4' => 'child exploitation', 'S5' => 'defamation', 'S6' => 'specialized advice',
        'S7' => 'privacy', 'S8' => 'intellectual property', 'S9' => 'weapons', 'S10' => 'hate',
        'S11' => 'self-harm', 'S12' => 'sexual content', 'S13' => 'elections', 'S14' => 'code abuse',
    ];

    /** Categories that escalate to a trusted adult, not just block. */
    private const CONCERNING = ['S3', 'S4', 'S11'];

    public function __construct(private LlmService $llm) {}

    /**
     * Classify one message. On a concerning category it also records a SafetyFlag for the
     * student (AG-15). Any non-safe result means the caller must withhold the content.
     */
    public function moderate(string $text, ?int $studentId = null): SafetyResult
    {
        $raw = trim($this->llm->chat(
            [['role' => 'user', 'content' => $text]],
            maxTokens: 24,
            studentId: $studentId,
            essential: false,
            model: (string) config('services.llm.guard_model'),
        ));

        $lower = strtolower($raw);

        if (str_starts_with($lower, 'safe')) {
            return SafetyResult::safe();
        }

        if (! str_starts_with($lower, 'unsafe')) {
            // Neither a clear "safe" nor "unsafe" verdict (e.g. the fallback string) —
            // the classifier is effectively unavailable, so fail closed (AG-14).
            return SafetyResult::blocked();
        }

        preg_match_all('/S\d+/', strtoupper($raw), $matches);
        $codes = $matches[0] ?? [];
        $concerning = array_intersect($codes, self::CONCERNING) !== [];
        $category = isset($codes[0]) ? (self::LABELS[$codes[0]] ?? $codes[0]) : null;

        if ($concerning && $studentId !== null) {
            $this->escalate($studentId, $category ?? 'concerning', $text);
        }

        return SafetyResult::unsafe($category, $concerning);
    }

    /** Record a safety flag for the guardian + admin to follow up (AG-15). */
    private function escalate(int $studentId, string $category, string $text): void
    {
        SafetyFlag::create([
            'student_id' => $studentId,
            'category' => $category,
            'excerpt' => Str::limit(trim($text), 200),
        ]);
    }
}
