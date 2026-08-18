<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SJ-11..13 — one question from a digitised school assessment: what was asked,
 * what she wrote, the marked solution, its syllabus alignment, a clipped image,
 * and the AI's honest-layer read of her reasoning.
 */
class SchoolJournalQuestion extends Model
{
    protected $fillable = [
        'school_journal_entry_id',
        'number',
        'prompt',
        'student_answer',
        'correct_answer',
        'is_correct',
        'syllabus_module_id',
        'topic_label',
        'topic_confidence',
        'reasoning_note',
        'clip_path',
        'clip_box',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'topic_confidence' => 'float',
            'clip_box' => 'array',
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(SchoolJournalEntry::class, 'school_journal_entry_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(SyllabusModule::class, 'syllabus_module_id');
    }
}
