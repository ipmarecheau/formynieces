<?php

namespace App\Mail;

use App\Models\PracticeQuestion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/** The weekly SEA Question of the Week nurture email (LG-10). */
class SeaQuestionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PracticeQuestion $question) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your SEA Question of the Week 🐢');
    }

    public function content(): Content
    {
        $exam = Carbon::create((int) now()->addYear()->year, 4, 1);

        return new Content(markdown: 'mail.sea-question', with: [
            'question' => $this->question,
            'daysToSea' => max(0, (int) now()->diffInDays($exam, false)),
        ]);
    }
}
