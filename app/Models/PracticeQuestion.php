<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticeQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'subject',
        'sea_section',
        'strand',
        'difficulty',
        'sequence_order',
        'prompt',
        'options',
        'correct_index',
        'hint',
        'explanation',
        'distractor_notes',
        'is_active',
        'source_ref',
        'content_hash',
    ];

    protected static function booted(): void
    {
        // Keep the content hash in step with the question's identity (prompt +
        // its option set), so the exposure ledger dedupes the same question even
        // across banks and regardless of option order (QB-16 shuffling).
        static::saving(function (PracticeQuestion $q): void {
            $q->content_hash = self::hashFor((string) $q->prompt, $q->options ?? []);
        });
    }

    /**
     * A stable content hash identifying a question by its prompt and option SET
     * (order-independent). Used by the no-repeat exposure ledger.
     *
     * @param  array<int, string>  $options
     */
    public static function hashFor(string $prompt, array $options): string
    {
        $normalisedOptions = collect($options)->map(fn ($o) => trim((string) $o))->sort()->values()->all();

        return sha1(trim($prompt).'|'.implode('␟', $normalisedOptions));
    }

    protected $casts = [
        'options' => 'array',   // JSON text <-> PHP array, like the diagnostic reads anchors
        'distractor_notes' => 'array',
        'difficulty' => 'integer',
        'sequence_order' => 'integer',
        'correct_index' => 'integer',
        'is_active' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(SyllabusModule::class, 'module_id');
    }

    /**
     * The misconception this chosen option reveals, if the distractor is tagged
     * (LL-09). Mirrors the diagnostic's distractor_notes.misconceptions[index]
     * shape, resolving the tag against this module's taxonomy. Null when the
     * option is correct or untagged — the caller then falls back to the generic
     * explanation (progressive enhancement).
     */
    public function misconceptionFor(int $chosenIndex): ?Misconception
    {
        if ($chosenIndex === $this->correct_index) {
            return null;
        }

        $notes = $this->distractor_notes ?? [];
        $key = $notes['misconceptions'][(string) $chosenIndex]
            ?? $notes['misconceptions'][$chosenIndex]
            ?? null;

        if ($key === null) {
            return null;
        }

        return Misconception::query()
            ->where('module_id', $this->module_id)
            ->where('key', $key)
            ->first();
    }
}
