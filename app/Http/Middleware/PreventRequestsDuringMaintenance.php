<?php

declare(strict_types=1);
 
namespace App\Http\Middleware;
 
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;
use Illuminate\Foundation\Http\MaintenanceModeBypassCookie;
use Illuminate\Http\RedirectResponse;
 
class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array
     */
    protected $except = [];
 
    protected function bypassResponse(string $secret): RedirectResponse
    {
        // Optional: redirect to the Filament dashboard route when a secret is present, but of course, you can redirect to any URL you want.
        return redirect(route('filament.admin.pages.dashboard'))->withCookie(
            MaintenanceModeBypassCookie::create($secret)
        );
    }
}