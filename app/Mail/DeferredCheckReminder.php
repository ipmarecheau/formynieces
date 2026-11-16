<?php

namespace App\Mail;

use App\Models\VerificationRun;
use App\Services\UserTesting\TestingCatalog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * One of the two reminders for a deferred test-drive check. Phase "before" nudges the tester
 * to watch their device just before the notification is expected; phase "after" asks them to
 * come back and record whether it arrived — linking straight to the scenario, pre-filled with
 * the "not received" note so a failure is one tap away.
 */
class DeferredCheckReminder extends Mailable
{
    use Queueable, SerializesModels;

    public const PHASE_BEFORE = 'before';

    public const PHASE_AFTER = 'after';

    public function __construct(
        public VerificationRun $run,
        public string $phase,
    ) {}

    public function envelope(): Envelope
    {
        $scenario = TestingCatalog::scenario($this->run->scenario_id);
        $title = $scenario['title'] ?? $this->run->scenario_id;

        $subject = $this->phase === self::PHASE_BEFORE
            ? "Watch your device: {$title}"
            : "Did it arrive? {$title}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $scenario = TestingCatalog::scenario($this->run->scenario_id);
        $timezone = $this->run->tester_timezone ?: config('app.timezone');

        return new Content(
            markdown: 'emails.user-testing.deferred-check',
            with: [
                'phase' => $this->phase,
                'name' => $this->run->tester_name,
                'scenarioTitle' => $scenario['title'] ?? $this->run->scenario_id,
                'label' => $scenario['delayed']['label'] ?? 'the notification',
                'expectedLocal' => $this->run->expected_at?->copy()->setTimezone($timezone)->format('g:ia'),
                'failByLocal' => $this->run->secondReminderAt()?->setTimezone($timezone)->format('g:ia'),
                'watchUrl' => route('user-testing.feature', strtolower($this->run->feature))
                    .'?scenario='.$this->run->scenario_id,
                'missedUrl' => route('user-testing.feature', strtolower($this->run->feature))
                    .'?scenario='.$this->run->scenario_id.'&outcome=missed',
            ],
        );
    }
}
