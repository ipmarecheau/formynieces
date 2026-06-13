<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosticResponse extends Model
{
    protected $fillable = [
        'diagnostic_session_id',
        'anchor_question_id',
        'chosen_index',
        'is_correct',
        'misconception',
    ];

    protected $casts = [
        'chosen_index' => 'integer',
        'is_correct' => 'boolean',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(DiagnosticSession::class, 'diagnostic_session_id');
    }

    public function anchorQuestion(): BelongsTo
    {
        return $this->belongsTo(AnchorQuestion::class, 'anchor_question_id');
    }
}
