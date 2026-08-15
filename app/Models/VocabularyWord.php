<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VocabularyWord extends Model
{
    protected $fillable = [
        'passage_id',
        'word',
        'definition',
        'context_sentence',
    ];

    public function passage(): BelongsTo
    {
        return $this->belongsTo(ReadingPassage::class, 'passage_id');
    }
}
