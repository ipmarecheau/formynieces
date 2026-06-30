<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticeAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'practice_question_id',
        'module_id',
        'difficulty',
        'is_correct',
    ];

    protected $casts = [
        'difficulty' => 'integer',
        'is_correct' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(PracticeQuestion::class, 'practice_question_id');
    }
}