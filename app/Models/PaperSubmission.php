<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaperSubmission extends Model
{
    protected $fillable = ['paper_sitting_id', 'image_paths', 'digitisation_status'];
    protected $casts = ['image_paths' => 'array'];
    public function sitting(): BelongsTo { return $this->belongsTo(PaperSitting::class, 'paper_sitting_id'); }
}
