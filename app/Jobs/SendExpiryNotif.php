<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\CarDocExpiryMail;
use App\Models\CarDocument;
use App\Models\Company;
use App\Models\User;


class SendExpiryNotif implements ShouldQueue
{
    use Queueable;
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $today = Carbon::today();

        // Reminder intervals
        $reminders = [
            '1_month' => $today->copy()->addMonth(),
            '1_week' => $today->copy()->addWeek(),
            '1_day'  => $today->copy()->addDay(),
        ];

        $companies = Company::with(['cars.carDocuments', 'users'])->get();

        foreach ($companies as $company) {
            foreach ($reminders as $label => $targetDate) {
                // Get documents that expire on or before the target date
                $docs = $company->cars()
                    ->with(['carDocuments' => function ($q) use ($targetDate) {
                        $q->where('expiration_date', '=', $targetDate);
                    }])
                    ->get()
                    ->pluck('carDocuments')
                    ->flatten();

                if ($docs->isNotEmpty()) {
                    foreach ($company->users as $user) {
                         Mail::to($user->email)->send(new CarDocExpiryMail($docs, $label));
                    }
                }
            }

        }
    }

}
