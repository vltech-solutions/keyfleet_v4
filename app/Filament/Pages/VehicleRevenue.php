<?php

namespace App\Filament\Pages;

use App\Models\Car;
use App\Models\Booking;
use Filament\Pages\Page;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

class VehicleRevenue extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Reports';
    protected static string $view = 'filament.pages.vehicle-revenue';

    public ?string $carId = 'all';
    public ?string $startDate = null;
    public ?string $endDate = null;

    public static function getNavigationGroupSort(): ?int
    {
        return 2;
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    protected function getDefaultTableRecordsPerPage(): int
    {
        return 5;
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    Grid::make(12)->schema([
                        Select::make('carId')
                            ->label('Car')
                            ->options(['all' => 'All Cars'] + Car::query()
                                ->orderBy('brand')
                                ->get()
                                ->mapWithKeys(fn ($car) => [$car->id => "{$car->name} ({$car->brand} {$car->model} - {$car->color})"])
                                ->toArray())
                            ->default('all')
                            ->reactive()
                            ->columnSpan(4),

                        DatePicker::make('startDate')
                            ->label('Start Date')
                            ->reactive()
                            ->nullable()
                            ->columnSpan(4),

                        DatePicker::make('endDate')
                            ->label('End Date')
                            ->reactive()
                            ->nullable()
                            ->columnSpan(4),
                    ]),
                ]),
        ];
    }

    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['startDate', 'endDate', 'carId'])) {
            $this->resetTable();
        }
    }

    protected function getTableQuery()
    {
        return Car::query()
            ->when($this->carId !== 'all', fn ($q) => $q->where('id', $this->carId))
            
            ->withCount([
                'bookings as filtered_bookings_count' => fn ($q) =>
                    $q->where('status', 'approved') // only approved
                    ->when($this->startDate && $this->endDate, fn ($q) =>
                        $q->whereBetween('start_datetime', [$this->startDate, $this->endDate])
                    )
            ])

            ->withSum([
                'bookings as filtered_bookings_total_due' => fn ($q) =>
                    $q->where('status', 'approved') // only approved
                    ->when($this->startDate && $this->endDate, fn ($q) =>
                        $q->whereBetween('start_datetime', [$this->startDate, $this->endDate])
                    )
            ], 'total_due')

            ->with([
                'bookings' => fn ($q) =>
                    $q->where('status', 'approved') // only approved
                    ->when($this->startDate && $this->endDate, fn ($q) =>
                        $q->whereBetween('start_datetime', [$this->startDate, $this->endDate])
                    )
            ])

            ->orderByDesc('filtered_bookings_total_due');
    }


    protected function getTableColumns(): array
    {
        return [
            ImageColumn::make('image')->label('Image'),

            TextColumn::make('name')
                ->label('Car')
                ->color('info')
                ->badge(),

            TextColumn::make('full_details')
                ->label('Vehicle Info')
                ->html()
                ->getStateUsing(fn ($record) =>
                    "{$record->brand} {$record->model} ({$record->year})<br>
                    <span style='color:gray;'>{$record->color} | {$record->seat_count} seater</span>"
                ),

            TextColumn::make('filtered_bookings_count')
                ->label('Bookings')
                ,

            TextColumn::make('total_revenue')
                ->label('Revenue')
                ->getStateUsing(fn ($record) =>
                    '₱' . number_format($record->bookings->sum('total_due'), 2)
                )
                ->color('success') // green color
                ->weight('bold')   // bold text
                ,
        ];
    }

    public function getTotalRevenue()
    {
        return Booking::query()
            ->when($this->carId !== 'all', fn ($q) => $q->where('car_id', $this->carId))
            ->when($this->startDate, fn ($q) => $q->whereDate('start_datetime', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('end_datetime', '<=', $this->endDate))
            ->sum('total_due');
    }

    public function getTotalBookings()
    {
        return Booking::query()
            ->when($this->carId !== 'all', fn ($q) => $q->where('car_id', $this->carId))
            ->when($this->startDate, fn ($q) => $q->whereDate('start_datetime', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('end_datetime', '<=', $this->endDate))
            ->count();
    }

    public function getTopCar()
    {
        $booking = Booking::query()
            ->selectRaw('car_id, SUM(total_due) as total')
            ->when($this->carId !== 'all', fn ($q) => $q->where('car_id', $this->carId))
            ->when($this->startDate, fn ($q) => $q->whereDate('start_datetime', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('end_datetime', '<=', $this->endDate))
            ->groupBy('car_id')
            ->orderByDesc('total')
            ->with('car')
            ->first();
    
        return $booking?->car;
    }
}
