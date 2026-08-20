<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyPlan extends Model
{
    protected $fillable = [
        'student_id',
        'date',
        'is_writing_day',
        'duties',
        'completed_at',
        'parent_summary_sent_at',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'is_writing_day' => 'boolean',
        'duties' => 'array',
        'completed_at' => 'datetime',
        'parent_summary_sent_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * The duty keys required today (writing only appears on writing days).
     *
     * @return list<string>
     */
    public function requiredDuties(): array
    {
        return array_keys(array_filter(
            $this->duties ?? [],
            fn ($done) => $done !== null,
        ));
    }

    /**
     * True when every required duty for the day is done.
     */
    public function isMinimumMet(): bool
    {
        $duties = array_filter($this->duties ?? [], fn ($done) => $done !== null);

        return $duties !== [] && ! in_array(false, $duties, true);
    }
}
