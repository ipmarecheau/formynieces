<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentJourney extends Model
{
    protected $fillable = [
        'student_id',
        'journey_start',
        'exam_date',
        'pace_status',
        'weeks_behind',
    ];

    protected $casts = [
        'journey_start' => 'date:Y-m-d',
        'exam_date'     => 'date:Y-m-d',
        'weeks_behind'  => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
