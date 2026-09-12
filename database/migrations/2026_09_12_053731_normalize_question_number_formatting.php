<?php

use App\Models\PracticeQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Normalise big-number formatting in the question banks so numbers read the same
 * everywhere (prompt, options, explanation, reports). The banks mixed space-grouped
 * numbers ("70 000") with unseparated ones ("45306"); this space-groups every
 * contiguous run of 5+ digits to match, leaving digit-by-digit place-value prompts
 * ("1 6 4 7 9") and 4-digit numbers (year ambiguity) untouched.
 *
 * Idempotent: an already-grouped number won't re-match. Data-only, so down() is a no-op.
 */
return new class extends Migration
{
    public function up(): void
    {
        $group = static function (?string $text): ?string {
            if ($text === null || $text === '') {
                return $text;
            }

            return preg_replace_callback('/(?<![\d,\. ])(\d{5,})(?![\d,\.])/', static function (array $m): string {
                return strrev(trim(chunk_split(strrev($m[1]), 3, ' ')));
            }, $text);
        };

        $groupOptions = static function (?string $json) use ($group): ?string {
            $opts = json_decode((string) $json, true);
            if (! is_array($opts)) {
                return $json;
            }

            return json_encode(array_map(fn ($o) => is_string($o) ? $group($o) : $o, $opts));
        };

        // Practice questions — recompute content_hash from the reformatted prompt+options
        // so a later db:seed of the (also-reformatted) YAML stays a no-op, not a duplicate.
        DB::table('practice_questions')->orderBy('id')->chunkById(500, function ($rows) use ($group, $groupOptions) {
            foreach ($rows as $r) {
                $prompt = $group($r->prompt);
                $options = $groupOptions($r->options);
                DB::table('practice_questions')->where('id', $r->id)->update([
                    'prompt' => $prompt,
                    'options' => $options,
                    'explanation' => $group($r->explanation),
                    'hint' => $group($r->hint),
                    'content_hash' => PracticeQuestion::hashFor((string) $prompt, json_decode((string) $options, true) ?: []),
                ]);
            }
        });

        // Diagnostic anchors — prompt + options only.
        DB::table('anchor_questions')->orderBy('id')->chunkById(500, function ($rows) use ($group, $groupOptions) {
            foreach ($rows as $r) {
                DB::table('anchor_questions')->where('id', $r->id)->update([
                    'prompt' => $group($r->prompt),
                    'options' => $groupOptions($r->options),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Formatting normalisation is not reversibly meaningful.
    }
};
