<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A record that a student has been shown a particular question (by content hash),
 * anywhere in the loop. Drives the global no-repeat guarantee.
 */
class StudentQuestionExposure extends Model
{
    protected $fillable = [
        'student_id',
        'content_hash',
        'context',
        'seen_count',
    ];

    protected $casts = [
        'seen_count' => 'integer',
    ];

    /** @return BelongsTo<User, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
