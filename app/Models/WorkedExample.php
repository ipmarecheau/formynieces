<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A cached, step-by-step worked example for a practice question — shown in the
 * tutorial stage. Generated once (by the LLM, or the bank explanation as fallback)
 * and reused across students.
 */
class WorkedExample extends Model
{
    protected $fillable = [
        'practice_question_id',
        'steps',
        'source',
    ];

    protected $casts = [
        'steps' => 'array',
    ];

    /** @return BelongsTo<PracticeQuestion, $this> */
    public function question(): BelongsTo
    {
        return $this->belongsTo(PracticeQuestion::class, 'practice_question_id');
    }
}
