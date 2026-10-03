<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesTenantPage;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Partners;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class PartnerCommissionSummary extends Page implements Tables\Contracts\HasTable
{
    use AuthorizesTenantPage;
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.pages.partner-commission-summary';
    protected static ?string $navigationLabel = 'Commissions';
    protected static ?string $title = 'Commissions Report';

    public float $tieUpRevenue = 0;
    public float $partnerCommission = 0;
    public float $companyEarnings = 0;

    public string $period = 'all_time';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public string $partnerId = 'all';

    public int $carPage = 1;
    public int $carPerPage = 10;

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

    public function mount(): void
    {
        $this->applyPeriod('all_time');
        $this->computeSummaryValues();
    }

    public function isTablePaginationEnabled(): bool
    {
        return true;
    }

    public function getTableRecordsPerPage(): int
    {
        return 10;
    }

    public function getTableDefaultSortColumn(): ?string
    {
        return 'name';
    }

    public function getTableDefaultSortDirection(): ?string
    {
        return 'asc';
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->applyPeriod($period);
        $this->refreshReport();
    }

    protected function applyPeriod(string $period): void
    {
        match ($period) {
            'this_month' => $this->setDateRange(now()->startOfMonth(), now()->endOfMonth()),
            'this_year' => $this->setDateRange(now()->startOfYear(), now()->endOfYear()),
            'all_time' => $this->setDateRange(null, null),
            'custom' => null,
            default => null,
        };
    }

    protected function setDateRange(?Carbon $start, ?Carbon $end): void
    {
        $this->startDate = $start?->toDateString();
        $this->endDate = $end?->toDateString();
    }

    public function updatedPartnerId(): void
    {
        $this->refreshReport();
    }

    public function updatedStartDate(): void
    {
        $this->period = 'custom';
        $this->refreshReport();
    }

    public function updatedEndDate(): void
    {
        $this->period = 'custom';
        $this->refreshReport();
    }

    public function updatedCarPerPage(): void
    {
        $this->carPage = 1;
    }

    protected function refreshReport(): void
    {
        $this->computeSummaryValues();
        $this->resetTable();
        $this->carPage = 1;
    }

    protected function applyEligibleBookingFilters($query)
    {
        return $query
            ->where('balance', 0)
            ->where('status', 'approved')
            ->whereNotNull('partner_commission')
            ->whereNotNull('company_earnings')
            ->when(
                $this->startDate,
                fn ($q) => $q->where(
                    'start_datetime',
                    '>=',
                    Carbon::parse($this->startDate)->startOfDay()
                )
            )
            ->when(
                $this->endDate,
                fn ($q) => $q->where(
                    'start_datetime',
                    '<=',
                    Carbon::parse($this->endDate)->endOfDay()
                )
            );
    }

    protected function filteredBookingsQuery(): Builder
    {
        return $this->applyEligibleBookingFilters(
            Booking::query()->whereHas('car.partner')
        )->when(
            $this->partnerId !== 'all',
            fn (Builder $q) => $q->whereHas(
                'car',
                fn (Builder $car) => $car->where('partner_id', $this->partnerId)
            )
        );
    }

    protected function computeSummaryValues(): void
    {
        $summary = $this->filteredBookingsQuery()
            ->selectRaw('
                COALESCE(SUM(paid_amount), 0) as revenue,
                COALESCE(SUM(partner_commission), 0) as partner_income,
                COALESCE(SUM(company_earnings), 0) as company_income
            ')
            ->first();

        $this->tieUpRevenue = (float) ($summary?->revenue ?? 0);
        $this->partnerCommission = (float) ($summary?->partner_income ?? 0);
        $this->companyEarnings = (float) ($summary?->company_income ?? 0);
    }

    public function getQualifiedBookingsProperty(): int
    {
        return $this->filteredBookingsQuery()->count();
    }

    public function getCommissionSplitProperty(): array
    {
        $total = $this->partnerCommission + $this->companyEarnings;

        return [
            'total' => $total,
            'partner' => $this->partnerCommission,
            'company' => $this->companyEarnings,
            'partner_percentage' => $total > 0
                ? round(($this->partnerCommission / $total) * 100, 1)
                : 0,
            'company_percentage' => $total > 0
                ? round(($this->companyEarnings / $total) * 100, 1)
                : 0,
        ];
    }

    public function getPartnerPerformanceProperty(): array
    {
        return Partners::query()
            ->when(
                $this->partnerId !== 'all',
                fn (Builder $q) => $q->where('id', $this->partnerId)
            )
            ->with([
                'cars.bookings' => function ($query) {
                    $this->applyEligibleBookingFilters($query);
                },
            ])
            ->get()
            ->map(function (Partners $partner) {
                $bookings = $partner->cars->flatMap->bookings;
                $activeCars = $partner->cars->filter(fn ($car) => $car->bookings->isNotEmpty());

                return [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'cars' => $activeCars->count(),
                    'bookings' => $bookings->count(),
                    'revenue' => (float) $bookings->sum('paid_amount'),
                    'partner_income' => (float) $bookings->sum('partner_commission'),
                    'company_income' => (float) $bookings->sum('company_earnings'),
                ];
            })
            ->filter(fn ($partner) => $partner['bookings'] > 0)
            ->sortByDesc('revenue')
            ->take(5)
            ->values()
            ->all();
    }

    protected function getTableQuery()
    {
        return Partners::query()
            ->when(
                $this->partnerId !== 'all',
                fn (Builder $q) => $q->where('id', $this->partnerId)
            )
            ->withCount('cars')
            ->with([
                'cars.bookings' => function ($query) {
                    $this->applyEligibleBookingFilters($query);
                },
            ]);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Partner')
                ->searchable()
                ->sortable(),

            TextColumn::make('cars_count')
                ->label('Cars')
                ->numeric()
                ->sortable(),

            TextColumn::make('bookings_count')
                ->label('Bookings')
                ->getStateUsing(
                    fn (Partners $record) => $this->getPartnerBookings($record)->count()
                ),

            TextColumn::make('total_revenue')
                ->label('Total Revenue')
                ->getStateUsing(
                    fn (Partners $record) => $this->getPartnerRevenue($record)
                )
                ->money('PHP', true),

            TextColumn::make('partner_income')
                ->label('Partner Income')
                ->getStateUsing(
                    fn (Partners $record) => $this->getPartnerValue(
                        $record,
                        'partner_commission'
                    )
                )
                ->money('PHP', true),

            TextColumn::make('company_cut')
                ->label('Your Commission')
                ->getStateUsing(
                    fn (Partners $record) => $this->getPartnerValue(
                        $record,
                        'company_earnings'
                    )
                )
                ->money('PHP', true),
        ];
    }

    private function getPartnerBookings(Partners $partner)
    {
        return $partner->cars->flatMap->bookings;
    }

    private function getPartnerValue(Partners $partner, string $field): float
    {
        return (float) $this->getPartnerBookings($partner)->sum($field);
    }

    private function getPartnerRevenue(Partners $partner): float
    {
        return (float) $this->getPartnerBookings($partner)->sum('paid_amount');
    }

    public function getCarBreakdownProperty()
    {
        return Car::query()
            ->whereNotNull('partner_id')
            ->when(
                $this->partnerId !== 'all',
                fn (Builder $q) => $q->where('partner_id', $this->partnerId)
            )
            ->whereHas('bookings', function (Builder $query) {
                $this->applyEligibleBookingFilters($query);
            })
            ->with([
                'partner',
                'bookings' => function ($query) {
                    $this->applyEligibleBookingFilters($query);
                },
            ])
            ->orderBy('name')
            ->get();
    }

    public function getPaginatedCarBreakdownProperty()
    {
        $cars = $this->carBreakdown;
        $total = $cars->count();
        $lastPage = max(1, (int) ceil($total / $this->carPerPage));

        if ($this->carPage > $lastPage) {
            $this->carPage = $lastPage;
        }

        return (object) [
            'items' => $cars
                ->slice(($this->carPage - 1) * $this->carPerPage, $this->carPerPage)
                ->values(),
            'total' => $total,
            'page' => $this->carPage,
            'perPage' => $this->carPerPage,
            'lastPage' => $lastPage,
        ];
    }
}