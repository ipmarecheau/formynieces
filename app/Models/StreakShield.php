<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-student streak protection: the three starter days a new voyager begins
 * with (SE-03) and the date an Anchor has frozen every streak (SE-08).
 */
class StreakShield extends Model
{
    protected $fillable = [
        'student_id',
        'starter_protection_remaining',
        'frozen_on',
    ];

    protected $casts = [
        'starter_protection_remaining' => 'integer',
        'frozen_on' => 'date:Y-m-d',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
