<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReadingAssignment extends Model
{
    protected $fillable = [
        'student_id',
        'passage_id',
        'date',
        'answers',
        'resume_position',
        'started_at',
        'completed_at',
        'comprehension_score',
        'words_per_minute',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'answers' => 'array',
        'resume_position' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'comprehension_score' => 'integer',
        'words_per_minute' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function passage(): BelongsTo
    {
        return $this->belongsTo(ReadingPassage::class, 'passage_id');
    }
}
