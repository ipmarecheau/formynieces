<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'parent_id',
        'onboarding_completed_at', // Slice 1
        'age_attested_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed_at' => 'datetime', // Slice 1
            'age_attested_at' => 'datetime',
        ];
    }

    // A student belongs to a guardian (column stays parent_id for now).
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // Alias reading better against the spec's "guardian" vocabulary.
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // A guardian has many students.
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(StudentProgress::class, 'student_id');
    }

    public function weeklyTargets(): HasMany
    {
        return $this->hasMany(WeeklyTarget::class, 'student_id');
    }

    // Slice 1: a student has many diagnostic sessions.
    public function diagnosticSessions(): HasMany
    {
        return $this->hasMany(DiagnosticSession::class, 'student_id');
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Spec vocabulary is "guardian", never "parent". We accept the legacy
     * 'parent' role value during transition so existing seeded users still
     * resolve; new accounts should be created with role 'guardian'.
     */
    public function isGuardian(): bool
    {
        return in_array($this->role, ['guardian', 'parent'], true);
    }

    /** @deprecated use isGuardian() — kept so existing callers don't break. */
    public function isParent(): bool
    {
        return $this->isGuardian();
    }

    // Slice 1: has this student finished onboarding (diagnostic + reveal)?
    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarding_completed_at !== null;
    }
}
