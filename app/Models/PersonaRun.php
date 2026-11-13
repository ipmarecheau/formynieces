<?php

namespace App\Models;

use Database\Factories\PersonaRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One nightly persona-simulation run (PA-01..03). A persona walks the live app and the run
 * records what it found, how long it took, and what it cost against the monthly budget.
 */
class PersonaRun extends Model
{
    /** @use HasFactory<PersonaRunFactory> */
    use HasFactory;

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED_BUDGET = 'skipped_budget';

    public const PERSONA_ANXIOUS_PARENT = 'anxious_parent';

    public const PERSONA_CHILD_FULL_DAY = 'child_full_day';

    protected $fillable = [
        'persona',
        'status',
        'summary',
        'cost_usd',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'cost_usd' => 'decimal:4',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function findings(): HasMany
    {
        return $this->hasMany(PersonaFinding::class);
    }
}
