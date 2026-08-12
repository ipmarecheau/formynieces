<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A child-safety escalation flag (AG-15) — a concerning tutor message surfaced for a
 * trusted adult (guardian + admin) to follow up.
 */
class SafetyFlag extends Model
{
    protected $fillable = [
        'student_id',
        'category',
        'excerpt',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
