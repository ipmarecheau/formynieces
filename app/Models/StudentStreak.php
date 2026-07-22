<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentStreak extends Model
{
    protected $fillable = [
        'student_id',
        'type',
        'count',
        'last_activity_date',
        'restarted_at',
    ];

    protected $casts = [
        'count' => 'integer',
        'last_activity_date' => 'date:Y-m-d',
        'restarted_at' => 'date:Y-m-d',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
