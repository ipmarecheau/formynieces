<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaperSitting extends Model
{
    protected $fillable = ['student_id', 'past_paper_id', 'paper_code', 'status', 'subject', 'length', 'question_ids', 'issued_at', 'graded_at', 'score', 'total_marks'];

    protected $casts = ['question_ids' => 'array', 'issued_at' => 'date', 'graded_at' => 'datetime'];

    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function paper(): BelongsTo { return $this->belongsTo(PastPaper::class, 'past_paper_id'); }
    public function answers(): HasMany { return $this->hasMany(PaperAnswer::class); }
    public function submissions(): HasMany { return $this->hasMany(PaperSubmission::class); }
}
