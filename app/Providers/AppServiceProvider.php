<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('calendar-feed', fn (Request $request) => [
            Limit::perMinute(30)->by($request->ip()),
        ]);

        if (request()->header('X-Forwarded-Proto') === 'https' || app()->environment('production')) {
            URL::forceScheme('https');
        }

        Event::listen(Login::class, function (Login $event): void {
            $event->user->forceFill(['last_login_at' => now()])->saveQuietly();
        });

        if (request()->has('ref')) {
            session(['ref' => request()->get('ref')]);
        }
    }
}
