<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionReminderMail extends Mailable
{
    use Queueable, SerializesModels;
    public Company $company;
    public string $type;
    
    public function __construct(
        $company,
        $type
    ) {
        $this->company = $company;
        $this->type = $type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Subscription Reminder Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = match ($this->type) {
            'expired_3_days_ago' => 'emails.reminders.follow-up',
            'expires_today' => 'emails.reminders.expire',
            'expires_in_1_day'  => 'emails.reminders.one-day-before',
            'expires_in_3_days'     => 'emails.reminders.three-days-before',
            'expires_in_7_days'  => 'emails.reminders.seven-days-before',
            default         => 'emails.reminders.default',
        };

        return new Content(
            view: $view,
            with: [
                'company' => $this->company,
                'plan' => $this->company->plan(),
                'type' => $this->type,
                'renewUrl' => env('APP_URL'),
            ]
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
