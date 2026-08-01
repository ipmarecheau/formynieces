<?php

namespace App\Models;

use Database\Factories\WritingSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A student's response to a weekly writing prompt. Carries a four-criterion rubric
 * profile and warm feedback — never a grade, never a pass/fail, never a mastery
 * status. `status` is 'pending' until scored.
 */
class WritingSubmission extends Model
{
    /** @use HasFactory<WritingSubmissionFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_SCORED = 'scored';

    /** The four rubric criteria, in display order, mapped to their column names. */
    public const CRITERIA = [
        'content' => 'Content',
        'language' => 'Language Use',
        'grammar' => 'Grammar and Mechanics',
        'organisation' => 'Organisation',
    ];

    protected $fillable = [
        'student_id',
        'writing_prompt_id',
        'body',
        'status',
        'content_score',
        'language_score',
        'grammar_score',
        'organisation_score',
        'did_well',
        'try_next',
        'scored_at',
    ];

    protected function casts(): array
    {
        return [
            'content_score' => 'integer',
            'language_score' => 'integer',
            'grammar_score' => 'integer',
            'organisation_score' => 'integer',
            'did_well' => 'array',
            'scored_at' => 'datetime',
        ];
    }

    public function isScored(): bool
    {
        return $this->status === self::STATUS_SCORED;
    }

    /**
     * Apply a parsed rubric to this submission and mark it scored.
     *
     * @param  array{content_score:int, language_score:int, grammar_score:int, organisation_score:int, did_well:array<int, string>, try_next:string}  $rubric
     */
    public function applyRubric(array $rubric): void
    {
        $this->update([
            'content_score' => $rubric['content_score'],
            'language_score' => $rubric['language_score'],
            'grammar_score' => $rubric['grammar_score'],
            'organisation_score' => $rubric['organisation_score'],
            'did_well' => $rubric['did_well'],
            'try_next' => $rubric['try_next'],
            'status' => self::STATUS_SCORED,
            'scored_at' => now(),
        ]);
    }

    /**
     * The rubric profile as a display-ready list of [label => score], or null if
     * not yet scored.
     *
     * @return array<string, int>|null
     */
    public function rubricProfile(): ?array
    {
        if (! $this->isScored()) {
            return null;
        }

        return [
            self::CRITERIA['content'] => $this->content_score,
            self::CRITERIA['language'] => $this->language_score,
            self::CRITERIA['grammar'] => $this->grammar_score,
            self::CRITERIA['organisation'] => $this->organisation_score,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /** @return BelongsTo<WritingPrompt, $this> */
    public function prompt(): BelongsTo
    {
        return $this->belongsTo(WritingPrompt::class, 'writing_prompt_id');
    }
}
