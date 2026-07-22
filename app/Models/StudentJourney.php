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
        'cap_review_required',
        'required_pace',
    ];

    protected $casts = [
        'journey_start' => 'date:Y-m-d',
        'exam_date' => 'date:Y-m-d',
        'weeks_behind' => 'integer',
        'cap_review_required' => 'boolean',
        'required_pace' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
