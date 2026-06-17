<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProgress extends Model
{
    protected $fillable = [
        'student_id',
        'module_id',
        'status',
        'score',
        'previous_score',
        'current_rung',
        'current_streak',
        'streak_question_ids',
    ];

    protected $casts = [
        'score'               => 'integer',
        'previous_score'      => 'integer',
        'current_rung'        => 'integer',
        'current_streak'      => 'integer',
        'streak_question_ids' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(SyllabusModule::class, 'module_id');
    }
}