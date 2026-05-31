<?php

namespace App\Filament\Pages;

use App\Models\BookingPayments;
use App\Models\Expense;
use Filament\Pages\Page;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;

class IncomeFlowReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string $view = 'filament.pages.income-flow-report';

    public $type = 'all';
    public $year;
    public $month = 'all';

    public $page = 1;
    public $perPage = 10;
    public $total = 0;

    public $totalIncome = 0;

    public $totalExpenses = 0;

    public $records = [];

    public function mount()
    {
        $this->year = date('Y');
        $this->fetchRecords();
    }

    public function updatedType()
    {
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

    public function updatedPage()
    {
        $this->fetchRecords();
    }

    protected function resetPage()
    {
        $this->page = 1;
    }

    public function updatedPerPage($value)
    {
        // e.g., reset to page 1 or reload data
        $this->resetPage();
        // You may also add logic to reload data if needed
    }

    public function fetchRecords()
    {
        $year = $this->year;
        $month = $this->month;
        $type = $this->type;

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
            ->when($year && $year !== 'all', fn ($q) => $q->whereYear('booking_payments.payment_date', $year))
            ->when($month && $month !== 'all', fn ($q) => $q->whereMonth('booking_payments.payment_date', $month));

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
            ->when($year && $year !== 'all', fn ($q) => $q->whereYear('expenses.date', $year))
            ->when($month && $month !== 'all', fn ($q) => $q->whereMonth('expenses.date', $month));

        $this->totalIncome = BookingPayments::query()
            ->join('fund_types', 'booking_payments.fund_type_id', '=', 'fund_types.id')
            ->join('bookings', 'booking_payments.booking_id', '=', 'bookings.id')
            ->where('fund_types.name', '!=', "Partner's Fund")
            ->when($year && $year !== 'all', fn ($q) => $q->whereYear('booking_payments.payment_date', $year))
            ->when($month && $month !== 'all', fn ($q) => $q->whereMonth('booking_payments.payment_date', $month))
            ->sum('booking_payments.amount');

        $this->totalExpenses = Expense::query()
            ->join('fund_types', 'expenses.fund_type_id', '=', 'fund_types.id')
            ->where('fund_types.name', '!=', "Partner's Fund")
            ->where('expenses.deduct_to_fund', true)
            ->when($year && $year !== 'all', fn ($q) => $q->whereYear('expenses.date', $year))
            ->when($month && $month !== 'all', fn ($q) => $q->whereMonth('expenses.date', $month))
            ->sum('expenses.amount');



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
        return [
            Section::make()
                ->schema([
                    Grid::make(12)->schema([
                        Select::make('type')
                            ->label('Transaction Type')
                            ->options([
                                'all' => 'All',
                                'income' => 'Income',
                                'expense' => 'Expense',
                            ])
                            ->default('all')
                            ->reactive()
                            ->columnSpan(4),

                        Select::make('year')
                            ->label('Year')
                            ->options(fn () => collect(range(date('Y'), 2000))->mapWithKeys(fn ($year) => [$year => $year])->toArray())
                            ->default(date('Y'))
                            ->searchable()
                            ->reactive()
                            ->columnSpan(4),

                        Select::make('month')
                            ->label('Month')
                            ->options([
                                'all' => 'All',
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
                            ->default('all')
                            ->reactive()
                            ->searchable()
                            ->columnSpan(4),
                    ]),
                ]),
        ];
    }
}
