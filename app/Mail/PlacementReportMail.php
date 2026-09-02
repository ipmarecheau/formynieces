<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The first-choice placement report, emailed to the parent (LG-05).
 * Optionally carries the AI Practice Pack PDF as an attachment (LG-09).
 */
class PlacementReportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{path:string, name:string}>  $pdfAttachments
     */
    public function __construct(public Lead $lead, public array $pdfAttachments = []) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your child's SEA placement report — ".$this->lead->placement_band,
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.placement-report', with: [
            'lead' => $this->lead,
        ]);
    }

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return collect($this->pdfAttachments)->map(
            fn (array $a) => Attachment::fromPath($a['path'])->as($a['name'])
        )->all();
    }
}
