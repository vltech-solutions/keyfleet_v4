<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SendExpiryNotif;
use App\Jobs\SendSubscriptionReminder;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::job(new SendExpiryNotif())
//     ->dailyAt('08:00');
    // ->everyMinute();

// Schedule::job(new SendSubscriptionReminder())
// //    ->dailyAt('08:00');
//     ->everyMinute();