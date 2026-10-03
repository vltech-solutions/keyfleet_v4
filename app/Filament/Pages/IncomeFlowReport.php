<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesTenantPage;

use App\Models\BookingPayments;
use App\Models\Expense;
use App\Models\FundType;
use Filament\Pages\Page;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\ToggleButtons;
use Carbon\Carbon;

class IncomeFlowReport extends Page
{
    use AuthorizesTenantPage;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static string $view = 'filament.pages.income-flow-report';

    public $filter_period = 'monthly';
    public $type = 'all';
    public $fund_type = 'all';
    public $year;
    public $month = 'all';
    public $date_from;
    public $date_to;

    public $page = 1;
    public $perPage = 10;
    public $total = 0;

    public $totalIncome = 0;
    public $totalExpenses = 0;
    public $netCashFlow = 0;

    public $records = [];

    public function mount()
    {
        $this->year = date('Y');
        $this->month = (string) date('n'); 
        $this->date_from = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->date_to = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->fetchRecords();
    }

    // Individual update methods for each filter
    public function updatedFilterPeriod()
    {
        $this->resetPage();
        $this->fetchRecords();
    }

    public function updatedType()
    {
        $this->resetPage();
        $this->fetchRecords();
    }

    public function updatedFundType()
    {
        // dd($this->fund_type);
        $this->resetPage();
        $this->fetchRecords();
    }

    public function updatedYear()
    {
        $this->resetPage();
        $this->fetchRecords();
    }

    public function updatedMonth()
    {
        $this->resetPage();
        $this->fetchRecords();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
        $this->fetchRecords();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
        $this->fetchRecords();
    }

    protected function resetPage()
    {
        $this->page = 1;
    }

    public function updatedPerPage($value)
    {
        $this->resetPage();
        $this->fetchRecords();
    }

    public function updatedPage()
    {
        $this->fetchRecords();
    }

    public function fetchRecords()
    {
        $type = $this->type;
        $filterPeriod = $this->filter_period;
        $year = $this->year;
        $month = $this->month;
        $dateFrom = $this->date_from;
        $dateTo = $this->date_to;
        $fundType = $this->fund_type;

        // Base payment query
        $payments = BookingPayments::query()
            ->select([
                'booking_payments.payment_date as date',
                'booking_payments.amount',
                'booking_payments.payment_notes as description',
                DB::raw("'income' as type"),
                'booking_payments.fund_type_id as fund_id',
                'fund_types.name as fund_name',
                'booking_payments.id',
                'bookings.id as booking_id',
                'bookings.renter_name',
                'booking_payments.created_at',
            ])
            ->join('fund_types', 'booking_payments.fund_type_id', '=', 'fund_types.id')
            ->join('bookings', 'booking_payments.booking_id', '=', 'bookings.id')
            ->where('fund_types.name', '!=', "Partner's Fund")
            ->when($fundType !== 'all', function ($query) use ($fundType) {
                return $query->where('booking_payments.fund_type_id', $fundType);
            })
            ->when($type === 'income' || $type === 'all', function ($query) use ($filterPeriod, $year, $month, $dateFrom, $dateTo) {
                return $this->applyDateFilters($query, 'booking_payments.payment_date', $filterPeriod, $year, $month, $dateFrom, $dateTo);
            });

        // Base expense query
        $expenses = Expense::query()
            ->select([
                'expenses.date',
                'expenses.amount',
                'expenses.expense_description as description',
                DB::raw("'expense' as type"),
                'expenses.fund_type_id as fund_id',
                'fund_types.name as fund_name',
                'expenses.id',
                DB::raw('NULL as booking_id'),   
                DB::raw('NULL as renter_name'), 
                'expenses.created_at',
            ])
            ->join('fund_types', 'expenses.fund_type_id', '=', 'fund_types.id')
            ->where('fund_types.name', '!=', "Partner's Fund")
            ->where('expenses.deduct_to_fund', true)
            ->when($fundType !== 'all', function ($query) use ($fundType) {
                return $query->where('expenses.fund_type_id', $fundType);
            })
            ->when($type === 'expense' || $type === 'all', function ($query) use ($filterPeriod, $year, $month, $dateFrom, $dateTo) {
                return $this->applyDateFilters($query, 'expenses.date', $filterPeriod, $year, $month, $dateFrom, $dateTo);
            });

        // Calculate totals
        $this->totalIncome = BookingPayments::query()
            ->join('fund_types', 'booking_payments.fund_type_id', '=', 'fund_types.id')
            ->join('bookings', 'booking_payments.booking_id', '=', 'bookings.id')
            ->where('fund_types.name', '!=', "Partner's Fund")
            ->when($fundType !== 'all', function ($query) use ($fundType) {
                return $query->where('booking_payments.fund_type_id', $fundType);
            })
            ->when($filterPeriod === 'monthly', function ($query) use ($year, $month) {
                $query->whereYear('booking_payments.payment_date', $year)
                      ->when($month && $month !== 'all', fn ($q) => $q->whereMonth('booking_payments.payment_date', $month));
            })
            ->when($filterPeriod === 'yearly', function ($query) use ($year) {
                $query->whereYear('booking_payments.payment_date', $year);
            })
            ->when($filterPeriod === 'custom', function ($query) use ($dateFrom, $dateTo) {
                $query->when($dateFrom, fn ($q) => $q->whereDate('booking_payments.payment_date', '>=', $dateFrom))
                      ->when($dateTo, fn ($q) => $q->whereDate('booking_payments.payment_date', '<=', $dateTo));
            })
            ->sum('booking_payments.amount');

        $this->totalExpenses = Expense::query()
            ->join('fund_types', 'expenses.fund_type_id', '=', 'fund_types.id')
            ->where('fund_types.name', '!=', "Partner's Fund")
            ->where('expenses.deduct_to_fund', true)
            ->when($fundType !== 'all', function ($query) use ($fundType) {
                return $query->where('expenses.fund_type_id', $fundType);
            })
            ->when($filterPeriod === 'monthly', function ($query) use ($year, $month) {
                $query->whereYear('expenses.date', $year)
                      ->when($month && $month !== 'all', fn ($q) => $q->whereMonth('expenses.date', $month));
            })
            ->when($filterPeriod === 'yearly', function ($query) use ($year) {
                $query->whereYear('expenses.date', $year);
            })
            ->when($filterPeriod === 'custom', function ($query) use ($dateFrom, $dateTo) {
                $query->when($dateFrom, fn ($q) => $q->whereDate('expenses.date', '>=', $dateFrom))
                      ->when($dateTo, fn ($q) => $q->whereDate('expenses.date', '<=', $dateTo));
            })
            ->sum('expenses.amount');

        $this->netCashFlow = $this->totalIncome - $this->totalExpenses;

        // Union query
        if ($type === 'income') {
            $unionQuery = $payments;
        } elseif ($type === 'expense') {
            $unionQuery = $expenses;
        } else {
            $unionQuery = $payments->unionAll($expenses);
        }

        $allRecords = DB::query()
            ->fromSub($unionQuery, 'ledger')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        $total = $allRecords->count();
        $items = $allRecords->slice(($this->page - 1) * $this->perPage, $this->perPage)->values();

        $this->total = $total;
        $this->records = $items->map(fn($i) => (array) $i)->toArray();
    }

    /**
     * Apply date filters to a query
     */
    protected function applyDateFilters($query, $dateColumn, $filterPeriod, $year, $month, $dateFrom, $dateTo)
    {
        if ($filterPeriod === 'monthly') {
            $query->whereYear($dateColumn, $year)
                  ->when($month && $month !== 'all', fn ($q) => $q->whereMonth($dateColumn, $month));
        } elseif ($filterPeriod === 'yearly') {
            $query->whereYear($dateColumn, $year);
        } elseif ($filterPeriod === 'custom') {
            $query->when($dateFrom, fn ($q) => $q->whereDate($dateColumn, '>=', $dateFrom))
                  ->when($dateTo, fn ($q) => $q->whereDate($dateColumn, '<=', $dateTo));
        }
        
        return $query;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Reports';
    }

    public static function getNavigationGroupSort(): ?int
    {
        return 1;
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    protected function getFormSchema(): array
    {
        $company = Filament::getTenant();

        $fundTypes = FundType::query()
            ->where('company_id', $company->id)
            ->where('name', '!=', "Partner's Fund")
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        return [
            Section::make('Report Filters')
                ->description('Filter the cash flow transactions included in this report.')
                ->icon('heroicon-o-adjustments-horizontal')
                ->compact()
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 12,
                    ])->schema([

                        ToggleButtons::make('filter_period')
                            ->label('Report Period')
                            ->options([
                                'monthly' => 'Monthly',
                                'yearly' => 'Yearly',
                                'custom' => 'Custom',
                            ])
                            ->icons([
                                'monthly' => 'heroicon-o-calendar-days',
                                'yearly' => 'heroicon-o-calendar',
                                'custom' => 'heroicon-o-calendar-date-range',
                            ])
                            ->grouped()
                            ->inline()
                            ->default('monthly')
                            ->live()
                            ->columnSpan(['xl' => 4]),

                        ToggleButtons::make('type')
                            ->label('Transactions')
                            ->options([
                                'all' => 'All',
                                'income' => 'Income',
                                'expense' => 'Expenses',
                            ])
                            ->grouped()
                            ->inline()
                            ->default('all')
                            ->live()
                            ->columnSpan(['xl' => 3]),

                        Select::make('fund_type')
                            ->label('Fund')
                            ->options(['all' => 'All Funds'] + $fundTypes)
                            ->default('all')
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->columnSpan(['xl' => 3]),

                        Select::make('year')
                            ->label('Year')
                            ->options(
                                fn () => collect(range(now()->year, 2000))
                                    ->mapWithKeys(fn ($year) => [$year => $year])
                                    ->all()
                            )
                            ->default(now()->year)
                            ->native(false)
                            ->live()
                            ->hidden(fn ($get) => $get('filter_period') === 'custom')
                            ->columnSpan(['xl' => 2]),

                        Select::make('month')
                            ->label('Month')
                            ->options([
                                'all' => 'All Months',
                                '1' => 'January',
                                '2' => 'February',
                                '3' => 'March',
                                '4' => 'April',
                                '5' => 'May',
                                '6' => 'June',
                                '7' => 'July',
                                '8' => 'August',
                                '9' => 'September',
                                '10' => 'October',
                                '11' => 'November',
                                '12' => 'December',
                            ])
                            ->default((string) now()->month)
                            ->native(false)
                            ->live()
                            ->visible(fn ($get) => $get('filter_period') === 'monthly')
                            ->columnSpan(['xl' => 2]),

                        DatePicker::make('date_from')
                            ->label('From')
                            ->native(false)
                            ->live()
                            ->visible(fn ($get) => $get('filter_period') === 'custom')
                            ->columnSpan(['xl' => 2]),

                        DatePicker::make('date_to')
                            ->label('To')
                            ->native(false)
                            ->live()
                            ->visible(fn ($get) => $get('filter_period') === 'custom')
                            ->afterOrEqual('date_from')
                            ->columnSpan(['xl' => 2]),
                    ]),
                ]),
        ];
    }

    // public function exportPdf()
    // {
    //     $company = Filament::getTenant();
        
    //     // Get the filtered data
    //     $type = $this->type;
    //     $filterPeriod = $this->filter_period;
    //     $year = $this->year;
    //     $month = $this->month;
    //     $dateFrom = $this->date_from;
    //     $dateTo = $this->date_to;
    //     $fundType = $this->fund_type;

    //     // Re-fetch all records without pagination for the PDF
    //     $payments = BookingPayments::query()
    //         ->select([
    //             'booking_payments.payment_date as date',
    //             'booking_payments.amount',
    //             'booking_payments.payment_notes as description',
    //             DB::raw("'income' as type"),
    //             'booking_payments.fund_type_id as fund_id',
    //             'fund_types.name as fund_name',
    //             'booking_payments.id',
    //             'bookings.id as booking_id',
    //             'bookings.renter_name',
    //             'booking_payments.created_at',
    //         ])
    //         ->join('fund_types', 'booking_payments.fund_type_id', '=', 'fund_types.id')
    //         ->join('bookings', 'booking_payments.booking_id', '=', 'bookings.id')
    //         ->where('fund_types.name', '!=', "Partner's Fund")
    //         ->when($fundType !== 'all', function ($query) use ($fundType) {
    //             return $query->where('booking_payments.fund_type_id', $fundType);
    //         })
    //         ->when($type === 'income' || $type === 'all', function ($query) use ($filterPeriod, $year, $month, $dateFrom, $dateTo) {
    //             return $this->applyDateFilters($query, 'booking_payments.payment_date', $filterPeriod, $year, $month, $dateFrom, $dateTo);
    //         });

    //     $expenses = Expense::query()
    //         ->select([
    //             'expenses.date',
    //             'expenses.amount',
    //             'expenses.expense_description as description',
    //             DB::raw("'expense' as type"),
    //             'expenses.fund_type_id as fund_id',
    //             'fund_types.name as fund_name',
    //             'expenses.id',
    //             DB::raw('NULL as booking_id'),   
    //             DB::raw('NULL as renter_name'), 
    //             'expenses.created_at',
    //         ])
    //         ->join('fund_types', 'expenses.fund_type_id', '=', 'fund_types.id')
    //         ->where('fund_types.name', '!=', "Partner's Fund")
    //         ->where('expenses.deduct_to_fund', true)
    //         ->when($fundType !== 'all', function ($query) use ($fundType) {
    //             return $query->where('expenses.fund_type_id', $fundType);
    //         })
    //         ->when($type === 'expense' || $type === 'all', function ($query) use ($filterPeriod, $year, $month, $dateFrom, $dateTo) {
    //             return $this->applyDateFilters($query, 'expenses.date', $filterPeriod, $year, $month, $dateFrom, $dateTo);
    //         });

    //     if ($type === 'income') {
    //         $unionQuery = $payments;
    //     } elseif ($type === 'expense') {
    //         $unionQuery = $expenses;
    //     } else {
    //         $unionQuery = $payments->unionAll($expenses);
    //     }

    //     $records = DB::query()
    //         ->fromSub($unionQuery, 'ledger')
    //         ->orderByDesc('date')
    //         ->orderByDesc('created_at')
    //         ->get();

    //     $data = [
    //         'company' => $company,
    //         'records' => $records,
    //         'totalIncome' => $this->totalIncome,
    //         'totalExpenses' => $this->totalExpenses,
    //         'netCashFlow' => $this->netCashFlow,
    //         'filter_period' => $this->filter_period,
    //         'year' => $this->year,
    //         'month' => $this->month,
    //         'date_from' => $this->date_from,
    //         'date_to' => $this->date_to,
    //         'fund_type' => $this->fund_type,
    //         'type' => $this->type,
    //     ];

    //     $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.income-flow-report', $data);
    //     $pdf->setPaper('a4', 'portrait');
    //     $pdf->setOptions([
    //         'defaultFont' => 'dejavu-sans',
    //         'isHtml5ParserEnabled' => true,
    //         'isRemoteEnabled' => true,
    //     ]);
        
    //     $fileName = 'income-flow-report-' . date('Y-m-d') . '.pdf';
        
    //     return response()->streamDownload(function () use ($pdf) {
    //         echo $pdf->output();
    //     }, $fileName);
    // }
}