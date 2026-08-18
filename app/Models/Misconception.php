<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A named misconception in a module's taxonomy (LL-09). Carries one reusable
 * worked example so many distractors across questions can share the same targeted
 * correction (approach B — shared taxonomy).
 */
class Misconception extends Model
{
    protected $fillable = [
        'module_id',
        'key',
        'label',
        'worked_example',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(SyllabusModule::class, 'module_id');
    }
}
