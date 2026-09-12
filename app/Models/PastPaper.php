<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PastPaper extends Model
{
    protected $fillable = ['title', 'subject', 'provenance', 'source_ref', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];

    public function questions(): HasMany
    {
        return $this->hasMany(PastPaperQuestion::class)->orderBy('number');
    }

    public function publishedQuestions(): HasMany
    {
        return $this->questions()->where('qc_status', 'approved')->where('is_withdrawn', false);
    }
}
