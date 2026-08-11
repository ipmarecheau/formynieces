<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single past-paper-style writing prompt in the bank, carrying its own marking
 * rubric. Feeds the parallel writing track; never a module mastery item. Keyed by
 * genre + difficulty, with `source_ref` (the Moodle question name) as the
 * idempotent import key.
 */
class WritingBankPrompt extends Model
{
    public const GENRE_NARRATIVE = 'narrative';

    public const GENRE_REPORT = 'report';

    protected $fillable = [
        'source_ref',
        'genre',
        'sub_genre',
        'module_id',
        'difficulty',
        'title',
        'prompt',
        'rubric',
        'rubric_html',
        'marks',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'difficulty' => 'integer',
            'marks' => 'integer',
            'rubric' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<SyllabusModule, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(SyllabusModule::class);
    }
}
