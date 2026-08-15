<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** OC-01..05 — a parent's booked 15-minute onboarding call (Trinidad & Tobago time). */
class OnboardingCall extends Model
{
    public const STATUSES = ['requested', 'confirmed', 'completed', 'cancelled'];

    protected $fillable = [
        'parent_name',
        'email',
        'phone',
        'child_standard',
        'notes',
        'call_date',
        'call_time',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'call_date' => 'date:Y-m-d',
            'call_time' => 'datetime:H:i',
        ];
    }
}
