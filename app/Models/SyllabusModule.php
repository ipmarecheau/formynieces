<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyllabusModule extends Model
{
    protected $fillable = [
        'subject',
        'topic',
        'sea_section',
        'sequence_order',
        'pacing_week',
        'description',
        'resources',
    ];

    protected $casts = [
        'resources' => 'array',
    ];

    public function studentProgress(): HasMany
    {
        return $this->hasMany(StudentProgress::class, 'module_id');
    }

    public function weeklyTargets(): HasMany
    {
        return $this->hasMany(WeeklyTarget::class, 'module_id');
    }
}