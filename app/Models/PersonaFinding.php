<?php

namespace App\Models;

use Database\Factories\PersonaFindingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One thing a persona noticed during a run (PA-04/05) — a friction point with a severity,
 * an area, and a suggestion, which the team can then critique with a verdict and notes.
 */
class PersonaFinding extends Model
{
    /** @use HasFactory<PersonaFindingFactory> */
    use HasFactory;

    public const VERDICT_VALID = 'valid';

    public const VERDICT_INVALID = 'invalid';

    public const VERDICT_WONT_FIX = 'wont_fix';

    public const VERDICT_FIXED = 'fixed';

    protected $fillable = [
        'persona_run_id',
        'severity',
        'area',
        'observation',
        'suggestion',
        'admin_verdict',
        'admin_notes',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(PersonaRun::class, 'persona_run_id');
    }
}
