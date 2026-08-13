<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A permanent "done at least once" marker for a learning stage of a module (LE-03).
 *
 * The gated learning sequence — lesson -> worked examples -> practice — reads these rows to
 * decide what a student has unlocked. One row per (student, module, stage); once written it
 * is never removed, so an unlocked stage stays open for that module.
 */
class ModuleStageCompletion extends Model
{
    public const STAGE_LESSON = 'lesson';

    public const STAGE_TUTORIAL = 'tutorial';

    protected $fillable = [
        'student_id',
        'module_id',
        'stage',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
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
