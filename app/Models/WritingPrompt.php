<?php

namespace App\Models;

use Database\Factories\WritingPromptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * The weekly writing prompt for the parallel writing track. One shared prompt per
 * Monday-anchored study week.
 */
class WritingPrompt extends Model
{
    /** @use HasFactory<WritingPromptFactory> */
    use HasFactory;

    protected $fillable = [
        'week_start_date',
        'title',
        'prompt',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'week_start_date' => 'date:Y-m-d',
        ];
    }

    /**
     * The prompt for the study week containing $on (defaults to now). Weeks are
     * Monday-anchored to match the pacing/weekly-target calendar.
     */
    public static function forWeek(?Carbon $on = null): ?self
    {
        $weekStart = ($on ?? now())->startOfWeek()->toDateString();

        return self::where('week_start_date', $weekStart)->first();
    }

    /** @return HasMany<WritingSubmission, $this> */
    public function submissions(): HasMany
    {
        return $this->hasMany(WritingSubmission::class);
    }
}
