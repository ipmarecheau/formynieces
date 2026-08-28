<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * The email-verification message, carrying BOTH the signed link (Breeze's
 * default) and a 6-digit code the parent can type instead. Either verifies.
 */
class VerifyEmailWithCode extends VerifyEmail
{
    public function __construct(private string $code) {}

    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify your email — SmoothSeas')
            ->greeting('Welcome aboard!')
            ->line('Confirm your email to keep setting up your child\'s voyage. You can either tap the button below or enter this code on the verification screen:')
            ->line("**Your code: {$this->code}**")
            ->action('Verify email', $url)
            ->line('This code expires in 30 minutes. If you didn\'t create a SmoothSeas account, you can ignore this email.');
    }
}
