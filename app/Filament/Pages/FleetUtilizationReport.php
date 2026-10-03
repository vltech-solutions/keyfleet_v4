<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesTenantPage;

use App\Models\Car;
use App\Models\Booking;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\RawJs;

class FleetUtilizationReport extends Page implements HasForms, HasTable
{
    use AuthorizesTenantPage;

    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.fleet-utilization-report';
    protected static ?string $navigationGroup = 'Reports';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'view_type' => 'whole',
            'period' => 'monthly',
            'date' => now()->format('Y-m'),
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Report Filters')
                ->description('Choose the fleet scope and reporting period.')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 12,
                    ])->schema([

                        Forms\Components\ToggleButtons::make('view_type')
                            ->label('Report Scope')
                            ->options([
                                'whole' => 'Whole Fleet',
                                'per_car' => 'Per Vehicle',
                            ])
                            ->icons([
                                'whole' => 'heroicon-o-truck',
                                'per_car' => 'heroicon-o-user',
                            ])
                            ->colors([
                                'whole' => 'primary',
                                'per_car' => 'primary',
                            ])
                            ->inline()
                            ->grouped()
                            ->default('whole')
                            ->live()
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 3,
                            ]),

                        Forms\Components\ToggleButtons::make('period')
                            ->label('Period')
                            ->options([
                                'monthly' => 'Monthly',
                                'semi_annual' => '6 Months',
                                'annual' => '12 Months',
                            ])
                            ->inline()
                            ->grouped()
                            ->default('monthly')
                            ->live()
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 4,
                            ]),

                        TextInput::make('date')
                            ->label(fn ($get) => match ($get('period')) {
                                'semi_annual' => 'Starting Month',
                                'annual' => 'Starting Month',
                                default => 'Month',
                            })
                            ->type('month')
                            ->required()
                            ->live()
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 2,
                            ]),

                        Select::make('car_id')
                            ->label('Vehicle')
                            ->placeholder('Select vehicle')
                            ->options(
                                Car::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->visible(
                                fn ($get): bool =>
                                    $get('view_type') === 'per_car'
                            )
                            ->required(
                                fn ($get): bool =>
                                    $get('view_type') === 'per_car'
                            )
                            ->live()
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 3,
                            ]),
                    ]),
                ])
                ->compact()
                ->statePath('data'),
        ];
    }

    // public function updatedData()
    // {
    //     $this->dispatch('updateChart', data: $this->chart_data);
    // }

    private function reportCars()
    {
        return Car::query()
            ->when(
                ($this->data['view_type'] ?? 'whole') === 'per_car' && filled($this->data['car_id'] ?? null),
                fn (Builder $query) => $query->where('id', $this->data['car_id'])
            )
            ->get();
    }

    public function getReportDataProperty(): array
    {
        $cars = $this->reportCars();
        $totalRate = $bookedDays = $capacityDays = 0;

        foreach ($cars as $car) {
            $metrics = $this->calculateCarMetrics($car);
            $totalRate += $metrics['rate'];
            $bookedDays += $metrics['booked'];
            $capacityDays += $metrics['available'];
        }

        $rate = $cars->count() ? $totalRate / $cars->count() : 0;

        return [
            'fleet_rate' => round($rate, 1),
            'total_cars' => $cars->count(),
            'booked_days' => $bookedDays,
            'capacity_days' => $capacityDays,
            'unused_days' => max(0, $capacityDays - $bookedDays),
            'remark' => $this->getRecommendation($rate),
        ];
    }

    public function getChartDataProperty(): array
    {
        $period = $this->data['period'] ?? 'monthly';
        $selected = Carbon::parse($this->data['date'] ?? now()->format('Y-m'))->startOfMonth();

        [$start, $months] = match ($period) {
            'annual' => [$selected, 12],
            'semi_annual' => [$selected, 6],
            default => [$selected->copy()->subMonths(5), 6],
        };

        $cars = $this->reportCars();

        return collect(range(0, $months - 1))->map(function ($i) use ($start, $cars) {
            $month = $start->copy()->addMonths($i);
            $rate = $cars->avg(
                fn (Car $car) => $this->calculateCarMetrics(
                    $car,
                    $month->format('Y-m'),
                    'monthly'
                )['rate']
            ) ?? 0;

            return [
                'label' => $month->format('M Y'),
                'short' => $month->format('M'),
                'rate' => round($rate, 1),
            ];
        })->all();
    }

    public function getPerformanceMixProperty(): array
    {
        $bands = [
            'Excellent' => 0,
            'Good' => 0,
            'Moderate' => 0,
            'Low' => 0,
        ];

        foreach ($this->reportCars() as $car) {
            $rate = $this->calculateCarMetrics($car)['rate'];

            match (true) {
                $rate >= 90 => $bands['Excellent']++,
                $rate >= 70 => $bands['Good']++,
                $rate >= 50 => $bands['Moderate']++,
                default => $bands['Low']++,
            };
        }

        return [
            'total' => array_sum($bands),
            'items' => $bands,
        ];
    }

    public function getTopVehiclesProperty(): array
    {
        return $this->reportCars()
            ->map(function (Car $car) {
                return [
                    'name' => $car->name,
                    'plate' => $car->plate_number,
                    'rate' => $this->calculateCarMetrics($car)['rate'],
                ];
            })
            ->sortByDesc('rate')
            ->take(5)
            ->values()
            ->all();
    }

    /**
     * Filament Table Configuration
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $state = $this->form->getRawState();
                
                return Car::query()
                    ->when(
                        ($state['view_type'] ?? 'whole') === 'per_car' && filled($state['car_id'] ?? null),
                        fn (Builder $query) => $query->where('id', $state['car_id'])
                    );
            })
            ->defaultSort(fn ($query) => $this->applyRateSorting($query, 'desc'))
            ->columns([
                ImageColumn::make('image')
                    ->label(''),

                TextColumn::make('name')
                    ->label('Vehicle')
                    ->description(fn (Car $record): string => "{$record->brand} {$record->model} {$record->year}")
                    ->searchable(),

                TextColumn::make('plate_number')
                    ->label('Plate')
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('booked_days')
                    ->label('Booked/Available')
                    ->getStateUsing(fn (Car $record) => $this->calculateCarMetrics($record)['booked'] . ' / ' . $this->calculateCarMetrics($record)['available'] . ' days')
                    ->alignCenter(),

                TextColumn::make('rate')
                    ->label('Utilization')
                    ->getStateUsing(fn (Car $record) => $this->calculateCarMetrics($record)['rate'])
                    ->numeric(1)
                    ->suffix('%')
                    ->badge()
                    ->color(fn ($state) => $this->getColor($state))
                    ->alignCenter()
                    // 2. Enable custom sorting for this virtual column
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $this->applyRateSorting($query, $direction);
                    }),

                TextColumn::make('performance')
                    ->label('Performance')
                    ->getStateUsing(fn (Car $record) => $this->getPerfLabel($this->calculateCarMetrics($record)['rate']))
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'Excellent' => 'success',
                        'Good' => 'info',
                        'Moderate' => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('recommendation')
                    ->label('Recommendation')
                    ->getStateUsing(fn (Car $record) => $this->getRecommendation($this->calculateCarMetrics($record)['rate']))
                    ->wrap()
                    ->color('gray'),
            ]);
    }

    protected function applyRateSorting(Builder $query, string $direction): Builder
    {
        $selectedDate = Carbon::parse($this->data['date'] ?? now());
        $periodType = $this->data['period'] ?? 'monthly';

        $start = $selectedDate->copy()->startOfMonth()->format('Y-m-d H:i:s');
        $end = match ($periodType) {
            'annual' => $selectedDate->copy()->endOfYear()->format('Y-m-d H:i:s'),
            'semi_annual' => $selectedDate->copy()->addMonths(5)->endOfMonth()->format('Y-m-d H:i:s'),
            default => $selectedDate->copy()->endOfMonth()->format('Y-m-d H:i:s'),
        };

        return $query->addSelect([
            'booked_count' => Booking::selectRaw('SUM(DATEDIFF(LEAST(end_datetime, ?), GREATEST(start_datetime, ?)) + 1)', [$end, $start])
                ->whereColumn('car_id', 'cars.id')
                ->where('status', '!=', 'cancelled')
                ->where('start_datetime', '<=', $end)
                ->where('end_datetime', '>=', $start)
        ])->orderBy('booked_count', $direction);
    }

    /**
     * Helper to calculate metrics for a single car row
     */
    private function calculateCarMetrics(Car $car, ?string $overrideDate = null, ?string $overridePeriod = null): array
    {
        // Use overrides if provided (for the chart loop), otherwise use form data
        $periodType = $overridePeriod ?? ($this->data['period'] ?? 'monthly');
        
        $rawDate = $overrideDate ?? ($this->data['date'] ?? now()->format('Y-m'));
        
        $start = Carbon::parse($rawDate)->startOfMonth()->startOfDay();
        
        $end = match ($periodType) {
            // Start Month + 11 months = full year (e.g., March 2026 to Feb 2027)
            'annual' => $start->copy()->addMonths(11)->endOfMonth()->endOfDay(),
            // Start Month + 5 months = 6 months total
            'semi_annual' => $start->copy()->addMonths(5)->endOfMonth()->endOfDay(),
            default => $start->copy()->endOfMonth()->endOfDay(),
        };

        // Inclusive day counting (+1)
        $daysInPeriod = max(1, $start->diffInDays($end)); 

        $bookedDays = Booking::where('car_id', $car->id)
            ->where('status', '!=', 'cancelled')
            ->where('start_datetime', '<=', $end)
            ->where('end_datetime', '>=', $start)
            ->get()
            ->sum(function ($b) use ($start, $end) {
                // Treat everything as Date only to ignore time-of-day noise
                $s = Carbon::parse($b->start_datetime)->startOfDay()->max($start);
                $e = Carbon::parse($b->end_datetime)->startOfDay()->min($end);
                
                // Inclusive counting: Start to End = (diff)
                return max(0, $s->diffInDays($e)); 
            });

        $rate = ($daysInPeriod > 0) ? (round($bookedDays) / round($daysInPeriod)) * 100 : 0;
        $rate = min(100, $rate);

        return [
            'booked' => round($bookedDays),
            'available' => round($daysInPeriod),
            'rate' => round($rate, 1),
        ];
    }

    private function getPerfLabel($r): string {
        return match(true) { $r >= 90 => 'Excellent', $r >= 70 => 'Good', $r >= 50 => 'Moderate', default => 'Poor' };
    }

    private function getRecommendation($r): string {
        return match(true) { $r >= 90 => 'Ready to add vehicles', $r >= 70 => 'Optimize pricing', $r >= 50 => 'Improve demand', default => 'Fix operations' };
    }

    private function getColor($r): string {
        return match(true) { $r >= 90 => 'primary', $r >= 70 => 'info', $r >= 50 => 'warning', default => 'danger' };
    }
}