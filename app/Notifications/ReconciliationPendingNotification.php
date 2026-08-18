<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * RR-13 — the diagnostic cleared a strand the guardian flagged, so the roadmap
 * waits on her decision. The child is held on a waiting page; this tells the
 * guardian a decision is waiting so the child is never silently stuck.
 */
class ReconciliationPendingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User $student,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->student->name ?? 'your child';

        return (new MailMessage)
            ->subject("A quick decision so {$name} can set sail")
            ->greeting('Hi there,')
            ->line("{$name} just finished the starting adventure — nicely done!")
            ->line("The check saw a topic you flagged differently from how you described it, so we've paused before setting the map, just to get it right with you.")
            ->line("{$name} is waiting on this one small choice. Please open your dashboard to decide — it takes a moment.")
            ->line('If we don\'t hear from you, we\'ll gently proceed with the check\'s result in a few days so nothing stays stuck.')
            ->salutation('Warmly, SmoothSeas');
    }
}
