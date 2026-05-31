<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\SubscriptionReminderMail;
use App\Models\Company;
use App\Models\User;

class SendSubscriptionReminder implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $today = Carbon::today();
        $days = [
            -3 => ['flag' => 'reminder_sent_after3d', 'label' => 'expired_3_days_ago'],
            0  => ['flag' => 'reminder_sent_0d', 'label' => 'expires_today'],
            1  => ['flag' => 'reminder_sent_1d', 'label' => 'expires_in_1_day'],
            3  => ['flag' => 'reminder_sent_3d', 'label' => 'expires_in_3_days'],
            7  => ['flag' => 'reminder_sent_7d', 'label' => 'expires_in_7_days'],
        ];

        foreach ($days as $offset => $info) {
            $targetDate = $today->copy()->addDays($offset)->format('Y-m-d');
            $flag = $info['flag'];
            
            // Chunking prevents memory exhaustion if you have thousands of companies
            Company::where('id', 3) 
            ->whereHas('subscription', function ($query) use ($targetDate, $flag) {
                $query->whereDate('ends_at', $targetDate)->where($flag, false);
            })
            ->with(['subscription', 'users'])
            ->chunk(100, function ($companies) use ($flag, $info) {
                foreach ($companies as $company) {
                    $emailsSent = 0;

                    foreach ($company->users as $user) {
                        try {
                            ProcessSubscriptionEmail::dispatch($user, $company, $info['label'], $flag);
                            
                            Log::info("[SubscriptionReminder] Job dispatched for {$user->email}");

                        } catch (\Exception $e) {
                            Log::error("[SubscriptionReminder] Could not queue job for {$user->email}: " . $e->getMessage());
                        }
                    }

                    // Only mark as sent if at least one email went out (or all, depending on your preference)
                    if ($emailsSent > 0) {
                        $company->subscription->update([$flag => true]);
                    }
                }
            });
        }
    }
}
