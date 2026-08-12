<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A student's active guided-learning time for one day (AG-05..07).
 */
class StudentGuidedTime extends Model
{
    protected $table = 'student_guided_time';

    protected $fillable = [
        'student_id',
        'day',
        'active_seconds',
    ];

    protected $casts = [
        'day' => 'date:Y-m-d',
        'active_seconds' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
