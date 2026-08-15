<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StreakReward extends Model
{
    public const TYPES = ['shore_leave', 'anchor', 'tailwind', 'lifebuoy'];

    protected $fillable = [
        'student_id',
        'type',
        'quantity',
        'source',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
