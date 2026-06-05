<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyTarget extends Model
{
    protected $fillable = [
        'student_id',
        'module_id',
        'week_start_date',
        'is_completed',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(SyllabusModule::class, 'module_id');
    }
}