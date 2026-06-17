<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticeQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'subject',
        'sea_section',
        'strand',
        'difficulty',
        'sequence_order',
        'prompt',
        'options',
        'correct_index',
        'hint',
        'explanation',
        'is_active',
    ];

    protected $casts = [
        'options'        => 'array',   // JSON text <-> PHP array, like the diagnostic reads anchors
        'difficulty'     => 'integer',
        'sequence_order' => 'integer',
        'correct_index'  => 'integer',
        'is_active'      => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(SyllabusModule::class, 'module_id');
    }
}