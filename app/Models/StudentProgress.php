<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class StudentProgress extends Model
{
    /** A mastered level is locked this many days, then its re-mastery comes due (LL-23/24). */
    public const MAINTENANCE_DAYS = 14;

    /** After the due day she has this many days of grace before it decays to review (LL-17/25). */
    public const GRACE_DAYS = 5;

    protected $fillable = [
        'student_id',
        'module_id',
        'status',
        'mastered_at',
        'score',
        'previous_score',
        'current_rung',
        'current_streak',
        'streak_question_ids',
    ];

    protected $casts = [
        'score' => 'integer',
        'previous_score' => 'integer',
        'current_rung' => 'integer',
        'current_streak' => 'integer',
        'streak_question_ids' => 'array',
        'mastered_at' => 'datetime',
    ];

    /**
     * True while a mastered level sits in its grace window — past the due day
     * (mastered_at + 14d) but before it decays to review (+5d more). This is the
     * "needs review" state the map glows red for (LL-25).
     */
    public function isDueForReview(): bool
    {
        if ($this->status !== 'mastered' || $this->mastered_at === null) {
            return false;
        }

        $due = $this->mastered_at->copy()->addDays(self::MAINTENANCE_DAYS);
        $graceEnd = $due->copy()->addDays(self::GRACE_DAYS);

        return now()->gte($due) && now()->lt($graceEnd);
    }

    /**
     * True once a mastered level's grace has fully passed without re-mastery, so
     * it must decay to review (LL-17). Past mastered_at + 14d window + 5d grace.
     */
    public function hasDecayed(?Carbon $asOf = null): bool
    {
        if ($this->status !== 'mastered' || $this->mastered_at === null) {
            return false;
        }

        $asOf ??= now();
        $graceEnd = $this->mastered_at->copy()
            ->addDays(self::MAINTENANCE_DAYS)
            ->addDays(self::GRACE_DAYS);

        return $asOf->gte($graceEnd);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(SyllabusModule::class, 'module_id');
    }
}
