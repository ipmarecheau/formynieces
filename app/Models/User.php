<?php

namespace App\Models;

use App\Notifications\VerifyEmailWithCode;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'phone_verified_at',
        'password',
        'role',
        'parent_id',
        'onboarding_completed_at', // Slice 1
        'welcomed_at', // TR-01: first welcome + joining bonus fired
        'tour_stage', // TR-07: cross-page interactive tour position
        'guardian_reconciled_at', // RR-04 reconciliation decision
        'paused_at', // Pause/resume: null = active
        'age_attested_at',
        'terms_accepted_at',
        'terms_version',
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
            'phone_verified_at' => 'datetime',
            'email_verification_code_expires_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed_at' => 'datetime', // Slice 1
            'welcomed_at' => 'datetime', // TR-01: first welcome + joining bonus
            'guardian_reconciled_at' => 'datetime', // RR-04 reconciliation decision
            'paused_at' => 'datetime', // Pause/resume
            'age_attested_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'known_weak_areas' => 'array',
            'learning_profile' => 'array',
            'weekly_module_cap_override' => 'integer',
            'seen_guides' => 'array',
        ];
    }

    /**
     * Send the verification email with a fresh code alongside the signed link.
     * Fired by the Registered event and by "resend" requests.
     */
    public function sendEmailVerificationNotification(): void
    {
        $code = $this->generateEmailVerificationCode();
        $this->notify(new VerifyEmailWithCode($code));
    }

    public function hasVerifiedPhone(): bool
    {
        return $this->phone_verified_at !== null;
    }

    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill(['phone_verified_at' => now()])->save();
    }

    /**
     * A phone is on file, verification is switched on, and it is not yet
     * confirmed. When phone verification is off (the free launch default) this
     * is always false — the number is captured but never gates onboarding.
     */
    public function needsPhoneVerification(): bool
    {
        return config('services.phone_verification.enabled')
            && $this->phone !== null
            && ! $this->hasVerifiedPhone();
    }

    /**
     * Email confirmed and no outstanding phone verification — the gate into
     * onboarding. Accounts with no phone on file (pre-existing) are not held for
     * a phone step.
     */
    public function isFullyVerified(): bool
    {
        return $this->hasVerifiedEmail() && ! $this->needsPhoneVerification();
    }

    /**
     * Generate a fresh 6-digit email verification code, store it hashed with a
     * short expiry, and return the plaintext to send. The companion to Breeze's
     * signed link — either verifies the email.
     */
    public function generateEmailVerificationCode(int $ttlMinutes = 30): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->forceFill([
            'email_verification_code' => Hash::make($code),
            'email_verification_code_expires_at' => now()->addMinutes($ttlMinutes),
        ])->save();

        return $code;
    }

    /**
     * Verify a submitted email code. On success, marks the email verified and
     * clears the stored code. Returns false when missing, expired, or wrong.
     */
    public function verifyEmailCode(string $code): bool
    {
        if ($this->email_verification_code === null
            || $this->email_verification_code_expires_at === null
            || $this->email_verification_code_expires_at->isPast()) {
            return false;
        }

        if (! Hash::check($code, $this->email_verification_code)) {
            return false;
        }

        $this->forceFill([
            'email_verification_code' => null,
            'email_verification_code_expires_at' => null,
        ])->save();

        if (! $this->hasVerifiedEmail()) {
            $this->markEmailAsVerified();
        }

        return true;
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

    /** Forget a dismissed guide so it can run again — e.g. when she re-starts the tour herself. (TR-04) */
    public function forgetGuide(string $key): void
    {
        $seen = $this->seen_guides ?? [];
        if (in_array($key, $seen, true)) {
            $this->seen_guides = array_values(array_filter($seen, fn ($g) => $g !== $key));
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
