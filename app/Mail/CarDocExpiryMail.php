<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CarDocExpiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $docs;
    public $reminderLabel;
    /**
     * Create a new message instance.
     */
    public function __construct($docs,$reminderLabel)
    {
        $this->docs = $docs;
        $this->reminderLabel = $reminderLabel;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Car Document Expiration Reminder',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.car-doc-expiry',
            with: [
                'docs' => $this->docs,
                'reminderLabel' => $this->reminderLabel,
                'dashboardUrl' => url('/app'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
