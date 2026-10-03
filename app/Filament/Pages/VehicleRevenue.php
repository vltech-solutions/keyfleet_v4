<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesTenantPage;
use App\Models\Booking;
use App\Models\Car;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class VehicleRevenue extends Page
{
    use AuthorizesTenantPage;
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Reports';

    protected static string $view = 'filament.pages.vehicle-revenue';

    public string $carId = 'all';

    public string $period = 'this_month';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public function mount(): void
    {
        $this->applyPeriod('this_month');
    }

    public static function getNavigationGroupSort(): ?int
    {
        return 2;
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;

        $this->applyPeriod($period);

        $this->resetVehiclePagination();
    }

    protected function applyPeriod(string $period): void
    {
        match ($period) {
            'this_month' => $this->setDateRange(
                now()->startOfMonth(),
                now()->endOfMonth()
            ),

            'this_year' => $this->setDateRange(
                now()->startOfYear(),
                now()->endOfYear()
            ),

            'all_time' => $this->setDateRange(null, null),

            'custom' => null,

            default => null,
        };
    }

    protected function setDateRange(
        ?Carbon $start,
        ?Carbon $end
    ): void {
        $this->startDate = $start?->toDateString();
        $this->endDate = $end?->toDateString();
    }

    protected function resetVehiclePagination(): void
    {
        $this->resetPage('vehicleRevenuePage');
    }

    public function updatedCarId(): void
    {
        $this->resetVehiclePagination();
    }

    public function updatedStartDate(): void
    {
        $this->period = 'custom';

        $this->resetVehiclePagination();
    }

    public function updatedEndDate(): void
    {
        $this->period = 'custom';

        $this->resetVehiclePagination();
    }

    protected function applyBookingDateFilters(
        Builder $query
    ): Builder {
        return $query
            ->when(
                $this->startDate,
                fn (Builder $query) => $query->where(
                    'start_datetime',
                    '>=',
                    Carbon::parse($this->startDate)->startOfDay()
                )
            )
            ->when(
                $this->endDate,
                fn (Builder $query) => $query->where(
                    'start_datetime',
                    '<=',
                    Carbon::parse($this->endDate)->endOfDay()
                )
            );
    }

    protected function approvedBookings(): Builder
    {
        $query = Booking::query()
            ->where('status', 'approved')
            ->when(
                $this->carId !== 'all',
                fn (Builder $query) => $query->where(
                    'car_id',
                    $this->carId
                )
            );

        return $this->applyBookingDateFilters($query);
    }

    #[Computed]
    public function vehicleRows(): LengthAwarePaginator
    {
        $start = $this->startDate
            ? Carbon::parse($this->startDate)->startOfDay()
            : null;

        $end = $this->endDate
            ? Carbon::parse($this->endDate)->endOfDay()
            : null;

        return Car::query()
            ->when(
                $this->carId !== 'all',
                fn (Builder $query) => $query->where(
                    'id',
                    $this->carId
                )
            )
            ->withCount([
                'bookings as filtered_bookings_count' => function (
                    Builder $query
                ) use ($start, $end) {
                    $query
                        ->where('status', 'approved')
                        ->when(
                            $start,
                            fn (Builder $query) => $query->where(
                                'start_datetime',
                                '>=',
                                $start
                            )
                        )
                        ->when(
                            $end,
                            fn (Builder $query) => $query->where(
                                'start_datetime',
                                '<=',
                                $end
                            )
                        );
                },
            ])
            ->withSum([
                'bookings as filtered_bookings_total_due' => function (
                    Builder $query
                ) use ($start, $end) {
                    $query
                        ->where('status', 'approved')
                        ->when(
                            $start,
                            fn (Builder $query) => $query->where(
                                'start_datetime',
                                '>=',
                                $start
                            )
                        )
                        ->when(
                            $end,
                            fn (Builder $query) => $query->where(
                                'start_datetime',
                                '<=',
                                $end
                            )
                        );
                },
            ], 'total_due')
            ->orderByDesc('filtered_bookings_total_due')
            ->paginate(
                perPage: 5,
                pageName: 'vehicleRevenuePage'
            );
    }

    public function getTotalRevenue(): float
    {
        return (float) $this->approvedBookings()
            ->sum('total_due');
    }

    public function getTotalBookings(): int
    {
        return $this->approvedBookings()
            ->count();
    }

    public function getAverageRevenuePerBooking(): float
    {
        $count = $this->getTotalBookings();

        return $count > 0
            ? $this->getTotalRevenue() / $count
            : 0;
    }

    public function getTopCar(): ?Car
    {
        if ($this->carId !== 'all') {
            return Car::find($this->carId);
        }

        $booking = $this->approvedBookings()
            ->selectRaw('car_id, SUM(total_due) as revenue_total')
            ->groupBy('car_id')
            ->orderByDesc('revenue_total')
            ->with('car')
            ->first();

        return $booking?->car;
    }

    public function getCarImageUrl(?Car $car): string
    {
        if (
            $car?->image &&
            Storage::disk('public')->exists($car->image)
        ) {
            return Storage::url($car->image);
        }

        return Storage::url('images/default-car.png');
    }
}