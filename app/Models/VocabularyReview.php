<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VocabularyReview extends Model
{
    protected $fillable = [
        'student_id',
        'word_id',
        'interval_days',
        'correct_streak',
        'due_at',
        'last_seen_at',
    ];

    protected $casts = [
        'interval_days' => 'integer',
        'correct_streak' => 'integer',
        'due_at' => 'date:Y-m-d',
        'last_seen_at' => 'date:Y-m-d',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function word(): BelongsTo
    {
        return $this->belongsTo(VocabularyWord::class, 'word_id');
    }
}
