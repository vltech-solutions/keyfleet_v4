<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use App\Models\BookingInspection;
use App\Models\BookingPayments;
use App\Models\Car;
use App\Models\CarDocument;
use App\Models\CarImage;
use App\Models\ChecklistItem;
use App\Models\CompanyWebsite;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\CustomerRequirement;
use App\Models\Expense;
use App\Models\FundType;
use App\Models\InspectionItem;
use App\Models\Partners;
use App\Models\Reservation;
use App\Models\Source;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ApplyTenantScopes
{
    public function handle(Request $request, Closure $next)
    {
        Booking::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Car::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Customer::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Expense::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        FundType::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Source::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        CarDocument::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Partners::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        BookingPayments::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Reservation::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        ChecklistItem::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Contract::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        CompanyWebsite::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        BookingInspection::addGlobalScope(
            fn (Builder $query) => $query->whereHas(
                'booking',
                fn (Builder $booking) => $booking->where('company_id', Filament::getTenant()?->getKey()),
            ),
        );

        InspectionItem::addGlobalScope(
            fn (Builder $query) => $query->whereHas(
                'inspection.booking',
                fn (Builder $booking) => $booking->where('company_id', Filament::getTenant()?->getKey()),
            ),
        );

        CustomerRequirement::addGlobalScope(
            fn (Builder $query) => $query->whereHas('customer', fn (Builder $customer) => $customer->where('company_id', Filament::getTenant()?->getKey())),
        );

        CarImage::addGlobalScope(
            fn (Builder $query) => $query->whereHas('car', fn (Builder $car) => $car->where('company_id', Filament::getTenant()?->getKey())),
        );

        return $next($request);
    }
}
