<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PastPaperQuestion extends Model
{
    protected $fillable = [
        'past_paper_id', 'syllabus_module_id', 'seed_question_id', 'number', 'item_type',
        'prompt', 'options', 'illustration_svg', 'correct_answer', 'mark_scheme', 'marks', 'objective',
        'difficulty', 'provenance', 'qc_status', 'qc_reason', 'is_withdrawn',
    ];

    protected $casts = ['options' => 'array', 'mark_scheme' => 'array', 'is_withdrawn' => 'boolean'];

    public function getSafeIllustrationSvgAttribute(): ?string
    {
        $svg = $this->illustration_svg;
        if (! is_string($svg) || ! str_starts_with(trim($svg), '<svg')) return null;
        $svg = preg_replace('/<\/?(script|iframe|object|embed|foreignObject)[^>]*>/i', '', $svg) ?? '';
        $svg = preg_replace('/\s(?:on[a-z]+|href|xlink:href)\s*=\s*(["\']).*?\1/i', '', $svg) ?? '';
        return strlen($svg) <= 20000 ? trim($svg) : null;
    }

    public function paper(): BelongsTo { return $this->belongsTo(PastPaper::class, 'past_paper_id'); }

    public function module(): BelongsTo { return $this->belongsTo(SyllabusModule::class, 'syllabus_module_id'); }

    public function seed(): BelongsTo { return $this->belongsTo(self::class, 'seed_question_id'); }
}
