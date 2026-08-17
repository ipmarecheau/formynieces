<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** SJ-08 — a corroborating/weakening signal a confirmed school assessment writes for a strand. */
class SchoolStrandSignal extends Model
{
    protected $fillable = [
        'student_id',
        'school_journal_entry_id',
        'strand',
        'direction',
        'strength',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(SchoolJournalEntry::class, 'school_journal_entry_id');
    }
}
