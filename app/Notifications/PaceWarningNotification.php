<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaceWarningNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User $student,
        public readonly int $weeksBehind,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $this->student->name ?? 'your child';

        return (new MailMessage)
            ->subject('A gentle heads-up about the study plan')
            ->greeting('Hi there,')
            ->line("We've adjusted {$name}'s weekly plan to keep the exam goal comfortably in reach.")
            ->line("Right now {$name} is about {$this->weeksBehind} week(s) behind an on-track schedule, so we've reshaped the coming weeks to catch up gradually — no cramming.")
            ->line('You can see the updated plan on your dashboard.')
            ->salutation('Warmly, ForMyNieces');
    }
}
