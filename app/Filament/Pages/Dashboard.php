<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\BookingPayments;
use App\Models\Car;
use App\Models\CarType;
use App\Models\Expense;
use App\Models\FundType;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -100;

    public string $financialFilter = 'current_month';

    public int $bookingYear;

    public ?int $carType = null;

    public string $dateTime = '';

    public array $availableCars = [];

    public bool $showAvailableCarsModal = false;

    public string $carTypeName = 'Cars';

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        $this->bookingYear = now()->year;

        $this->dateTime = now()
            ->addDay()
            ->setTime(9, 0)
            ->format('Y-m-d\TH:i');
    }

    public function getGreeting(): string
    {
        return match (true) {
            now()->hour < 12 => 'Good morning',
            now()->hour < 18 => 'Good afternoon',
            default => 'Good evening',
        };
    }

    public function getTenantName(): string
    {
        return Filament::getTenant()?->name ?? 'KeyFleet';
    }

    public function getRentalOperationsUrl(): string
    {
        return \App\Filament\Pages\RentalOperations::getUrl();
    }

    #[Computed]
    public function bookingStats(): array
    {
        $now = now();

        $baseQuery = Booking::query()
            ->where('status', 'approved');

        $upcomingQuery = (clone $baseQuery)
            ->where('start_datetime', '>', $now);

        $ongoingQuery = (clone $baseQuery)
            ->where('start_datetime', '<=', $now)
            ->where('end_datetime', '>=', $now);

        $finishedQuery = (clone $baseQuery)
            ->where('end_datetime', '<', $now);

        return [
            'upcoming' => [
                'count' => (clone $upcomingQuery)->count(),
                'receivables' => (float) (clone $upcomingQuery)->sum('balance'),
            ],

            'ongoing' => [
                'count' => (clone $ongoingQuery)->count(),
                'receivables' => (float) (clone $ongoingQuery)->sum('balance'),
            ],

            'finished' => [
                'count' => (clone $finishedQuery)->count(),
                'receivables' => (float) (clone $finishedQuery)->sum('balance'),
            ],
        ];
    }

    #[Computed]
    public function financialSummary(): array
    {
        $bookings = Booking::query()
            ->where('status', 'approved');

        $expenses = Expense::query();

        $payments = BookingPayments::query()
            ->whereHas('fundType', function ($query) {
                $query->where('name', '!=', "Partner's Fund");
            });

        switch ($this->financialFilter) {
            case 'current_year':
                $bookings->whereYear('start_datetime', now()->year);

                $expenses->whereYear('date', now()->year);

                $payments->whereYear('payment_date', now()->year);

                break;

            case 'current_month':
                $bookings
                    ->whereYear('start_datetime', now()->year)
                    ->whereMonth('start_datetime', now()->month);

                $expenses
                    ->whereYear('date', now()->year)
                    ->whereMonth('date', now()->month);

                $payments
                    ->whereYear('payment_date', now()->year)
                    ->whereMonth('payment_date', now()->month);

                break;
        }

        $totalRevenue = (float) $payments->sum('amount');

        $totalExpenses = (float) $expenses->sum('amount');

        return [
            'bookings' => $bookings->count(),
            'revenue' => $totalRevenue,
            'expenses' => $totalExpenses,
            'profit' => $totalRevenue - $totalExpenses,
        ];
    }

    #[Computed]
    public function bookingTrend(): array
    {
        $bookings = Booking::query()
            ->whereYear('start_datetime', $this->bookingYear)
            ->selectRaw('MONTH(start_datetime) as month, COUNT(*) as total')
            ->groupByRaw('MONTH(start_datetime)')
            ->pluck('total', 'month');

        $items = [];

        for ($month = 1; $month <= 12; $month++) {
            $items[] = [
                'month' => Carbon::createFromDate(
                    $this->bookingYear,
                    $month,
                    1
                )->format('M'),

                'count' => (int) $bookings->get($month, 0),
            ];
        }

        $max = max(
            1,
            collect($items)->max('count')
        );

        return collect($items)
            ->map(function (array $item) use ($max) {
                $item['percentage'] = $item['count'] > 0
                    ? max(
                        7,
                        round(($item['count'] / $max) * 100)
                    )
                    : 0;

                return $item;
            })
            ->all();
    }

    #[Computed]
    public function upcomingBookings(): array
    {
        return Booking::query()
            ->where('start_datetime', '>', now())
            ->where('status', 'approved')
            ->orderBy('start_datetime')
            ->with('car')
            ->limit(4)
            ->get()
            ->map(function (Booking $booking) {
                $start = Carbon::parse($booking->start_datetime);
                $end = Carbon::parse($booking->end_datetime);

                $hours = $start->diffInHours($end);
                $days = $start->diffInDays($end);

                if ($hours > 24) {
                    $duration = round($days)
                        . ' day'
                        . ($days > 1 ? 's' : '');
                } else {
                    $duration = $hours
                        . ' hour'
                        . ($hours > 1 ? 's' : '');
                }

                if (
                    ($booking->extend_hours ?? 0) > 0 &&
                    $hours > 24
                ) {
                    $duration .= ' & '
                        . $booking->extend_hours
                        . ' hour'
                        . ($booking->extend_hours > 1 ? 's' : '');
                }

                return [
                    'id' => $booking->id,

                    'renter_name' => $booking->renter_name,

                    'start' => $start,

                    'end' => $end,

                    'duration' => $duration,

                    'balance' => (float) ($booking->balance ?? 0),

                    'car_name' => $booking->car?->name ?? 'Vehicle',

                    'car_image' => $this->resolveCarImage(
                        $booking->car?->image
                    ),
                ];
            })
            ->all();
    }

    #[Computed]
    public function funds(): array
    {
        return FundType::query()
            ->where('balance', '>', 0)
            ->where('name', '!=', "Partner's Fund")
            ->orderByDesc('balance')
            ->get()
            ->map(fn (FundType $fund) => [
                'id' => $fund->id,
                'name' => $fund->name,
                'balance' => (float) $fund->balance,
            ])
            ->all();
    }

    #[Computed]
    public function fundTotal(): float
    {
        return (float) collect($this->funds)
            ->sum('balance');
    }

    #[Computed]
    public function carTypes(): array
    {
        return CarType::query()
            ->whereNotNull('car_type')
            ->orderBy('car_type')
            ->pluck('car_type', 'id')
            ->toArray();
    }

    #[Computed]
    public function financialTrend(): array
    {
        $revenue = BookingPayments::query()
            ->whereHas('fundType', fn ($q) => $q->where('name', '!=', "Partner's Fund"))
            ->whereYear('payment_date', $this->bookingYear)
            ->selectRaw('MONTH(payment_date) month, SUM(amount) total')
            ->groupByRaw('MONTH(payment_date)')
            ->pluck('total', 'month');

        $expenses = Expense::query()
            ->whereYear('date', $this->bookingYear)
            ->selectRaw('MONTH(date) month, SUM(amount) total')
            ->groupByRaw('MONTH(date)')
            ->pluck('total', 'month');

        return collect(range(1, 12))->map(function ($month) use ($revenue, $expenses) {
            $rev = (float) $revenue->get($month, 0);
            $exp = (float) $expenses->get($month, 0);

            return [
                'month' => Carbon::create(null, $month)->format('M'),
                'revenue' => $rev,
                'expenses' => $exp,
                'profit' => $rev - $exp,
            ];
        })->all();
    }

    #[Computed]
    public function fleetUtilization(): array
    {
        $total = Car::query()->count();

        $active = Booking::query()
            ->where('status', 'approved')
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now())
            ->whereNotNull('car_id')
            ->distinct()
            ->count('car_id');

        return [
            'total' => $total,
            'active' => $active,
            'idle' => max(0, $total - $active),
            'percentage' => $total > 0 ? round(($active / $total) * 100) : 0,
        ];
    }

    #[Computed]
    public function bookingSources(): array
    {
        $sources = Booking::query()
            ->join('sources', 'bookings.source_id', '=', 'sources.id')
            ->whereYear('bookings.start_datetime', $this->bookingYear)
            ->select('sources.source')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('sources.source')
            ->orderByDesc('total')
            ->pluck('total', 'sources.source');

        $total = (int) $sources->sum();

        /*
        * Maximum of 5 displayed entries:
        * Top 4 sources + Others
        */
        if ($sources->count() > 5) {
            $visible = $sources->take(4);
            $others = (int) $sources->slice(4)->sum();

            $visible->put('Others', $others);
        } else {
            $visible = $sources;
        }

        $colors = [
            '#3b82f6',
            '#10b981',
            '#f59e0b',
            '#8b5cf6',
            '#94a3b8',
        ];

        $index = 0;

        return [
            'total' => $total,

            'items' => $visible
                ->map(function ($count, $source) use ($total, $colors, &$index) {
                    return [
                        'name' => $source,
                        'count' => (int) $count,
                        'percentage' => $total > 0
                            ? round(($count / $total) * 100, 1)
                            : 0,
                        'color' => $colors[$index++ % count($colors)],
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    public function checkAvailability(): void
    {
        $this->validate([
            'carType' => [
                'nullable',
                'integer',
                'exists:car_types,id',
            ],

            'dateTime' => [
                'required',
                'date',
            ],
        ]);

        $query = Car::query()
            ->with('carType');

        if ($this->carType) {
            $query->where('car_type_id', $this->carType);

            $this->carTypeName = CarType::query()
                ->find($this->carType)?->car_type ?? 'Cars';
        } else {
            $this->carTypeName = 'Cars';
        }

        $cars = $query
            ->orderBy('car_type_id')
            ->get()
            ->filter(
                fn (Car $car) => Car::isAvailableAt(
                    $car->id,
                    $this->dateTime
                )
            )
            ->values();

        if ($cars->isEmpty()) {
            $this->availableCars = [];

            Notification::make()
                ->title('No available vehicles')
                ->body(
                    'No vehicles are available for the selected date and time.'
                )
                ->warning()
                ->send();

            return;
        }

        $this->availableCars = $cars
            ->map(fn (Car $car) => [
                'id' => $car->id,

                'name' => $car->name,

                'brand' => $car->brand,

                'model' => $car->model,

                'year' => $car->year,

                'image' => $this->resolveCarImage(
                    $car->image
                ),

                'type' => $car->carType?->car_type,

                'fuel_type' => $car->fuel_type,

                'seat_count' => $car->seat_count,

                'transmission' => $car->transmission,

                'coding' => $car->coding,
            ])
            ->all();

        $this->showAvailableCarsModal = true;
    }

    public function closeAvailabilityModal(): void
    {
        $this->showAvailableCarsModal = false;
    }

    protected function resolveCarImage(?string $path): string
    {
        if (
            $path &&
            Storage::disk('public')->exists($path)
        ) {
            return Storage::url($path);
        }

        return Storage::url(
            'images/default-car.png'
        );
    }
}