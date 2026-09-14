<?php

namespace App\Models;

use App\Services\PastPapers\SvgSanitizer;
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
        return SvgSanitizer::clean($this->illustration_svg);
    }

    public function paper(): BelongsTo { return $this->belongsTo(PastPaper::class, 'past_paper_id'); }

    public function module(): BelongsTo { return $this->belongsTo(SyllabusModule::class, 'syllabus_module_id'); }

    public function seed(): BelongsTo { return $this->belongsTo(self::class, 'seed_question_id'); }
}
