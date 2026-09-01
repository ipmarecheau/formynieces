<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoParentInvitation extends Notification
{
    public function __construct(
        public User $guardian,
        public string $coParentName,
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
        return (new MailMessage)
            ->subject("{$this->guardian->name} invited you to SmoothSeas")
            ->greeting("Hi {$this->coParentName},")
            ->line("{$this->guardian->name} has added you as a co-parent on SmoothSeas, so you can follow your child's SEA preparation together.")
            ->action('Join SmoothSeas', route('register'))
            ->line('Create your account with this email address to get connected.');
    }
}
