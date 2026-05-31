<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;

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
        if (request()->header('X-Forwarded-Proto') === 'https' || app()->environment('production')) {
            URL::forceScheme('https');
        }

        if (request()->has('ref')) {
            session(['ref' => request()->get('ref')]);
        }
    }
}
