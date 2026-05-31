<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionReminderMail;

class ProcessSubscriptionEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Retry 3 times if Hostinger kicks us out
    public $tries = 3; 

    public function __construct(
        public $user,
        public $company,
        public $label,
        public $flag
    ) {}

    public function handle(): void
    {
        Mail::to($this->user->email)
            ->bcc('rhayras22@gmail.com')
            ->send(new SubscriptionReminderMail($this->company, $this->label));

        // Only update the DB if the email actually sent!
        $this->company->subscription->update([$this->flag => true]);
    }
}