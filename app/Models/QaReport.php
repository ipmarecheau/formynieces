<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single QA finding posted by the walkthrough agent — one scenario outcome per run.
 */
class QaReport extends Model
{
    protected $fillable = [
        'scenario',
        'outcome',
        'summary',
        'detail',
        'actor',
        'screenshot_url',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }
}
