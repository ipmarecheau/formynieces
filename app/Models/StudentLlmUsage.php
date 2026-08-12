<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A student's LLM usage for one calendar month (period = 'YYYY-MM') — the ledger the
 * budget governor reads and writes (AG-01..04).
 */
class StudentLlmUsage extends Model
{
    protected $table = 'student_llm_usage';

    protected $fillable = [
        'student_id',
        'period',
        'input_tokens',
        'output_tokens',
        'cost_usd',
    ];

    protected $casts = [
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'cost_usd' => 'decimal:6',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
