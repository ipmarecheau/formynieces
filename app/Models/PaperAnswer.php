<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaperAnswer extends Model
{
    protected $fillable = ['paper_sitting_id', 'past_paper_question_id', 'read_answer', 'working_note', 'is_correct', 'marks_awarded', 'confidence', 'misconception'];
    protected $casts = ['is_correct' => 'boolean', 'confidence' => 'float'];
    public function sitting(): BelongsTo { return $this->belongsTo(PaperSitting::class, 'paper_sitting_id'); }
    public function question(): BelongsTo { return $this->belongsTo(PastPaperQuestion::class, 'past_paper_question_id'); }
}
