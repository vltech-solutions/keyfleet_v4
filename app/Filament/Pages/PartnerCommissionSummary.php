<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Partners;
use App\Models\Booking;
use App\Models\Car;
use Filament\Tables;
use Filament\Forms;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Navigation\NavigationItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class PartnerCommissionSummary extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.pages.partner-commission-summary';
    protected static ?string $navigationLabel = 'Commisions';
    protected static ?string $title = 'Commisions Report';

    public float $tieUpRevenue = 0;
    public float $partnerCommission = 0;
    public float $companyEarnings = 0;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public string $partnerId = 'all';

    public static function getNavigationGroup(): ?string
    {
        return 'Reports';
    }

    public static function getNavigationGroupSort(): ?int
    {
        return 3;
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    // protected static ?string $navigationParentItem = 'Reports';


    public function mount(): void
    {
        $this->computeSummaryValues();
    }

    protected function computeSummaryValues(): void
    {
        $query = Booking::whereHas('car.partner')
            ->where('balance', 0)
            ->where('status','approved')
            ->whereNotNull('partner_commission')
            ->whereNotNull('company_earnings');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('start_datetime', [$this->startDate, $this->endDate]);
        }

        if ($this->partnerId !== 'all') {
            $query->whereHas('car', fn ($q) => $q->where('partner_id', $this->partnerId));
        }

        $this->tieUpRevenue = $query->sum('paid_amount');
        $this->partnerCommission = $query->sum('partner_commission');
        $this->companyEarnings = $query->sum('company_earnings');
    }

    protected function getTableQuery()
    {
        return Partners::query()
            ->when($this->partnerId !== 'all', fn ($q) => $q->where('id', $this->partnerId))
            ->with([
                'cars.bookings' => fn ($q) =>
                    $q->where('balance', 0)
                    ->where('status','approved')
                    ->whereNotNull('partner_commission')
                    ->whereNotNull('company_earnings')
                    ->when($this->startDate && $this->endDate, fn ($q) =>
                        $q->whereBetween('start_datetime', [$this->startDate, $this->endDate])
                    )
            ]);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')->label('Partner'),

            TextColumn::make('cars_count')
                ->label('Cars Count')
                ->getStateUsing(fn (Partners $record) => $record->cars->count()),

            TextColumn::make('bookings_count')
                ->label('Booking Count')
                ->getStateUsing(fn (Partners $record) => $this->getPartnerBookings($record)->count()),

            TextColumn::make('total_revenue')
                ->label('Total Revenue')
                ->getStateUsing(fn (Partners $record) => $this->getPartnerRevenue($record))
                ->money('PHP', true),

            TextColumn::make('partner_income')
                ->label('Partner Income')
                ->getStateUsing(fn (Partners $record) =>
                    $this->getPartnerValue($record, 'partner_commission')
                )
                ->money('PHP', true),

            TextColumn::make('company_cut')
                ->label('Your Commission')
                ->getStateUsing(fn (Partners $record) => $this->getPartnerValue($record, 'company_earnings'))
                ->money('PHP', true),
        ];
    }

    private function getPartnerBookings(Partners $partner)
    {
        return $partner->cars->flatMap->bookings;
    }

    private function getPartnerValue(Partners $partner, string $field): float
    {
        return $this->getPartnerBookings($partner)->sum($field);
    }

    private function getPartnerRevenue(Partners $partner): float
    {
        return $this->getPartnerBookings($partner)->sum('paid_amount');
    }

    public function getCarBreakdownProperty()
    {
        $query = Car::query()
            ->whereNotNull('partner_id')
            ->whereHas('bookings', function ($q) {
                $q->where('balance', 0)
                    ->where('status','approved')
                    ->whereNotNull('partner_commission')
                    ->whereNotNull('company_earnings');
                if ($this->startDate && $this->endDate) {
                    $q->whereBetween('start_datetime', [$this->startDate, $this->endDate]);
                }
            });

        if ($this->partnerId !== 'all') {
            $query->where('partner_id', $this->partnerId);
        }

        return $query->with(['partner', 'bookings' => function ($q) {
            $q->where('balance', 0)
                ->where('status','approved')
                ->whereNotNull('partner_commission')
                ->whereNotNull('company_earnings');
            if ($this->startDate && $this->endDate) {
                $q->whereBetween('start_datetime', [$this->startDate, $this->endDate]);
            }
        }])->get();
    }


    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['startDate', 'endDate', 'partnerId'])) {
            $this->computeSummaryValues();
            $this->resetTable();
        }
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    Grid::make()->schema([
                            Select::make('partnerId')
                                ->label('Partner')
                                ->options(['all' => 'All Partners'] + Partners::pluck('name', 'id')->toArray())
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
                        ])
                        ->columns(12),
                ]),
        ];
    }
}
