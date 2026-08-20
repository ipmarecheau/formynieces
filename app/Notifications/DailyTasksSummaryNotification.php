<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PN-01/PN-02 — the parent's end-of-day (or gone-inactive) note: did today's
 * paced tasks get done? Guardian-layer and honest, but kind: it names what was
 * completed and what is still open, and ties it to staying ready for the exam,
 * never shaming the child.
 */
class DailyTasksSummaryNotification extends Notification
{
    use Queueable;

    /**
     * @param  list<string>  $doneTopics
     * @param  list<string>  $openTopics
     */
    public function __construct(
        public readonly User $student,
        public readonly bool $minimumMet,
        public readonly array $doneTopics,
        public readonly array $openTopics,
        public readonly string $reason, // 'done' | 'inactive'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->student->name ?? 'your child';
        $allDone = $this->minimumMet && count($this->openTopics) === 0;

        $mail = (new MailMessage)
            ->subject($allDone
                ? "🎉 {$name} finished today's plan"
                : "{$name}'s study day — a quick summary")
            ->greeting('Hi there,');

        if ($this->reason === 'inactive') {
            $mail->line("{$name} has stepped away from SmoothSeas for a little while, so here's where today stands.");
        } else {
            $mail->line("Here's how {$name}'s day on SmoothSeas went.");
        }

        $mail->line($this->minimumMet
            ? "✅ Today's daily minimum is complete."
            : "⏳ Today's daily minimum isn't finished yet.");

        if (count($this->doneTopics) > 0) {
            $mail->line('**Finished today:** '.implode(', ', $this->doneTopics));
        }

        if (count($this->openTopics) > 0) {
            $mail->line('**Still to do to stay on pace:** '.implode(', ', $this->openTopics));
            $mail->line('These topics are paced to keep the exam goal comfortably in reach — a nudge to wrap them up would help.');
        } elseif ($this->minimumMet) {
            $mail->line("Everything paced for today is done — a great day's sailing. 🌊");
        }

        return $mail->salutation('Warmly, SmoothSeas');
    }
}
