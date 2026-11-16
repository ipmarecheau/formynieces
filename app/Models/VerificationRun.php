<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\VerificationRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One tester's verdict on one scenario from a guided /user-testing walkthrough. Evidence
 * for Isaac's review — not a specs:verify record. See the create migration for the rule.
 *
 * A verdict can also be `waiting`: on a delayed-feedback scenario (a notification that fires
 * later) the tester defers, and the system emails them around the expected notification time
 * to come back and record whether it arrived.
 */
class VerificationRun extends Model
{
    /** @use HasFactory<VerificationRunFactory> */
    use HasFactory;

    public const RESULT_WORKED = 'worked';

    public const RESULT_ISSUE = 'issue';

    public const RESULT_SKIPPED = 'skipped';

    /** A deferred check: awaiting the expected notification and the tester's return verdict. */
    public const RESULT_WAITING = 'waiting';

    /** Minutes before the expected event to send the "watch for it now" reminder. */
    public const FIRST_REMINDER_LEAD_MINUTES = 5;

    /** Minutes after the expected event to send the "did it arrive?" reminder. */
    public const SECOND_REMINDER_LAG_MINUTES = 20;

    protected $fillable = [
        'tester_email',
        'tester_timezone',
        'tester_name',
        'feature',
        'scenario_id',
        'result',
        'notes',
        'screenshot_path',
        'expected_at',
        'first_reminder_sent_at',
        'second_reminder_sent_at',
        'spec_hash',
        'impl_hash',
        'verified_commit',
        'change_notified_hash',
    ];

    protected function casts(): array
    {
        return [
            'expected_at' => 'datetime',
            'first_reminder_sent_at' => 'datetime',
            'second_reminder_sent_at' => 'datetime',
        ];
    }

    public function isWaiting(): bool
    {
        return $this->result === self::RESULT_WAITING;
    }

    /** When to send the "watch your device now" reminder — just before the event. */
    public function firstReminderAt(): ?CarbonImmutable
    {
        return $this->expected_at?->toImmutable()->subMinutes(self::FIRST_REMINDER_LEAD_MINUTES);
    }

    /** When to send the "did it arrive?" reminder — after the event, once it should have fired. */
    public function secondReminderAt(): ?CarbonImmutable
    {
        return $this->expected_at?->toImmutable()->addMinutes(self::SECOND_REMINDER_LAG_MINUTES);
    }
}
