<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyllabusModule extends Model
{
    protected $fillable = [
        'subject',
        'topic',
        'sea_section',
        'sequence_order',
        'pacing_week',
        'description',
        'resources',
    ];

    protected $casts = [
        'resources' => 'array',
    ];

    public function studentProgress(): HasMany
    {
        return $this->hasMany(StudentProgress::class, 'module_id');
    }

    public function weeklyTargets(): HasMany
    {
        return $this->hasMany(WeeklyTarget::class, 'module_id');
    }

    // Slice 1: modules that must be mastered before this one.
    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            SyllabusModule::class,
            'module_prerequisites',
            'module_id',
            'prerequisite_module_id',
        )->withTimestamps();
    }

    // Slice 1: modules that depend on this one.
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            SyllabusModule::class,
            'module_prerequisites',
            'prerequisite_module_id',
            'module_id',
        )->withTimestamps();
    }

    // Slice 1: anchor questions that certify mastery of this module.
    public function anchorQuestions(): BelongsToMany
    {
        return $this->belongsToMany(
            AnchorQuestion::class,
            'anchor_question_module',
            'module_id',
            'anchor_question_id',
        )->withTimestamps();
    }

    /**
     * Distinct guardian-facing strands derived from the "Strand: Topic" naming,
     * grouped by subject. Used for the child-setup weak-area checkboxes.
     */
    public static function strandsBySubject(): array
    {
        return static::query()
            ->whereRaw("instr(topic, ':') > 0")
            ->get()
            ->groupBy('subject')
            ->map(fn ($modules) => $modules
                ->map(fn ($m) => trim(strstr($m->topic, ':', true)))
                ->unique()
                ->sort()
                ->values()
                ->all()
            )
            ->all();
    }
}
