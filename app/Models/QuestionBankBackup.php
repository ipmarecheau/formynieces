<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A catalogued snapshot of the practice question bank — metadata pointing at a
 * JSON file that holds every question at the moment the backup was taken.
 */
class QuestionBankBackup extends Model
{
    protected $fillable = [
        'reason',
        'question_count',
        'path',
    ];

    protected $casts = [
        'question_count' => 'integer',
    ];
}
