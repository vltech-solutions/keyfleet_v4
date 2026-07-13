<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingPayments;
use App\Models\Car;
use App\Models\Expense;
use App\Models\FundType;
use App\Models\Partners;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Carbon\Carbon;

class ChatbotService
{
    protected $partnerId;
    protected $companyId;
    protected $useAI;
    protected $model;
    protected $dateRange;
    protected $startDate;
    protected $endDate;

    public function __construct()
    {
        $user = Auth::user();
        if ($user && $user->partner) {
            $this->partnerId = $user->partner->id;
            $this->companyId = $user->company_id;
        }
        
        $this->companyId = Filament::getTenant()?->id;

        $apiKey = config('openai.api_key');
        $this->useAI = $apiKey && !empty($apiKey) && $apiKey !== 'your-api-key-here';
        $this->model = config('openai.model', 'openrouter/free');
        
        // Initialize date range to default (all time)
        $this->dateRange = 'all_time';
        $this->startDate = null;
        $this->endDate = null;
    }

    public function handleQuery($userMessage)
    {
        // Extract date range from the user message
        $this->extractDateRange($userMessage);

        if (!$this->useAI) {
            return $this->getFallbackResponse($userMessage);
        }

        try {
            return $this->handleAIQuery($userMessage);
        } catch (\Exception $e) {
            Log::error('AI Error: ' . $e->getMessage());
            return $this->getFallbackResponse($userMessage);
        }
    }

    /**
     * Extract date range from user message
     */
    protected function extractDateRange($message)
    {
        $message = strtolower($message);
        
        // Reset to default
        $this->dateRange = 'all_time';
        $this->startDate = null;
        $this->endDate = null;
        
        $now = Carbon::now();
        
        // Check for specific date ranges
        if (str_contains($message, 'today') || str_contains($message, 'todays')) {
            $this->dateRange = 'today';
            $this->startDate = $now->copy()->startOfDay();
            $this->endDate = $now->copy()->endOfDay();
            return;
        }
        
        if (str_contains($message, 'yesterday')) {
            $this->dateRange = 'yesterday';
            $this->startDate = $now->copy()->subDay()->startOfDay();
            $this->endDate = $now->copy()->subDay()->endOfDay();
            return;
        }
        
        if (str_contains($message, 'this week')) {
            $this->dateRange = 'this_week';
            $this->startDate = $now->copy()->startOfWeek();
            $this->endDate = $now->copy()->endOfWeek();
            return;
        }
        
        if (str_contains($message, 'last week')) {
            $this->dateRange = 'last_week';
            $this->startDate = $now->copy()->subWeek()->startOfWeek();
            $this->endDate = $now->copy()->subWeek()->endOfWeek();
            return;
        }
        
        if (str_contains($message, 'this month')) {
            $this->dateRange = 'this_month';
            $this->startDate = $now->copy()->startOfMonth();
            $this->endDate = $now->copy()->endOfMonth();
            return;
        }
        
        if (str_contains($message, 'last month')) {
            $this->dateRange = 'last_month';
            $this->startDate = $now->copy()->subMonth()->startOfMonth();
            $this->endDate = $now->copy()->subMonth()->endOfMonth();
            return;
        }
        
        if (str_contains($message, 'this quarter')) {
            $this->dateRange = 'this_quarter';
            $this->startDate = $now->copy()->startOfQuarter();
            $this->endDate = $now->copy()->endOfQuarter();
            return;
        }
        
        if (str_contains($message, 'last quarter')) {
            $this->dateRange = 'last_quarter';
            $this->startDate = $now->copy()->subQuarter()->startOfQuarter();
            $this->endDate = $now->copy()->subQuarter()->endOfQuarter();
            return;
        }
        
        if (str_contains($message, 'this year')) {
            $this->dateRange = 'this_year';
            $this->startDate = $now->copy()->startOfYear();
            $this->endDate = $now->copy()->endOfYear();
            return;
        }
        
        if (str_contains($message, 'last year')) {
            $this->dateRange = 'last_year';
            $this->startDate = $now->copy()->subYear()->startOfYear();
            $this->endDate = $now->copy()->subYear()->endOfYear();
            return;
        }
        
        // Check for specific date format: "January 2024", "Jan 2024", "2024"
        // Month and Year
        if (preg_match('/(january|february|march|april|may|june|july|august|september|october|november|december|jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\s*(\d{4})/i', $message, $matches)) {
            $month = $this->getMonthNumber($matches[1]);
            $year = $matches[2];
            $this->dateRange = 'specific_month';
            $this->startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $this->endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            return;
        }
        
        // Just a year (4 digits)
        if (preg_match('/(\d{4})/', $message, $matches) && !preg_match('/\d{4}-\d{2}-\d{2}/', $message)) {
            $year = $matches[1];
            $this->dateRange = 'specific_year';
            $this->startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $this->endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
            return;
        }
        
        // Date range: "from Jan 1 to Jan 15"
        if (preg_match('/from\s+([a-z]+)\s+(\d{1,2})\s*(?:st|nd|rd|th)?\s*(?:to|until|-)\s*([a-z]+)\s+(\d{1,2})/i', $message, $matches)) {
            // This is complex, but we can try to parse it
            // For simplicity, let's assume the format is "from Jan 1 to Jan 15"
            $startMonth = $this->getMonthNumber($matches[1]);
            $startDay = $matches[2];
            $endMonth = $this->getMonthNumber($matches[3]);
            $endDay = $matches[4];
            
            // Determine year (assume current year if not specified)
            $year = $now->year;
            if ($startMonth > $now->month && $endMonth > $now->month) {
                $year = $year - 1; // If both months are in the future, use last year
            }
            
            $this->dateRange = 'custom_range';
            $this->startDate = Carbon::createFromDate($year, $startMonth, $startDay)->startOfDay();
            $this->endDate = Carbon::createFromDate($year, $endMonth, $endDay)->endOfDay();
            return;
        }
        
        // Fallback: if no specific date range, default to all time
        $this->dateRange = 'all_time';
        $this->startDate = null;
        $this->endDate = null;
    }

    protected function getMonthNumber($monthName)
    {
        $months = [
            'jan' => 1, 'january' => 1,
            'feb' => 2, 'february' => 2,
            'mar' => 3, 'march' => 3,
            'apr' => 4, 'april' => 4,
            'may' => 5,
            'jun' => 6, 'june' => 6,
            'jul' => 7, 'july' => 7,
            'aug' => 8, 'august' => 8,
            'sep' => 9, 'september' => 9,
            'oct' => 10, 'october' => 10,
            'nov' => 11, 'november' => 11,
            'dec' => 12, 'december' => 12
        ];
        
        return $months[strtolower($monthName)] ?? 1;
    }

    protected function getDateRangeLabel()
    {
        switch ($this->dateRange) {
            case 'today':
                return 'today';
            case 'yesterday':
                return 'yesterday';
            case 'this_week':
                return 'this week';
            case 'last_week':
                return 'last week';
            case 'this_month':
                return 'this month';
            case 'last_month':
                return 'last month';
            case 'this_quarter':
                return 'this quarter';
            case 'last_quarter':
                return 'last quarter';
            case 'this_year':
                return 'this year';
            case 'last_year':
                return 'last year';
            case 'specific_month':
                return $this->startDate ? $this->startDate->format('F Y') : 'this month';
            case 'specific_year':
                return $this->startDate ? $this->startDate->format('Y') : 'this year';
            case 'custom_range':
                return ($this->startDate ? $this->startDate->format('M d, Y') : '') . ' to ' . ($this->endDate ? $this->endDate->format('M d, Y') : '');
            default:
                return 'all time';
        }
    }

    protected function applyDateRange($query)
    {
        if ($this->startDate && $this->endDate) {
            return $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }
        return $query;
    }

    protected function handleAIQuery($userMessage)
    {
        // First, detect intent using AI
        $intent = $this->detectIntent($userMessage);
        
        // If intent detection failed, use fallback
        if (!$intent || $intent['action'] === 'general') {
            return $this->generateGeneralResponse($userMessage);
        }
        
        // Fetch data from database with date range
        $data = $this->fetchData($intent);
        
        // Let AI generate a dynamic response based on the data
        return $this->generateDynamicResponse($userMessage, $data, $intent);
    }

    protected function detectIntent($message)
    {
        $prompt = "
        You are a query analyzer for KeyFleet, a car rental management system.
        
        Analyze this user question and determine what data they need:
        '{$message}'
        
        Available actions:
        - earnings_breakdown: Get detailed earnings breakdown
        - total_bookings: Get total number of bookings
        - today_bookings: Get today's bookings
        - upcoming_bookings: Get upcoming bookings
        - recent_bookings: Get recent bookings (last 10)
        - total_revenue: Get total revenue
        - partner_earnings: Get partner earnings
        - company_commission: Get company commission
        - car_status: Get car availability status
        - car_performance: Get car performance
        - car_expenses: Get car expenses
        - fund_balance: Get fund balance
        - partner_info: Get partner information
        - payment_summary: Get payment summary
        - recent_payments: Get recent payments
        - payment_breakdown: Get payment breakdown by fund
        - general: For general questions
        
        Return JSON with:
        {
            \"action\": \"action_name\",
            \"params\": {\"param1\": \"value1\"}
        }
        
        Return only the JSON, no other text.
        ";

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a query analyzer. Return only JSON.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.1,
                'max_tokens' => 150,
            ]);

            $result = json_decode($response->choices[0]->message->content, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($result['action'])) {
                return $result;
            }
        } catch (\Exception $e) {
            Log::error('Intent detection failed: ' . $e->getMessage());
        }

        return $this->simpleIntentDetection($message);
    }

    protected function simpleIntentDetection($message)
    {
        $message = strtolower($message);
        
        if (str_contains($message, 'earn') || str_contains($message, 'revenue') || 
            str_contains($message, 'income') || str_contains($message, 'commission') ||
            str_contains($message, 'profit') || str_contains($message, 'breakdown')) {
            return ['action' => 'earnings_breakdown', 'params' => []];
        }
        
        if (str_contains($message, 'booking') || str_contains($message, 'rental') ||
            str_contains($message, 'reservation')) {
            if (str_contains($message, 'today')) {
                return ['action' => 'today_bookings', 'params' => []];
            }
            if (str_contains($message, 'upcoming') || str_contains($message, 'future')) {
                return ['action' => 'upcoming_bookings', 'params' => []];
            }
            if (str_contains($message, 'total') || str_contains($message, 'all')) {
                return ['action' => 'total_bookings', 'params' => []];
            }
            return ['action' => 'recent_bookings', 'params' => []];
        }
        
        if (str_contains($message, 'car') || str_contains($message, 'vehicle') ||
            str_contains($message, 'fleet')) {
            if (str_contains($message, 'available')) {
                return ['action' => 'car_status', 'params' => []];
            }
            if (str_contains($message, 'best') || str_contains($message, 'top') ||
                str_contains($message, 'performance')) {
                return ['action' => 'car_performance', 'params' => []];
            }
            if (str_contains($message, 'expense') || str_contains($message, 'cost')) {
                return ['action' => 'car_expenses', 'params' => []];
            }
            return ['action' => 'car_status', 'params' => []];
        }
        
        if (str_contains($message, 'fund') || str_contains($message, 'balance')) {
            return ['action' => 'fund_balance', 'params' => []];
        }
        
        if (str_contains($message, 'partner') || str_contains($message, 'info') ||
            str_contains($message, 'profile')) {
            return ['action' => 'partner_info', 'params' => []];
        }

        if (str_contains($message, 'payment') || str_contains($message, 'paid') ||
            str_contains($message, 'received') || str_contains($message, 'collection')) {
            if (str_contains($message, 'recent') || str_contains($message, 'list')) {
                return ['action' => 'recent_payments', 'params' => []];
            }
            if (str_contains($message, 'breakdown') || str_contains($message, 'summary')) {
                return ['action' => 'payment_breakdown', 'params' => []];
            }
            return ['action' => 'payment_summary', 'params' => []];
        }
        
        return ['action' => 'general', 'params' => []];
    }

    protected function fetchData($intent)
    {
        $action = $intent['action'];
        $params = $intent['params'] ?? [];

        $query = Booking::query();
        
        if ($this->partnerId) {
            $query->whereHas('car', function($q) {
                $q->where('partner_id', $this->partnerId);
            });
        }
        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        // Apply date range filter
        if ($action !== 'upcoming_bookings' && $action !== 'car_status') {
            $this->applyDateRange($query);
        }

        switch ($action) {
            case 'earnings_breakdown':
                $totalRevenue = (clone $query)->sum('total_due');
                $partnerEarnings = (clone $query)->sum('partner_commission');
                $companyCommission = (clone $query)->sum('company_earnings');
                $bookingCount = (clone $query)->count();
                $dateLabel = $this->getDateRangeLabel();
                
                return [
                    'type' => 'earnings_breakdown',
                    'title' => 'Earnings Breakdown',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Total Revenue' => '₱' . number_format($totalRevenue, 2),
                        'Partner Earnings' => '₱' . number_format($partnerEarnings, 2),
                        'Company Commission' => '₱' . number_format($companyCommission, 2),
                        'Total Bookings' => $bookingCount,
                        'Partner Share' => $totalRevenue > 0 ? number_format(($partnerEarnings / $totalRevenue) * 100, 1) . '%' : '0%',
                        'Period' => ucfirst($dateLabel)
                    ]
                ];

            case 'total_bookings':
                $count = (clone $query)->count();
                $dateLabel = $this->getDateRangeLabel();
                return [
                    'type' => 'total_bookings',
                    'title' => 'Booking Summary',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Total Bookings' => $count,
                        'Period' => ucfirst($dateLabel)
                    ]
                ];

            case 'today_bookings':
                $bookings = $query->whereDate('created_at', now()->toDateString())->get();
                return [
                    'type' => 'today_bookings',
                    'title' => 'Today\'s Bookings',
                    'date_label' => 'today',
                    'data' => $bookings->map(function($booking) {
                        return [
                            'Booking ID' => '#' . $booking->booking_id,
                            'Renter' => $booking->renter_name,
                            'Amount' => '₱' . number_format($booking->total_due, 2),
                            'Vehicle' => $booking->car->name ?? 'N/A'
                        ];
                    })->toArray()
                ];

            case 'upcoming_bookings':
                $bookings = (clone $query)
                    ->where('status', 'approved')
                    ->where('start_datetime', '>', now())
                    ->limit(10)
                    ->get();
                return [
                    'type' => 'upcoming_bookings',
                    'title' => 'Upcoming Bookings',
                    'date_label' => 'upcoming',
                    'data' => $bookings->map(function($booking) {
                        return [
                            'Booking ID' => '#' . $booking->booking_id,
                            'Renter' => $booking->renter_name,
                            'Start' => $booking->start_datetime->format('M d, Y g:i A'),
                            'Vehicle' => $booking->car->name ?? 'N/A'
                        ];
                    })->toArray()
                ];

            case 'recent_bookings':
                $bookings = (clone $query)->latest()->limit(10)->get();
                $dateLabel = $this->getDateRangeLabel();
                return [
                    'type' => 'recent_bookings',
                    'title' => 'Recent Bookings',
                    'date_label' => $dateLabel,
                    'data' => $bookings->map(function($booking) {
                        return [
                            'Booking ID' => '#' . $booking->booking_id,
                            'Renter' => $booking->renter_name,
                            'Amount' => '₱' . number_format($booking->total_due, 2),
                            'Date' => $booking->created_at->format('M d, Y')
                        ];
                    })->toArray()
                ];

            case 'total_revenue':
                $revenue = (clone $query)->sum('total_due');
                $dateLabel = $this->getDateRangeLabel();
                return [
                    'type' => 'total_revenue',
                    'title' => 'Total Revenue',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Total Revenue' => '₱' . number_format($revenue, 2),
                        'Period' => ucfirst($dateLabel)
                    ]
                ];

            case 'partner_earnings':
                $earnings = (clone $query)->sum('partner_commission');
                $dateLabel = $this->getDateRangeLabel();
                return [
                    'type' => 'partner_earnings',
                    'title' => 'Partner Earnings',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Partner Earnings' => '₱' . number_format($earnings, 2),
                        'Period' => ucfirst($dateLabel)
                    ]
                ];

            case 'company_commission':
                $commission = (clone $query)->sum('company_earnings');
                $dateLabel = $this->getDateRangeLabel();
                return [
                    'type' => 'company_commission',
                    'title' => 'Company Commission',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Company Commission' => '₱' . number_format($commission, 2),
                        'Period' => ucfirst($dateLabel)
                    ]
                ];

            case 'car_status':
                $cars = Car::withCount(['bookings' => function($q) {
                    $q->where('status', 'approved')
                    ->where('start_datetime', '<=', now())
                    ->where('end_datetime', '>=', now());
                }])->when($this->partnerId, function($q) {
                    $q->where('partner_id', $this->partnerId);
                })->get();
                
                return [
                    'type' => 'car_status',
                    'title' => 'Fleet Status',
                    'date_label' => 'current',
                    'data' => [
                        'Total Cars' => $cars->count(),
                        'Available' => $cars->filter(function($car) { return $car->bookings_count == 0; })->count(),
                        'Currently Rented' => $cars->filter(function($car) { return $car->bookings_count > 0; })->count()
                    ]
                ];

            case 'car_performance':
                $cars = Car::withCount(['bookings' => function($q) {
                    if ($this->startDate && $this->endDate) {
                        $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                    }
                    $q->where('status', 'approved');
                }])
                ->when($this->partnerId, function($q) {
                    $q->where('partner_id', $this->partnerId);
                })
                ->orderBy('bookings_count', 'desc')
                ->limit(5)
                ->get();
                
                $dateLabel = $this->getDateRangeLabel();
                return [
                    'type' => 'car_performance',
                    'title' => 'Top Performing Cars',
                    'date_label' => $dateLabel,
                    'data' => $cars->map(function($car, $index) {
                        return [
                            'Rank' => $index + 1,
                            'Car Name' => $car->name,
                            'Total Bookings' => $car->bookings_count
                        ];
                    })->toArray()
                ];

            case 'car_expenses':
                // Car-related expenses (with car_id)
                $carExpensesQuery = Expense::whereNotNull('car_id');
                if ($this->partnerId) {
                    $carExpensesQuery->whereHas('car', function($q) {
                        $q->where('partner_id', $this->partnerId);
                    });
                }
                if ($this->companyId) {
                    $carExpensesQuery->where('company_id', $this->companyId);
                }
                if ($this->startDate && $this->endDate) {
                    $carExpensesQuery->whereBetween('created_at', [$this->startDate, $this->endDate]);
                }
                $carExpenses = $carExpensesQuery->sum('amount');
                $carExpensesCount = $carExpensesQuery->count();
                
                // General expenses (car_id is null)
                $generalExpensesQuery = Expense::whereNull('car_id');
                if ($this->companyId) {
                    $generalExpensesQuery->where('company_id', $this->companyId);
                }
                if ($this->startDate && $this->endDate) {
                    $generalExpensesQuery->whereBetween('created_at', [$this->startDate, $this->endDate]);
                }
                $generalExpenses = $generalExpensesQuery->sum('amount');
                $generalExpensesCount = $generalExpensesQuery->count();
                
                // Get recent expenses for breakdown
                $recentExpensesQuery = Expense::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->when($this->partnerId, function($q) {
                    $q->whereHas('car', function($sub) {
                        $sub->where('partner_id', $this->partnerId);
                    });
                });
                
                if ($this->startDate && $this->endDate) {
                    $recentExpensesQuery->whereBetween('created_at', [$this->startDate, $this->endDate]);
                }
                
                $recentExpenses = $recentExpensesQuery->latest()->limit(10)->get();
                $dateLabel = $this->getDateRangeLabel();
                
                return [
                    'type' => 'car_expenses',
                    'title' => 'Expenses Summary',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Total Expenses' => '₱' . number_format($carExpenses + $generalExpenses, 2),
                        'Car-Related Expenses' => '₱' . number_format($carExpenses, 2) . ' (' . $carExpensesCount . ' items)',
                        'General Expenses' => '₱' . number_format($generalExpenses, 2) . ' (' . $generalExpensesCount . ' items)',
                        'Period' => ucfirst($dateLabel)
                    ],
                    'recent_expenses' => $recentExpenses->map(function($expense) {
                        return [
                            'Description' => $expense->expense_description ?? ($expense->other_payment_type ?? 'N/A'),
                            'Amount' => '₱' . number_format($expense->amount, 2),
                            'Date' => $expense->date ? Carbon::parse($expense->date)->format('M d, Y') : 'N/A',
                            'Type' => $expense->car_id ? 'Car' : 'General',
                            'Car' => $expense->car_id ? ($expense->car->name ?? 'N/A') : 'N/A'
                        ];
                    })->toArray()
                ];

            case 'fund_balance':
                $funds = FundType::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })->get();
                
                return [
                    'type' => 'fund_balance',
                    'title' => 'Fund Balances',
                    'date_label' => 'current',
                    'data' => $funds->map(function($fund) {
                        return [
                            'Fund Name' => $fund->name,
                            'Balance' => '₱' . number_format($fund->balance ?? 0, 2)
                        ];
                    })->toArray()
                ];

            case 'partner_info':
                $partner = Partners::find($this->partnerId);
                if ($partner) {
                    return [
                        'type' => 'partner_info',
                        'title' => 'Partner Information',
                        'date_label' => 'current',
                        'data' => [
                            'Name' => $partner->name,
                            'Email' => $partner->email,
                            'Contact' => $partner->contact_number ?? 'N/A',
                            'Total Cars' => $partner->cars()->count(),
                            'Commission Type' => ucfirst($partner->commission_type ?? 'N/A'),
                            'Commission Value' => $partner->commission_type === 'percentage' 
                                ? ($partner->commission_value ?? 0) . '%' 
                                : '₱' . number_format($partner->commission_value ?? 0, 2)
                        ]
                    ];
                }
                return [
                    'type' => 'partner_info',
                    'title' => 'Partner Information',
                    'date_label' => 'current',
                    'data' => ['Message' => 'Partner information not found']
                ];

            // ============ NEW: PAYMENT RELATED CASES ============
            case 'payment_summary':
                $paymentsQuery = BookingPayments::query();
                if ($this->companyId) {
                    $paymentsQuery->where('company_id', $this->companyId);
                }
                if ($this->startDate && $this->endDate) {
                    $paymentsQuery->whereBetween('created_at', [$this->startDate, $this->endDate]);
                }
                
                $totalPayments = (clone $paymentsQuery)->sum('amount');
                $paymentCount = (clone $paymentsQuery)->count();
                $dateLabel = $this->getDateRangeLabel();
                
                return [
                    'type' => 'payment_summary',
                    'title' => 'Payment Summary',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Total Payments Received' => '₱' . number_format($totalPayments, 2),
                        'Number of Payments' => $paymentCount,
                        'Average Payment' => $paymentCount > 0 ? '₱' . number_format($totalPayments / $paymentCount, 2) : '₱0.00',
                        'Period' => ucfirst($dateLabel)
                    ]
                ];

            case 'recent_payments':
                $paymentsQuery = BookingPayments::with(['booking', 'fundType'])
                    ->when($this->companyId, function($q) {
                        $q->where('company_id', $this->companyId);
                    });
                
                if ($this->startDate && $this->endDate) {
                    $paymentsQuery->whereBetween('created_at', [$this->startDate, $this->endDate]);
                }
                
                $payments = $paymentsQuery->latest()->limit(10)->get();
                $dateLabel = $this->getDateRangeLabel();
                
                return [
                    'type' => 'recent_payments',
                    'title' => 'Recent Payments',
                    'date_label' => $dateLabel,
                    'data' => $payments->map(function($payment) {
                        return [
                            'Payment ID' => $payment->id,
                            'Amount' => '₱' . number_format($payment->amount, 2),
                            'Date' => $payment->payment_date ? Carbon::parse($payment->payment_date)->format('M d, Y') : 'N/A',
                            'Booking' => $payment->booking->booking_id ?? 'N/A',
                            'Fund' => $payment->fundType->name ?? 'N/A',
                            'Notes' => $payment->payment_notes ?? 'N/A'
                        ];
                    })->toArray()
                ];

            case 'payment_breakdown':
                $paymentsQuery = BookingPayments::with('fundType')
                    ->when($this->companyId, function($q) {
                        $q->where('company_id', $this->companyId);
                    });
                
                if ($this->startDate && $this->endDate) {
                    $paymentsQuery->whereBetween('created_at', [$this->startDate, $this->endDate]);
                }
                
                $payments = $paymentsQuery->get();
                $dateLabel = $this->getDateRangeLabel();
                $totalAmount = $payments->sum('amount');
                
                // Group by fund type
                $fundBreakdown = $payments->groupBy('fund_type_id')->map(function($group) {
                    $fund = $group->first()->fundType;
                    return [
                        'Fund Name' => $fund->name ?? 'Uncategorized',
                        'Total Amount' => '₱' . number_format($group->sum('amount'), 2),
                        'Count' => $group->count()
                    ];
                })->values()->toArray();
                
                // Combine summary with breakdown
                $resultData = [
                    'Total Payments' => '₱' . number_format($totalAmount, 2),
                    'Period' => ucfirst($dateLabel)
                ];
                
                // Add fund breakdown as additional rows
                foreach ($fundBreakdown as $index => $fund) {
                    $resultData['Fund: ' . $fund['Fund Name']] = $fund['Total Amount'] . ' (' . $fund['Count'] . ' payments)';
                }
                
                return [
                    'type' => 'payment_breakdown',
                    'title' => 'Payment Breakdown by Fund',
                    'date_label' => $dateLabel,
                    'data' => $resultData
                ];

            default:
                return [
                    'type' => 'general',
                    'title' => 'Information',
                    'date_label' => 'current',
                    'data' => ['Message' => 'No specific data found']
                ];
        }
    }

    /**
     * Generate a dynamic AI response based on the data
     */
    protected function generateDynamicResponse($userMessage, $data, $intent)
    {
        $dataArray = $data['data'] ?? [];
        $title = $data['title'] ?? 'Information';
        $type = $data['type'] ?? 'general';
        $dateLabel = $data['date_label'] ?? '';

        if (empty($dataArray)) {
            return '<p>No data found.</p>';
        }

        // Build a context for the AI to generate a response
        $context = json_encode($dataArray, JSON_PRETTY_PRINT);

        // Determine if it's a list or single item
        $isList = isset($dataArray[0]) && is_array($dataArray[0]);
        $dataType = $isList ? 'list of items' : 'key-value pairs';

        $prompt = "
        You are a helpful assistant for KeyFleet, a car rental management platform.
        
        User question: '{$userMessage}'
        
        Data retrieved from the system:
        {$context}
        
        Data type: {$dataType}
        Title: {$title}
        Period: {$dateLabel}
        
        Your task:
        1. Analyze the data provided
        2. Generate a clear, professional, and helpful response
        3. If it's a list of items, present it in a well-structured format
        4. If it's key-value pairs, present it in a readable format
        5. Use HTML formatting for better presentation
        6. Do not use emojis
        7. Make the response conversational but professional
        8. For income or money related, use Philippine Peso (₱) format
        9. If there's a period/date range mentioned, include it in the response
        
        Format the response using HTML. You can use:
        - <h4> for section headers
        - <table> for tabular data with proper headers
        - <div class=\"grid grid-cols-2 gap-2\"> for key-value pairs
        - <strong> for emphasis
        - <ul> and <li> for lists
        
        The response should be self-contained and ready to display.
        ";

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant for KeyFleet. Generate clean, well-formatted HTML responses. Do not use emojis.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 800,
            ]);

            $htmlResponse = $response->choices[0]->message->content;
            
            // Wrap the response in a container if not already wrapped
            if (!str_contains($htmlResponse, '<div')) {
                $htmlResponse = '<div class="space-y-2">' . $htmlResponse . '</div>';
            }
            
            return $htmlResponse;
        } catch (\Exception $e) {
            Log::error('AI dynamic response generation failed: ' . $e->getMessage());
            return $this->formatFallbackHTML($dataArray, $title, $isList);
        }
    }

    /**
     * Fallback HTML formatting if AI fails
     */
    protected function formatFallbackHTML($dataArray, $title, $isList)
    {
        $html = [];
        $html[] = '<div class="space-y-2">';
        $html[] = '<h4 class="text-lg font-semibold text-gray-900 dark:text-white">' . $title . '</h4>';
        
        if ($isList) {
            // Table
            $headers = array_keys($dataArray[0]);
            $html[] = '<div class="overflow-x-auto my-2">';
            $html[] = '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">';
            $html[] = '<thead class="bg-gray-50 dark:bg-gray-800"><tr>';
            foreach ($headers as $header) {
                $html[] = '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">' . $header . '</th>';
            }
            $html[] = '</tr></thead>';
            $html[] = '<tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">';
            foreach ($dataArray as $row) {
                $html[] = '<tr class="hover:bg-gray-50 dark:hover:bg-gray-800">';
                foreach (array_values($row) as $value) {
                    $html[] = '<td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">' . $value . '</td>';
                }
                $html[] = '</tr>';
            }
            $html[] = '</tbody></table></div>';
        } else {
            // Cards
            $html[] = '<div class="grid grid-cols-2 gap-2 my-2">';
            foreach ($dataArray as $key => $value) {
                $html[] = '<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">';
                $html[] = '<div class="text-xs text-gray-500 dark:text-gray-400">' . $key . '</div>';
                $html[] = '<div class="text-sm font-semibold text-gray-900 dark:text-white">' . $value . '</div>';
                $html[] = '</div>';
            }
            $html[] = '</div>';
        }
        
        $html[] = '</div>';
        return implode("\n", $html);
    }

    protected function generateGeneralResponse($userMessage)
    {
        $prompt = "
        You are a helpful assistant for KeyFleet, a car rental fleet management platform.
        
        User question: {$userMessage}
        
        If the question is about KeyFleet features or general help, provide helpful information.
        If the question seems to ask for specific data (bookings, earnings, cars, etc.), 
        guide them to ask for specific data like:
        - 'How much did I earn?'
        - 'Show me my bookings'
        - 'Which car is performing best?'
        - 'Show me today's bookings'
        
        Format the response with HTML.
        Keep the response professional. Do not use emojis.
        ";

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant for KeyFleet. Be professional. Do not use emojis. Use HTML formatting.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 300,
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error('General response generation failed: ' . $e->getMessage());
            return $this->getFallbackResponse($userMessage);
        }
    }

    protected function getFallbackResponse($userMessage)
    {
        $intent = $this->simpleIntentDetection($userMessage);
        
        if ($intent && $intent['action'] !== 'general') {
            $data = $this->fetchData($intent);
            $dataArray = $data['data'] ?? [];
            $title = $data['title'] ?? 'Information';
            $isList = isset($dataArray[0]) && is_array($dataArray[0]);
            return $this->formatFallbackHTML($dataArray, $title, $isList);
        }
        
        return '<div class="space-y-2">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">KeyFleet Assistant</h4>
            <p class="text-sm text-gray-600 dark:text-gray-300">I can help you with:</p>
            <ul class="list-disc pl-4 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                <li><strong>Earnings</strong> - "How much did I earn this month?"</li>
                <li><strong>Bookings</strong> - "Show me my bookings for today"</li>
                <li><strong>Cars</strong> - "How many cars are available?"</li>
                <li><strong>Performance</strong> - "Which car is performing best this month?"</li>
                <li><strong>Expenses</strong> - "Show me my expenses this year"</li>
            </ul>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Ask me anything about your fleet!</p>
        </div>';
    }
}