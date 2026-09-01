<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the guardian when a child account is created. Carries the child's login
 * ID as a durable record — never the password, which the guardian reveals or
 * resets in the Parent Portal.
 */
class ChildAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $child,
        public string $loginId,
        public string $manageUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "{$this->child->name}'s SmoothSeas login is ready");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.child-account-created');
    }
}
