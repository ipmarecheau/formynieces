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
        'week_start_date' => 'date:Y-m-d',
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

    /**
     * The student-facing progress state of this target's module, derived live:
     *   'completed'   — the module is mastered
     *   'in_progress' — at least one practice attempt exists (even a wrong one)
     *   'not_started' — no practice attempts yet
     */
    public function state(): string
    {
        $status = StudentProgress::where('student_id', $this->student_id)
            ->where('module_id', $this->module_id)
            ->value('status');

        if ($status === 'mastered') {
            return 'completed';
        }

        $hasPracticed = PracticeAttempt::where('student_id', $this->student_id)
            ->where('module_id', $this->module_id)
            ->exists();

        return $hasPracticed ? 'in_progress' : 'not_started';
    }
}
