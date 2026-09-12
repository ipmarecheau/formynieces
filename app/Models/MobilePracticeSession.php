<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A mobile child practice session — the served question set and progress through it.
 * Attempts are still recorded in practice_attempts/student_progress by RecordPracticeAttempt.
 */
class MobilePracticeSession extends Model
{
    protected $fillable = [
        'student_id',
        'module_id',
        'question_ids',
        'position',
        'answers',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'question_ids' => 'array',
            'answers' => 'array',
            'position' => 'integer',
            'finished_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /** @return BelongsTo<SyllabusModule, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(SyllabusModule::class, 'module_id');
    }
}
