<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AnchorQuestion extends Model
{
    protected $fillable = [
        'subject',
        'sea_section',
        'strand',
        'difficulty',
        'prompt',
        'options',
        'correct_index',
        'distractor_notes',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'distractor_notes' => 'array',
        'difficulty' => 'integer',
        'correct_index' => 'integer',
        'is_active' => 'boolean',
    ];

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(
            SyllabusModule::class,
            'anchor_question_module',
            'anchor_question_id',
            'module_id',
        )->withTimestamps();
    }
}
