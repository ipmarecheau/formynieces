<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolJournalEntry extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DIGITISED = 'digitised';

    public const STATUS_CONFIRMED = 'confirmed';

    protected $fillable = [
        'student_id',
        'uploaded_by',
        'image_path',
        'assessment_date',
        'term',
        'subject',
        'strand',
        'assessment_type',
        'score',
        'teacher_comment',
        'ocr_text',
        'ocr_confidence',
        'digitisation_status',
    ];

    protected $casts = [
        'assessment_date' => 'date:Y-m-d',
        'ocr_confidence' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /** @return HasMany<SchoolJournalQuestion> */
    public function questions(): HasMany
    {
        return $this->hasMany(SchoolJournalQuestion::class);
    }
}
