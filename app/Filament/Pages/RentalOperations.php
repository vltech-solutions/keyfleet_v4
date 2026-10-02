<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\Car;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Contracts\Support\Htmlable;

class RentalOperations extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Rental Operations';

    protected static ?string $title = 'Rental Operations';

    protected static ?string $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.rental-operations';

    public string $selectedDate = '';

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
    }

    public function previousDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)
            ->subDay()
            ->toDateString();
    }

    public function nextDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)
            ->addDay()
            ->toDateString();
    }

    public function goToToday(): void
    {
        $this->selectedDate = now()->toDateString();
    }

    #[Computed]
    public function selectedDateLabel(): string
    {
        $date = Carbon::parse($this->selectedDate);

        if ($date->isToday()) {
            return 'Today, ' . $date->format('F j, Y');
        }

        if ($date->isTomorrow()) {
            return 'Tomorrow, ' . $date->format('F j, Y');
        }

        if ($date->isYesterday()) {
            return 'Yesterday, ' . $date->format('F j, Y');
        }

        return $date->format('l, F j, Y');
    }

    #[Computed]
    public function isToday(): bool
    {
        return Carbon::parse($this->selectedDate)->isToday();
    }

    protected function approvedBookings(): Builder
    {
        return Booking::query()
            ->where('status', 'approved');
    }

    #[Computed]
    public function releases(): Collection
    {
        return $this->approvedBookings()
            ->with('car')
            ->whereDate('start_datetime', $this->selectedDate)
            ->orderBy('start_datetime')
            ->get();
    }

    #[Computed]
    public function returns(): Collection
    {
        return $this->approvedBookings()
            ->with('car')
            ->whereDate('end_datetime', $this->selectedDate)
            ->orderBy('end_datetime')
            ->get();
    }

    #[Computed]
    public function outstandingBookings(): LengthAwarePaginator
    {
        return $this->approvedBookings()
            ->with('car')
            ->where('balance', '>', 0)
            ->orderBy('end_datetime')
            ->paginate(
                perPage: 5,
                pageName: 'outstandingPage'
            );
    }

    #[Computed]
    public function outstandingBalance(): float
    {
        return (float) $this->approvedBookings()
            ->where('balance', '>', 0)
            ->sum('balance');
    }

    #[Computed]
    public function outstandingCount(): int
    {
        return $this->approvedBookings()
            ->where('balance', '>', 0)
            ->count();
    }

    #[Computed]
    public function fleetStatus(): array
    {
        $cars = Car::query()
            ->orderBy('name')
            ->get();

        $available = $cars
            ->filter(
                fn (Car $car) => Car::isAvailableAt(
                    $car->id,
                    now()
                )
            )
            ->values();

        $unavailable = $cars
            ->reject(
                fn (Car $car) => Car::isAvailableAt(
                    $car->id,
                    now()
                )
            )
            ->values();

        return [
            'total' => $cars->count(),

            'available' => [
                'count' => $available->count(),
                'cars' => $available,
            ],

            'unavailable' => [
                'count' => $unavailable->count(),
                'cars' => $unavailable,
            ],
        ];
    }

    public function getBookingUrl(int $bookingId): string
    {
        $resource = 'App\\Filament\\Resources\\BookingResource';

        if (! class_exists($resource)) {
            return '#';
        }

        $pages = $resource::getPages();

        if (isset($pages['edit'])) {
            return $resource::getUrl(
                'edit',
                ['record' => $bookingId]
            );
        }

        if (isset($pages['view'])) {
            return $resource::getUrl(
                'view',
                ['record' => $bookingId]
            );
        }

        return $resource::getUrl();
    }

    public function getBookingReference(Booking $booking): string
    {
        return $booking->booking_number
            ?? $booking->reference_number
            ?? 'Booking #' . $booking->id;
    }

    public function getCarName(Booking $booking): string
    {
        return $booking->car?->name
            ?? trim(
                ($booking->car?->brand ?? '')
                . ' '
                . ($booking->car?->model ?? '')
            )
            ?: 'Vehicle';
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