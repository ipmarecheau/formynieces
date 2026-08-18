<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Days a subject's streak has been banked ahead by accelerating (SE-09), up to
 * the banking limit (one normally, two with a Tailwind — SE-10).
 */
class StreakBank extends Model
{
    protected $fillable = [
        'student_id',
        'subject',
        'days_ahead',
    ];

    protected $casts = [
        'days_ahead' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
