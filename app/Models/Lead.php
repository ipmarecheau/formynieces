<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A funnel lead — a parent captured by the free SEA mock / placement-report offer
 * (lead_capture.feature) before they hold an account.
 */
class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'whatsapp',
        'child_name',
        'child_level',
        'source',
        'mock_session_id',
        'mock_score',
        'placement_band',
        'weakest_strands',
        'next_step',
        'weekly_opt_in',
        'converted_user_id',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'weakest_strands' => 'array',
            'weekly_opt_in' => 'boolean',
            'converted_at' => 'datetime',
            'mock_score' => 'integer',
        ];
    }

    /** The account created when this lead claimed the trial, if any. */
    public function convertedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_user_id');
    }

    /** Whether this lead has completed the mock and has a report. */
    public function hasReport(): bool
    {
        return $this->placement_band !== null;
    }
}
