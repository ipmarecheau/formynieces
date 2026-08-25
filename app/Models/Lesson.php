<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An interactive lesson authored in advance for a module (LE-01). Its `blocks` are the
 * ordered content shown on the lesson page; never generated in real time.
 */
class Lesson extends Model
{
    protected $fillable = [
        'module_id',
        'title',
        'blocks',
        'is_published',
        'objectives_direct',
        'objectives_indirect',
    ];

    protected $casts = [
        'blocks' => 'array',
        'is_published' => 'boolean',
        'objectives_direct' => 'array',
        'objectives_indirect' => 'array',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(SyllabusModule::class, 'module_id');
    }
}
