<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiagnosticSession extends Model
{
    protected $fillable = [
        'student_id',
        'status',
        'item_plan',
        'current_item',
        'writing_sample',
        'completed_at',
    ];

    protected $casts = [
        'item_plan' => 'array',
        'writing_sample' => 'array',
        'current_item' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(DiagnosticResponse::class, 'diagnostic_session_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
