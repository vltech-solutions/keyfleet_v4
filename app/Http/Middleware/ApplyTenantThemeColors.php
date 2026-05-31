<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTenantThemeColors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();
        if ($tenant && $tenant->primary_color) {
            FilamentColor::register(['primary' => $tenant->primary_color]);
        }
        return $next($request);
    }
}
