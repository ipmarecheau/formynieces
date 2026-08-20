<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'parent_id',
        'onboarding_completed_at', // Slice 1
        'welcomed_at', // TR-01: first welcome + joining bonus fired
        'tour_stage', // TR-07: cross-page interactive tour position
        'guardian_reconciled_at', // RR-04 reconciliation decision
        'paused_at', // Pause/resume: null = active
        'age_attested_at',
        'target_sea_year',
        'known_weak_areas',
        'learning_profile', // AG-08: compact derived tags for AI tutor tailoring
        'weekly_module_cap_override', // Weekly targets: per-student cap override
        'seen_guides', // Smooth's guide: dismissed how-to screens (SG-01/02)
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
            'welcomed_at' => 'datetime', // TR-01: first welcome + joining bonus
            'guardian_reconciled_at' => 'datetime', // RR-04 reconciliation decision
            'paused_at' => 'datetime', // Pause/resume
            'age_attested_at' => 'datetime',
            'known_weak_areas' => 'array',
            'learning_profile' => 'array',
            'weekly_module_cap_override' => 'integer',
            'seen_guides' => 'array',
        ];
    }

    /** Has this student already been welcomed aboard (welcome + joining bonus)? (TR-01) */
    public function hasBeenWelcomed(): bool
    {
        return $this->welcomed_at !== null;
    }

    /**
     * The ordered legs of the first-run interactive tour (TR-07). After the overworld
     * and island, the student is walked through the whole learning loop on the real
     * pages: the check (guided to miss on purpose) → lesson → worked examples →
     * practice, then the relearn/AI leg, and finally done.
     *
     * @var array<int, string>
     */
    public const TOUR_STAGES = ['overworld', 'island', 'lesson', 'learn', 'examples', 'practice', 'reteach', 'done'];

    /** Move the student to a stage of the interactive cross-page tour. (TR-07) */
    public function setTourStage(?string $stage): void
    {
        $this->forceFill(['tour_stage' => $stage])->save();
    }

    /** Is the student mid-way through the first-run interactive tour? (TR-07) */
    public function onGuidedTour(): bool
    {
        return $this->tour_stage !== null && $this->tour_stage !== 'done';
    }

    /**
     * Advance the tour to a later leg, never backwards — so re-opening an already-seen
     * page can't rewind the walkthrough. (TR-07)
     */
    public function advanceTourStage(string $stage): void
    {
        $current = array_search($this->tour_stage, self::TOUR_STAGES, true);
        $next = array_search($stage, self::TOUR_STAGES, true);
        if ($next !== false && ($current === false || $next > $current)) {
            $this->setTourStage($stage);
        }
    }

    /** Has this student already dismissed the named Smooth guide? (SG-02) */
    public function hasSeenGuide(string $key): bool
    {
        return in_array($key, $this->seen_guides ?? [], true);
    }

    /** Remember that this student has dismissed the named Smooth guide. (SG-01/02) */
    public function markGuideSeen(string $key): void
    {
        $seen = $this->seen_guides ?? [];
        if (! in_array($key, $seen, true)) {
            $seen[] = $key;
            $this->seen_guides = $seen;
            $this->save();
        }
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

    public function studentJourney(): HasOne
    {
        return $this->hasOne(StudentJourney::class, 'student_id');
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

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
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

    /** Whether this student is currently paused by her guardian. */
    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }
}
