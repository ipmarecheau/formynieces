<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An AI-assisted re-teach a student is pulled into after struggling in practice (LL-14…16, LL-22).
 * Open while `completed_at` is null; completed once she proves understanding (3 correct at D1),
 * which resumes her solo practice at D3.
 */
class ReteachSession extends Model
{
    public const TRIGGER_STREAK = 'streak';   // two hard misses in a row at D3/D5

    public const TRIGGER_WINDOW = 'window';   // five hard misses in the last seven

    /** Correct D1 answers needed to prove understanding and exit the re-teach (LL-16). */
    public const PROOF_TARGET = 3;

    protected $fillable = [
        'student_id',
        'module_id',
        'trigger',
        'correct_count',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'correct_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(SyllabusModule::class, 'module_id');
    }
}
