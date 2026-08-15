<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReadingPassage extends Model
{
    protected $fillable = [
        'title',
        'body',
        'reading_level',
        'word_count',
        'questions',
        'is_active',
    ];

    protected $casts = [
        'reading_level' => 'integer',
        'word_count' => 'integer',
        'questions' => 'array',
        'is_active' => 'boolean',
    ];

    public function vocabularyWords(): HasMany
    {
        return $this->hasMany(VocabularyWord::class, 'passage_id');
    }
}
