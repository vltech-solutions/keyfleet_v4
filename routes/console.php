<?php

use App\Jobs\SendExpiryNotif;
use App\Jobs\SendSubscriptionReminder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(\App\Services\AgentCommissionService::class)->promotePayable())
    ->name('agent-commissions-payable')
    ->hourly()
    ->withoutOverlapping();

// Schedule::job(new SendExpiryNotif())
//     ->dailyAt('08:00');
// ->everyMinute();

// Schedule::job(new SendSubscriptionReminder())
// //    ->dailyAt('08:00');
//     ->everyMinute();
