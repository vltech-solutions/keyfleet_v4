<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingPayments;
use App\Models\Car;
use App\Models\Customer;
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
    protected $language;

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
        
        $this->dateRange = 'all_time';
        $this->startDate = null;
        $this->endDate = null;
        $this->language = 'en';
    }

    public function handleQuery($userMessage)
    {
        $this->detectLanguage($userMessage);
        $normalizedMessage = $this->normalizeMessage($userMessage);
        $this->extractDateRange($normalizedMessage);

        if (!$this->useAI) {
            return $this->getFallbackResponse($normalizedMessage);
        }

        try {
            return $this->handleAIQuery($normalizedMessage);
        } catch (\Exception $e) {
            Log::error('AI Error: ' . $e->getMessage());
            return $this->getFallbackResponse($normalizedMessage);
        }
    }

    protected function detectLanguage($message)
    {
        $tagalogWords = ['po', 'opo', 'salamat', 'ano', 'saan', 'kailan', 'paano', 'bakit', 'sino', 
                         'magkano', 'ilan', 'meron', 'wala', 'oo', 'hindi', 'ngayon', 'kahapon', 
                         'bukas', 'araw', 'linggo', 'buwan', 'taon', 'kita', 'gasto', 'bayad', 
                         'booking', 'kotse', 'sasakyan', 'kustomer', 'kliyente', 'tubo', 'gastos'];
        
        $messageLower = strtolower($message);
        $tagalogCount = 0;
        
        foreach ($tagalogWords as $word) {
            if (str_contains($messageLower, $word)) {
                $tagalogCount++;
            }
        }
        
        if ($tagalogCount >= 2) {
            $this->language = 'tl';
        } else {
            $this->language = 'en';
        }
    }

    protected function normalizeMessage($message)
    {
        $message = strtolower($message);
        
        $typoMap = [
            'earning' => 'earnings',
            'earnigs' => 'earnings',
            'revenue' => 'revenue',
            'bookng' => 'booking',
            'bokings' => 'bookings',
            'bookingss' => 'bookings',
            'resrvation' => 'reservation',
            'renter' => 'renter',
            'custmer' => 'customer',
            'custome' => 'customer',
            'clients' => 'client',
            'paymet' => 'payment',
            'paymnt' => 'payment',
            'pyament' => 'payment',
            'expnse' => 'expense',
            'expeses' => 'expenses',
            'comision' => 'commission',
            'comission' => 'commission',
            'comisyon' => 'commission',
            'profilt' => 'profit',
            'profitt' => 'profit',
            'avalable' => 'available',
            'availble' => 'available',
            'availlable' => 'available',
            'perfomance' => 'performance',
            'perfrmance' => 'performance',
            'top customer' => 'top_customer',
            'best customer' => 'top_customer',
            'frequent customer' => 'top_customer',
            'balnce' => 'balance',
            'balane' => 'balance',
            'outstanding' => 'outstanding',
            'debt' => 'balance',
            'owing' => 'balance',
            'net revenue' => 'net_revenue',
            'net income' => 'net_revenue',
            'net profit' => 'net_revenue',
            'profit after expenses' => 'net_revenue',
            'car status' => 'car_status',
            'fleet status' => 'car_status',
            'car perfomance' => 'car_performance',
            'top car' => 'car_performance',
            'best car' => 'car_performance',
            'fund balance' => 'fund_balance',
            'fund balace' => 'fund_balance',
            'partner info' => 'partner_info',
            'partners info' => 'partner_info',
            'today bookings' => 'today_bookings',
            'todays bookings' => 'today_bookings',
            'upcoming booking' => 'upcoming_bookings',
            'future booking' => 'upcoming_bookings',
            'recent booking' => 'recent_bookings',
            'latest booking' => 'recent_bookings',
            'all bookings' => 'total_bookings',
            'total booking' => 'total_bookings',
            'payment summary' => 'payment_summary',
            'payments summary' => 'payment_summary',
            'recent payments' => 'recent_payments',
            'latest payments' => 'recent_payments',
            'payment breakdown' => 'payment_breakdown',
            'customer summary' => 'customer_summary',
            'customers summary' => 'customer_summary',
            'recent customers' => 'recent_customers',
            'latest customers' => 'recent_customers',
            'customers with balance' => 'customers_with_balance',
            'customers with debt' => 'customers_with_balance',
        ];
        
        foreach ($typoMap as $typo => $correct) {
            if (str_contains($message, $typo)) {
                $message = str_replace($typo, $correct, $message);
            }
        }
        
        return $message;
    }

    protected function getLanguagePrompt()
    {
        if ($this->language === 'tl') {
            return "Magsalita ka sa Tagalog/Filipino. Gumamit ng magalang at propesyonal na tono. Kung may mga salitang Ingles na walang direktang salin sa Tagalog, gamitin ang Ingles na salita. Siguraduhing malinaw at madaling maintindihan ang iyong sagot.";
        }
        return "Speak in English. Use a professional and polite tone. Make sure your response is clear and easy to understand.";
    }

    protected function extractDateRange($message)
    {
        $message = strtolower($message);
        
        $this->dateRange = 'all_time';
        $this->startDate = null;
        $this->endDate = null;
        
        $now = Carbon::now();
        
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
        
        if (preg_match('/(january|february|march|april|may|june|july|august|september|october|november|december|jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\s*(\d{4})/i', $message, $matches)) {
            $month = $this->getMonthNumber($matches[1]);
            $year = $matches[2];
            $this->dateRange = 'specific_month';
            $this->startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $this->endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            return;
        }
        
        if (preg_match('/(\d{4})/', $message, $matches) && !preg_match('/\d{4}-\d{2}-\d{2}/', $message)) {
            $year = $matches[1];
            $this->dateRange = 'specific_year';
            $this->startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $this->endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
            return;
        }
        
        if (preg_match('/from\s+([a-z]+)\s+(\d{1,2})\s*(?:st|nd|rd|th)?\s*(?:to|until|-)\s*([a-z]+)\s+(\d{1,2})/i', $message, $matches)) {
            $startMonth = $this->getMonthNumber($matches[1]);
            $startDay = $matches[2];
            $endMonth = $this->getMonthNumber($matches[3]);
            $endDay = $matches[4];
            
            $year = $now->year;
            if ($startMonth > $now->month && $endMonth > $now->month) {
                $year = $year - 1;
            }
            
            $this->dateRange = 'custom_range';
            $this->startDate = Carbon::createFromDate($year, $startMonth, $startDay)->startOfDay();
            $this->endDate = Carbon::createFromDate($year, $endMonth, $endDay)->endOfDay();
            return;
        }
        
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
            case 'today': return 'today';
            case 'yesterday': return 'yesterday';
            case 'this_week': return 'this week';
            case 'last_week': return 'last week';
            case 'this_month': return 'this month';
            case 'last_month': return 'last month';
            case 'this_quarter': return 'this quarter';
            case 'last_quarter': return 'last quarter';
            case 'this_year': return 'this year';
            case 'last_year': return 'last year';
            case 'specific_month': return $this->startDate ? $this->startDate->format('F Y') : 'this month';
            case 'specific_year': return $this->startDate ? $this->startDate->format('Y') : 'this year';
            case 'custom_range': return ($this->startDate ? $this->startDate->format('M d, Y') : '') . ' to ' . ($this->endDate ? $this->endDate->format('M d, Y') : '');
            default: return 'all time';
        }
    }

    protected function applyDateRange($query)
    {
        if ($this->startDate && $this->endDate) {
            return $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }
        return $query;
    }

    /**
     * Calculate revenue for a booking based on partner status
     * If car has partner → revenue = company_earnings (commission only)
     * If car is owned by user → revenue = total_due (full amount)
     */
    protected function calculateBookingRevenue($booking)
    {
        if ($booking->car && $booking->car->partner_id) {
            // Car belongs to a partner → only commission is revenue
            return $booking->company_earnings ?? 0;
        }
        // User's own car → full amount is revenue
        return $booking->total_due ?? 0;
    }

    /**
     * Get revenue for a query with partner logic
     */
    protected function getRevenueForQuery($query)
    {
        $bookings = (clone $query)->get();
        $totalRevenue = 0;
        
        foreach ($bookings as $booking) {
            $totalRevenue += $this->calculateBookingRevenue($booking);
        }
        
        return $totalRevenue;
    }

    protected function extractTopNumber($message)
    {
        $message = strtolower($message);
        if (preg_match('/top\s+(\d+)/', $message, $matches)) {
            return intval($matches[1]);
        }
        if (preg_match('/top\b/', $message)) {
            return 5;
        }
        return null;
    }

    protected function extractCarName($message)
    {
        $cars = Car::when($this->companyId, function($q) {
            $q->where('company_id', $this->companyId);
        })->when($this->partnerId, function($q) {
            $q->where('partner_id', $this->partnerId);
        })->get();
        
        $messageLower = strtolower($message);
        
        foreach ($cars as $car) {
            $carNameLower = strtolower($car->name);
            if (str_contains($messageLower, $carNameLower)) {
                return $car;
            }
            if (strpos($carNameLower, $messageLower) !== false || strpos($messageLower, $carNameLower) !== false) {
                return $car;
            }
        }
        
        return null;
    }

    protected function handleAIQuery($userMessage)
    {
        $car = $this->extractCarName($userMessage);
        if ($car) {
            return $this->getCarPerformanceResponse($car, $userMessage);
        }
        
        $intent = $this->detectIntent($userMessage);
        
        // Check for car availability response
        if ($intent && in_array($intent['action'], ['car_availability', 'car_availability_by_date'])) {
            $data = $this->fetchData($intent);
            if (empty($data['data']) || empty($data['data']['cars'])) {
                return $this->generateNoDataResponse($userMessage, $intent);
            }
            return $this->generateCarAvailabilityResponse($userMessage, $data, $intent);
        }
        
        if (!$intent || $intent['action'] === 'general') {
            $context = $this->getContextualData($userMessage);
            return $this->generateSmartResponse($userMessage, $context);
        }
        
        $data = $this->fetchData($intent);
        
        if (empty($data['data'])) {
            return $this->generateNoDataResponse($userMessage, $intent);
        }
        
        return $this->generateDynamicResponse($userMessage, $data, $intent);
    }

    protected function getCarPerformanceResponse($car, $userMessage)
    {
        $dateLabel = $this->getDateRangeLabel();
        $languageInstruction = $this->getLanguagePrompt();
        
        $bookingsQuery = Booking::where('car_id', $car->id)
            ->where('status', 'approved');
        
        if ($this->startDate && $this->endDate) {
            $bookingsQuery->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }
        
        $bookings = (clone $bookingsQuery)->get();
        $totalBookings = $bookings->count();
        $totalRevenue = 0;
        $totalEarnings = 0;
        $totalPaid = 0;
        $totalBalance = 0;
        
        foreach ($bookings as $booking) {
            $totalRevenue += $this->calculateBookingRevenue($booking);
            $totalEarnings += $booking->partner_commission ?? 0;
            $totalPaid += $booking->paid_amount ?? 0;
            $totalBalance += $booking->balance ?? 0;
        }
        
        $isCurrentlyRented = Booking::where('car_id', $car->id)
            ->where('status', 'approved')
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now())
            ->exists();
        
        $recentBookings = (clone $bookingsQuery)
            ->latest()
            ->limit(5)
            ->get();
        
        $status = $isCurrentlyRented ? 'Currently Rented' : ($car->is_available ? 'Available' : 'Not Available');
        
        $data = [
            'Car Name' => $car->name,
            'Brand' => $car->brand ?? 'N/A',
            'Model' => $car->model ?? 'N/A',
            'Plate Number' => $car->plate_number ?? 'N/A',
            'Status' => $status,
            'Total Bookings' => $totalBookings,
            'Total Revenue' => '₱' . number_format($totalRevenue, 2),
            'Partner Earnings' => '₱' . number_format($totalEarnings, 2),
            'Total Paid' => '₱' . number_format($totalPaid, 2),
            'Outstanding Balance' => '₱' . number_format($totalBalance, 2),
            'Period' => ucfirst($dateLabel)
        ];
        
        // Rest of the method...
        $prompt = "
        You are OTTO, a helpful assistant for KeyFleet.
        
        The user asked about car '{$car->name}'.
        
        Here is the data for this car:
        " . json_encode($data, JSON_PRETTY_PRINT) . "
        
        {$languageInstruction}
        
        Your task:
        1. Generate a friendly, professional response about this car
        2. Include the key metrics in a clear, readable format
        3. If there are recent bookings, mention them briefly
        4. Use HTML formatting for better presentation
        5. Do not use emojis
        
        Format the response with:
        - A header with the car name
        - A summary card with the key metrics
        - A section for recent bookings if any
        ";

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are OTTO, a helpful assistant for KeyFleet. Be professional and helpful. Do not use emojis. ' . $languageInstruction],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 800,
            ]);

            $htmlResponse = $response->choices[0]->message->content;
            
            if (!str_contains($htmlResponse, '<div')) {
                $htmlResponse = '<div class="space-y-2">' . $htmlResponse . '</div>';
            }
            
            return $htmlResponse;
            
        } catch (\Exception $e) {
            Log::error('Car performance response failed: ' . $e->getMessage());
            
            $html = [];
            $html[] = '<div class="space-y-2">';
            $html[] = '<h4 class="text-lg font-semibold text-gray-900 dark:text-white">' . $car->name . ' - Performance Summary</h4>';
            $html[] = '<div class="grid grid-cols-2 gap-2 my-2">';
            $html[] = '<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3"><div class="text-xs text-gray-500 dark:text-gray-400">Status</div><div class="text-sm font-semibold text-gray-900 dark:text-white">' . $status . '</div></div>';
            $html[] = '<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3"><div class="text-xs text-gray-500 dark:text-gray-400">Total Bookings</div><div class="text-sm font-semibold text-gray-900 dark:text-white">' . $totalBookings . '</div></div>';
            $html[] = '<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3"><div class="text-xs text-gray-500 dark:text-gray-400">Total Revenue</div><div class="text-sm font-semibold text-gray-900 dark:text-white">₱' . number_format($totalRevenue, 2) . '</div></div>';
            $html[] = '<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3"><div class="text-xs text-gray-500 dark:text-gray-400">Partner Earnings</div><div class="text-sm font-semibold text-gray-900 dark:text-white">₱' . number_format($totalEarnings, 2) . '</div></div>';
            $html[] = '</div>';
            
            if ($recentBookings->count() > 0) {
                $html[] = '<p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Recent Bookings:</p>';
                $html[] = '<ul class="list-disc pl-4 space-y-1 text-sm text-gray-600 dark:text-gray-300">';
                foreach ($recentBookings as $booking) {
                    $html[] = '<li>#' . $booking->booking_id . ' - ' . $booking->renter_name . ' - ₱' . number_format($booking->total_due, 2) . ' (' . $booking->start_datetime->format('M d, Y') . ')</li>';
                }
                $html[] = '</ul>';
            }
            $html[] = '<p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Period: ' . ucfirst($dateLabel) . '</p>';
            $html[] = '</div>';
            
            return implode("\n", $html);
        }
    }

    protected function getContextualData($userMessage)
    {
        $message = strtolower($userMessage);
        $data = [];
        
        if (str_contains($message, 'business') || str_contains($message, 'overview') || 
            str_contains($message, 'summary') || str_contains($message, 'dashboard')) {
            
            $query = Booking::query();
            if ($this->companyId) {
                $query->where('company_id', $this->companyId);
            }
            if ($this->startDate && $this->endDate) {
                $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
            }
            
            $data['total_bookings'] = (clone $query)->count();
            $data['total_revenue'] = $this->getRevenueForQuery($query);
            $data['company_commission'] = (clone $query)->sum('company_earnings');
            $data['total_customers'] = Customer::when($this->companyId, function($q) {
                $q->where('company_id', $this->companyId);
            })->count();
            $data['total_partners'] = Partners::when($this->companyId, function($q) {
                $q->where('company_id', $this->companyId);
            })->count();
            $data['period'] = $this->getDateRangeLabel();
        }
        
        return $data;
    }

    protected function generateSmartResponse($userMessage, $context = [])
    {
        // ... existing method
        $languageInstruction = $this->getLanguagePrompt();
        
        $contextJson = !empty($context) ? json_encode($context, JSON_PRETTY_PRINT) : 'No specific data retrieved.';
        
        $prompt = "
        You are OTTO, a helpful assistant for KeyFleet, a car rental management platform.
        
        User question: '{$userMessage}'
        
        Context data from the system:
        {$contextJson}
        
        {$languageInstruction}
        
        Your task:
        1. Analyze the user's question carefully
        2. If you have relevant data, present it in a helpful way
        3. If you don't have specific data, guide the user on what they can ask
        4. Use HTML formatting for better presentation
        5. Do not use emojis
        6. Be conversational but professional
        
        Available topics users can ask about:
        - Earnings: 'How much did I earn this month?'
        - Bookings: 'Show me my bookings for today'
        - Cars: 'How many cars are available?' or 'How is [car name] doing?'
        - Customers: 'Who are my top customers?'
        - Payments: 'Show me my payment summary'
        - Partners: 'Who are my partners?'
        - Expenses: 'Show me my expenses'
        - Net Revenue: 'What is my net revenue?'
        - Funds: 'Show me my fund balances'
        - Business Summary: 'Show me business summary'
        - Car Ranking: 'Show me car performance ranking'
        - VIP Customers: 'Show me VIP customers'
        - Profit Margin: 'What's my profit margin?'
        - Booking Source: 'Where do bookings come from?'
        - Period Comparison: 'Compare this month to last month'
        
        If the question is a greeting or general chat, respond warmly and list what you can help with.
        ";

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are OTTO, a helpful assistant for KeyFleet. Be professional, friendly, and helpful. Do not use emojis. ' . $languageInstruction],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

            $htmlResponse = $response->choices[0]->message->content;
            
            if (!str_contains($htmlResponse, '<div')) {
                $htmlResponse = '<div class="space-y-2">' . $htmlResponse . '</div>';
            }
            
            return $htmlResponse;
            
        } catch (\Exception $e) {
            Log::error('Smart response generation failed: ' . $e->getMessage());
            return $this->getFallbackResponse($userMessage);
        }
    }

    protected function generateNoDataResponse($userMessage, $intent)
    {
        // ... existing method
        $action = $intent['action'] ?? '';
        $dateLabel = $this->getDateRangeLabel();
        $languageInstruction = $this->getLanguagePrompt();
        
        $actionLabels = [
            'earnings_breakdown' => 'earnings',
            'total_bookings' => 'bookings',
            'today_bookings' => 'today\'s bookings',
            'upcoming_bookings' => 'upcoming bookings',
            'recent_bookings' => 'recent bookings',
            'total_revenue' => 'revenue',
            'partner_earnings' => 'partner earnings',
            'company_commission' => 'company commission',
            'car_status' => 'car status',
            'car_performance' => 'car performance',
            'car_expenses' => 'car expenses',
            'fund_balance' => 'fund balances',
            'partner_info' => 'partner information',
            'payment_summary' => 'payments',
            'recent_payments' => 'recent payments',
            'payment_breakdown' => 'payment breakdown',
            'net_revenue' => 'net revenue',
            'top_customers' => 'top customers',
            'customers_with_balance' => 'customers with balance',
            'recent_customers' => 'recent customers',
            'customer_summary' => 'customer summary',
            'partner_list' => 'partners list',
            'partner_performance' => 'partner performance',
            'business_summary' => 'business summary',
            'car_ranking' => 'car ranking',
            'best_car' => 'best car',
            'customer_retention' => 'customer retention',
            'vip_customers' => 'VIP customers',
            'profit_margin' => 'profit margin',
            'booking_source' => 'booking source',
            'period_comparison' => 'period comparison',
            'daily_report' => 'daily report',
        ];
        
        $actionLabel = $actionLabels[$action] ?? 'that information';
        
        $prompt = "
        You are OTTO, a helpful assistant for KeyFleet.
        
        User asked: '{$userMessage}'
        
        The user was looking for {$actionLabel} for the period: {$dateLabel}.
        
        No data was found for this query.
        
        {$languageInstruction}
        
        Respond politely explaining that no data was found for that specific query, and suggest:
        1. They might want to check a different time period
        2. They can ask about other topics like earnings, bookings, cars, customers, payments, or expenses
        3. Keep the response friendly and helpful
        
        Use HTML formatting.
        Do not use emojis.
        ";

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are OTTO, a helpful assistant for KeyFleet. Be professional and helpful. Do not use emojis. ' . $languageInstruction],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 300,
            ]);

            $htmlResponse = $response->choices[0]->message->content;
            
            if (!str_contains($htmlResponse, '<div')) {
                $htmlResponse = '<div class="space-y-2">' . $htmlResponse . '</div>';
            }
            
            return $htmlResponse;
            
        } catch (\Exception $e) {
            Log::error('No data response generation failed: ' . $e->getMessage());
            return $this->formatFallbackHTML([], 'No Data Found', false);
        }
    }

    protected function detectIntent($message)
    {
        // ... existing method
        $languageInstruction = $this->getLanguagePrompt();
        
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
        - car_status: Get car availability status
        - car_performance: Get car performance
        - car_expenses: Get car expenses
        - fund_balance: Get fund balance
        - partner_info: Get partner information
        - payment_summary: Get payment summary
        - recent_payments: Get recent payments
        - payment_breakdown: Get payment breakdown by fund
        - net_revenue: Get net revenue (revenue - expenses)
        - top_customers: Get top customers
        - customers_with_balance: Get customers with outstanding balance
        - recent_customers: Get recent customers
        - customer_summary: Get customer summary
        - partner_list: Get list of all partners
        - partner_performance: Get partner performance (top partners by bookings)
        - business_summary: Get complete business summary
        - car_ranking: Get car performance ranking
        - best_car: Get best performing car
        - customer_retention: Get customer retention stats
        - vip_customers: Get VIP customers
        - profit_margin: Get profit margin
        - booking_source: Get booking source analysis
        - period_comparison: Compare two periods
        - daily_report: Get daily report
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
                    ['role' => 'system', 'content' => 'You are a query analyzer. Return only JSON. ' . $languageInstruction],
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
        // ... existing method
        $message = strtolower($message);
        
        $car = $this->extractCarName($message);
        if ($car) {
            return ['action' => 'car_performance', 'params' => ['car_id' => $car->id]];
        }
        
        if (str_contains($message, 'business summary') || str_contains($message, 'overview') ||
            str_contains($message, 'business performance') || str_contains($message, 'how is my business')) {
            return ['action' => 'business_summary', 'params' => []];
        }
        
        if (str_contains($message, 'car ranking') || str_contains($message, 'rank cars') ||
            str_contains($message, 'vehicle ranking')) {
            return ['action' => 'car_ranking', 'params' => []];
        }
        
        if (str_contains($message, 'best performing car') || str_contains($message, 'best car') ||
            str_contains($message, 'highest earning car') || str_contains($message, 'most profitable car')) {
            return ['action' => 'best_car', 'params' => []];
        }
        
        if (str_contains($message, 'retention') || str_contains($message, 'repeat customer') ||
            str_contains($message, 'returning customer')) {
            return ['action' => 'customer_retention', 'params' => []];
        }
        
        if (str_contains($message, 'vip') || str_contains($message, 'most valuable') ||
            str_contains($message, 'high value customer')) {
            return ['action' => 'vip_customers', 'params' => []];
        }
        
        if (str_contains($message, 'profit margin') || str_contains($message, 'profitability')) {
            return ['action' => 'profit_margin', 'params' => []];
        }
        
        if (str_contains($message, 'booking source') || str_contains($message, 'source of booking') ||
            str_contains($message, 'where do bookings come from')) {
            return ['action' => 'booking_source', 'params' => []];
        }
        
        if (str_contains($message, 'compare') || str_contains($message, 'vs') ||
            str_contains($message, 'versus') || str_contains($message, 'last month')) {
            return ['action' => 'period_comparison', 'params' => []];
        }
        
        if (str_contains($message, 'daily report') || str_contains($message, 'today report') ||
            str_contains($message, 'report for today')) {
            return ['action' => 'daily_report', 'params' => []];
        }
        
        if (str_contains($message, 'partner') || str_contains($message, 'partners')) {
            if (str_contains($message, 'top') || str_contains($message, 'best') || 
                str_contains($message, 'performance') || str_contains($message, 'bookings')) {
                return ['action' => 'partner_performance', 'params' => []];
            }
            if (str_contains($message, 'list') || str_contains($message, 'how many') || 
                str_contains($message, 'count') || str_contains($message, 'all')) {
                return ['action' => 'partner_list', 'params' => []];
            }
            return ['action' => 'partner_info', 'params' => []];
        }
        
        if (str_contains($message, 'customer') || str_contains($message, 'client') ||
            str_contains($message, 'renter') || str_contains($message, 'top customer') ||
            str_contains($message, 'frequent')) {
            if (str_contains($message, 'top') || str_contains($message, 'best') || 
                str_contains($message, 'frequent') || str_contains($message, 'most')) {
                return ['action' => 'top_customers', 'params' => []];
            }
            if (str_contains($message, 'balance') || str_contains($message, 'owed') ||
                str_contains($message, 'owing') || str_contains($message, 'debt')) {
                return ['action' => 'customers_with_balance', 'params' => []];
            }
            if (str_contains($message, 'recent') || str_contains($message, 'new')) {
                return ['action' => 'recent_customers', 'params' => []];
            }
            return ['action' => 'customer_summary', 'params' => []];
        }
        
        if (str_contains($message, 'net revenue') || str_contains($message, 'net income') ||
            str_contains($message, 'profit') || str_contains($message, 'after expenses') ||
            str_contains($message, 'revenue minus') || str_contains($message, 'revenue after') ||
            str_contains($message, 'net earnings') || str_contains($message, 'net profit')) {
            return ['action' => 'net_revenue', 'params' => []];
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

        // Add this before the car-related checks
        if (str_contains($message, 'available') && 
            (str_contains($message, 'car') || str_contains($message, 'vehicle') || str_contains($message, 'fleet'))) {
            // Check if dates are mentioned
            $dates = $this->extractSpecificDates($message);
            if ($dates['start_date'] || $dates['end_date']) {
                return [
                    'action' => 'car_availability_by_date', 
                    'params' => [
                        'start_date' => $dates['start_date'],
                        'end_date' => $dates['end_date']
                    ]
                ];
            }
            return ['action' => 'car_availability', 'params' => []];
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

        if (str_contains($message, 'expense') || str_contains($message, 'cost')) {
            return ['action' => 'car_expenses', 'params' => []];
        }
        
        if (str_contains($message, 'fund') || str_contains($message, 'balance')) {
            return ['action' => 'fund_balance', 'params' => []];
        }
        
        return ['action' => 'general', 'params' => []];
    }

    protected function fetchData($intent)
    {
        $action = $intent['action'];
        $params = $intent['params'] ?? [];

        $query = Booking::query();
        
        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        if ($action !== 'upcoming_bookings' && $action !== 'car_status') {
            $this->applyDateRange($query);
        }

        switch ($action) {
            // ============ BUSINESS SUMMARY ============
            case 'business_summary':
                $dateLabel = $this->getDateRangeLabel();
                
                // Get revenue from BookingPayments (exclude Partner's Fund)
                $paymentsQuery = BookingPayments::query()
                    ->whereHas('fundType', function($q) {
                        $q->where('name', '!=', "Partner's Fund");
                    });
                
                if ($this->companyId) {
                    $paymentsQuery->where('company_id', $this->companyId);
                }
                if ($this->startDate && $this->endDate) {
                    $paymentsQuery->whereBetween('payment_date', [$this->startDate, $this->endDate]);
                }
                
                $totalRevenue = (clone $paymentsQuery)->sum('amount');
                $paymentCount = (clone $paymentsQuery)->count();
                
                // Get company commission and bookings count
                $bookingsQuery = Booking::query();
                if ($this->companyId) {
                    $bookingsQuery->where('company_id', $this->companyId);
                }
                if ($this->startDate && $this->endDate) {
                    $bookingsQuery->whereBetween('created_at', [$this->startDate, $this->endDate]);
                }
                $companyCommission = (clone $bookingsQuery)->sum('company_earnings');
                $totalBookings = (clone $bookingsQuery)->count();
                
                // Get total expenses
                $totalExpenses = Expense::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->when($this->startDate && $this->endDate, function($q) {
                    $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                })
                ->sum('amount');
                
                $netProfit = $totalRevenue - $totalExpenses;
                
                // Get other metrics
                $totalCustomers = Customer::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })->count();
                
                $totalCars = Car::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })->when($this->partnerId, function($q) {
                    $q->where('partner_id', $this->partnerId);
                })->count();
                
                $partnerCars = Car::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })->whereNotNull('partner_id')->count();
                
                $totalPartners = Partners::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })->count();
                
                return [
                    'type' => 'business_summary',
                    'title' => 'Business Summary',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Total Revenue (Payments)' => '₱' . number_format($totalRevenue, 2),
                        'Total Bookings' => $totalBookings,
                        'Total Expenses' => '₱' . number_format($totalExpenses, 2),
                        'Net Profit' => $netProfit >= 0 ? '₱' . number_format($netProfit, 2) : '-₱' . number_format(abs($netProfit), 2),
                        'Company Commission' => '₱' . number_format($companyCommission, 2),
                        'Total Customers' => $totalCustomers,
                        'Total Cars' => $totalCars,
                        'Partner Cars' => $partnerCars,
                        'Total Partners' => $totalPartners,
                        'Period' => ucfirst($dateLabel)
                    ]
                ];

            // ============ EARNINGS BREAKDOWN ============
            case 'earnings_breakdown':
                $dateLabel = $this->getDateRangeLabel();
                
                $paymentsQuery = BookingPayments::query()
                    ->whereHas('fundType', function($q) {
                        $q->where('name', '!=', "Partner's Fund");
                    });
                
                if ($this->companyId) {
                    $paymentsQuery->where('company_id', $this->companyId);
                }
                if ($this->startDate && $this->endDate) {
                    $paymentsQuery->whereBetween('payment_date', [$this->startDate, $this->endDate]);
                }
                
                $totalRevenue = (clone $paymentsQuery)->sum('amount');
                $paymentCount = (clone $paymentsQuery)->count();
                
                // Get company commission
                $bookingsQuery = Booking::query();
                if ($this->companyId) {
                    $bookingsQuery->where('company_id', $this->companyId);
                }
                if ($this->startDate && $this->endDate) {
                    $bookingsQuery->whereBetween('created_at', [$this->startDate, $this->endDate]);
                }
                $companyCommission = (clone $bookingsQuery)->sum('company_earnings');
                $totalBookings = (clone $bookingsQuery)->count();
                
                return [
                    'type' => 'earnings_breakdown',
                    'title' => 'Earnings Breakdown',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Total Revenue (Payments)' => '₱' . number_format($totalRevenue, 2),
                        'Company Commission' => '₱' . number_format($companyCommission, 2),
                        'Total Bookings' => $totalBookings,
                        'Payment Count' => $paymentCount,
                        'Period' => ucfirst($dateLabel)
                    ]
                ];

            // ============ TOTAL REVENUE ============
            case 'total_revenue':
                $dateLabel = $this->getDateRangeLabel();
                
                $paymentsQuery = BookingPayments::query()
                    ->whereHas('fundType', function($q) {
                        $q->where('name', '!=', "Partner's Fund");
                    });
                
                if ($this->companyId) {
                    $paymentsQuery->where('company_id', $this->companyId);
                }
                if ($this->startDate && $this->endDate) {
                    $paymentsQuery->whereBetween('payment_date', [$this->startDate, $this->endDate]);
                }
                
                $totalRevenue = (clone $paymentsQuery)->sum('amount');
                
                return [
                    'type' => 'total_revenue',
                    'title' => 'Total Revenue',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Total Revenue' => '₱' . number_format($totalRevenue, 2),
                        'Period' => ucfirst($dateLabel)
                    ]
                ];

            // ============ NET REVENUE ============
            case 'net_revenue':
                $dateLabel = $this->getDateRangeLabel();
                
                $paymentsQuery = BookingPayments::query()
                    ->whereHas('fundType', function($q) {
                        $q->where('name', '!=', "Partner's Fund");
                    });
                
                if ($this->companyId) {
                    $paymentsQuery->where('company_id', $this->companyId);
                }
                if ($this->startDate && $this->endDate) {
                    $paymentsQuery->whereBetween('payment_date', [$this->startDate, $this->endDate]);
                }
                
                $totalRevenue = (clone $paymentsQuery)->sum('amount');
                
                $totalExpenses = Expense::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->when($this->startDate && $this->endDate, function($q) {
                    $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                })
                ->sum('amount');
                
                $netRevenue = $totalRevenue - $totalExpenses;
                
                return [
                    'type' => 'net_revenue',
                    'title' => 'Net Revenue Summary',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Total Revenue (Payments)' => '₱' . number_format($totalRevenue, 2),
                        'Total Expenses' => '₱' . number_format($totalExpenses, 2),
                        'Net Revenue' => $netRevenue >= 0 ? '₱' . number_format($netRevenue, 2) : '-₱' . number_format(abs($netRevenue), 2),
                        'Status' => $netRevenue >= 0 ? '✅ Profitable' : '❌ Loss',
                        'Period' => ucfirst($dateLabel)
                    ]
                ];

            // ============ PROFIT MARGIN ============
            case 'profit_margin':
                $dateLabel = $this->getDateRangeLabel();
                
                $paymentsQuery = BookingPayments::query()
                    ->whereHas('fundType', function($q) {
                        $q->where('name', '!=', "Partner's Fund");
                    });
                
                if ($this->companyId) {
                    $paymentsQuery->where('company_id', $this->companyId);
                }
                if ($this->startDate && $this->endDate) {
                    $paymentsQuery->whereBetween('payment_date', [$this->startDate, $this->endDate]);
                }
                
                $totalRevenue = (clone $paymentsQuery)->sum('amount');
                
                $totalExpenses = Expense::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->when($this->startDate && $this->endDate, function($q) {
                    $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                })
                ->sum('amount');
                
                $netProfit = $totalRevenue - $totalExpenses;
                $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;
                
                return [
                    'type' => 'profit_margin',
                    'title' => 'Profit Margin',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Total Revenue (Payments)' => '₱' . number_format($totalRevenue, 2),
                        'Total Expenses' => '₱' . number_format($totalExpenses, 2),
                        'Net Profit' => '₱' . number_format($netProfit, 2),
                        'Profit Margin' => $profitMargin . '%',
                        'Period' => ucfirst($dateLabel)
                    ]
                ];

            // ============ DAILY REPORT ============
            case 'daily_report':
                $dateLabel = $this->getDateRangeLabel();
                
                $paymentsQuery = BookingPayments::query()
                    ->whereHas('fundType', function($q) {
                        $q->where('name', '!=', "Partner's Fund");
                    })
                    ->whereDate('payment_date', now()->toDateString());
                
                if ($this->companyId) {
                    $paymentsQuery->where('company_id', $this->companyId);
                }
                
                $todayRevenue = (clone $paymentsQuery)->sum('amount');
                $todayPaymentCount = (clone $paymentsQuery)->count();
                
                // Today's bookings
                $bookingsQuery = Booking::query();
                if ($this->companyId) {
                    $bookingsQuery->where('company_id', $this->companyId);
                }
                $todayBookings = $bookingsQuery->whereDate('created_at', now()->toDateString())->count();
                
                $activeBookings = Booking::where('status', 'approved')
                    ->whereDate('start_datetime', '<=', now()->toDateString())
                    ->whereDate('end_datetime', '>=', now()->toDateString())
                    ->count();
                
                return [
                    'type' => 'daily_report',
                    'title' => 'Daily Report',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Today\'s Revenue' => '₱' . number_format($todayRevenue, 2),
                        'Today\'s Bookings' => $todayBookings,
                        'Today\'s Payments' => $todayPaymentCount,
                        'Active Bookings Today' => $activeBookings,
                        'Date' => now()->format('F d, Y')
                    ]
                ];

            // ============ PERIOD COMPARISON ============
            case 'period_comparison':
                $currentPeriod = $this->getDateRangeLabel();
                
                $previousStart = $this->startDate ? (clone $this->startDate)->subMonth() : null;
                $previousEnd = $this->endDate ? (clone $this->endDate)->subMonth() : null;
                
                // Current period payments
                $currentPaymentsQuery = BookingPayments::query()
                    ->whereHas('fundType', function($q) {
                        $q->where('name', '!=', "Partner's Fund");
                    });
                
                if ($this->companyId) {
                    $currentPaymentsQuery->where('company_id', $this->companyId);
                }
                if ($this->startDate && $this->endDate) {
                    $currentPaymentsQuery->whereBetween('payment_date', [$this->startDate, $this->endDate]);
                }
                
                $currentRevenue = (clone $currentPaymentsQuery)->sum('amount');
                $currentPaymentCount = (clone $currentPaymentsQuery)->count();
                
                // Previous period payments
                $previousPaymentsQuery = BookingPayments::query()
                    ->whereHas('fundType', function($q) {
                        $q->where('name', '!=', "Partner's Fund");
                    });
                
                if ($this->companyId) {
                    $previousPaymentsQuery->where('company_id', $this->companyId);
                }
                if ($previousStart && $previousEnd) {
                    $previousPaymentsQuery->whereBetween('payment_date', [$previousStart, $previousEnd]);
                }
                
                $previousRevenue = (clone $previousPaymentsQuery)->sum('amount');
                $previousPaymentCount = (clone $previousPaymentsQuery)->count();
                
                // Current period bookings
                $currentBookingsQuery = Booking::query();
                if ($this->companyId) {
                    $currentBookingsQuery->where('company_id', $this->companyId);
                }
                if ($this->startDate && $this->endDate) {
                    $currentBookingsQuery->whereBetween('created_at', [$this->startDate, $this->endDate]);
                }
                $currentBookings = (clone $currentBookingsQuery)->count();
                
                // Previous period bookings
                $previousBookingsQuery = Booking::query();
                if ($this->companyId) {
                    $previousBookingsQuery->where('company_id', $this->companyId);
                }
                if ($previousStart && $previousEnd) {
                    $previousBookingsQuery->whereBetween('created_at', [$previousStart, $previousEnd]);
                }
                $previousBookings = (clone $previousBookingsQuery)->count();
                
                $bookingGrowth = $previousBookings > 0 ? round((($currentBookings - $previousBookings) / $previousBookings) * 100, 1) : 0;
                $revenueGrowth = $previousRevenue > 0 ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1) : 0;
                
                return [
                    'type' => 'period_comparison',
                    'title' => 'Period Comparison',
                    'date_label' => $currentPeriod,
                    'data' => [
                        'Current Period Revenue' => '₱' . number_format($currentRevenue, 2),
                        'Previous Period Revenue' => '₱' . number_format($previousRevenue, 2),
                        'Revenue Growth' => ($revenueGrowth >= 0 ? '+' : '') . $revenueGrowth . '%',
                        'Current Period Bookings' => $currentBookings,
                        'Previous Period Bookings' => $previousBookings,
                        'Booking Growth' => ($bookingGrowth >= 0 ? '+' : '') . $bookingGrowth . '%',
                        'Current Period Payments' => $currentPaymentCount,
                        'Previous Period Payments' => $previousPaymentCount,
                    ]
                ];

            // ============ TOTAL BOOKINGS ============
            case 'total_bookings':
                $query = Booking::query();
                if ($this->companyId) {
                    $query->where('company_id', $this->companyId);
                }
                $this->applyDateRange($query);
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

            // ============ TODAY BOOKINGS ============
            case 'today_bookings':
                $query = Booking::query();
                if ($this->companyId) {
                    $query->where('company_id', $this->companyId);
                }
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

            // ============ UPCOMING BOOKINGS ============
            case 'upcoming_bookings':
                $query = Booking::query();
                if ($this->companyId) {
                    $query->where('company_id', $this->companyId);
                }
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

            // ============ RECENT BOOKINGS ============
            case 'recent_bookings':
                $query = Booking::query();
                if ($this->companyId) {
                    $query->where('company_id', $this->companyId);
                }
                $this->applyDateRange($query);
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

            // ============ CAR STATUS ============
            case 'car_status':
                $cars = Car::withCount(['bookings' => function($q) {
                    $q->where('status', 'approved')
                    ->where('start_datetime', '<=', now())
                    ->where('end_datetime', '>=', now());
                }])->when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })->when($this->partnerId, function($q) {
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

            // ============ CAR RANKING ============
            case 'car_ranking':
                $dateLabel = $this->getDateRangeLabel();
                
                $carRanking = Car::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })->when($this->partnerId, function($q) {
                    $q->where('partner_id', $this->partnerId);
                })
                ->withCount(['bookings' => function($q) {
                    if ($this->startDate && $this->endDate) {
                        $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                    }
                    $q->where('status', 'approved');
                }])
                ->get()
                ->map(function($car) {
                    return [
                        'Rank' => 0,
                        'Car Name' => $car->name,
                        'Total Bookings' => $car->bookings_count,
                        'Model' => $car->model ?? 'N/A',
                        'Status' => $car->is_available ? 'Available' : 'Not Available'
                    ];
                })
                ->sortByDesc('Total Bookings')
                ->values()
                ->map(function($item, $index) {
                    $item['Rank'] = $index + 1;
                    return $item;
                })
                ->toArray();
                
                return [
                    'type' => 'car_ranking',
                    'title' => 'Car Performance Ranking',
                    'date_label' => $dateLabel,
                    'data' => $carRanking
                ];

            // ============ BEST CAR (Revenue from Payments) ============
            case 'best_car':
                $dateLabel = $this->getDateRangeLabel();
                
                $cars = Car::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })->when($this->partnerId, function($q) {
                    $q->where('partner_id', $this->partnerId);
                })->get();
                
                $bestCarData = null;
                $highestRevenue = 0;
                
                foreach ($cars as $car) {
                    $carBookingIds = $car->bookings()->pluck('id');
                    
                    $paymentsQuery = BookingPayments::query()
                        ->whereHas('fundType', function($q) {
                            $q->where('name', '!=', "Partner's Fund");
                        })
                        ->whereIn('booking_id', $carBookingIds);
                    
                    if ($this->startDate && $this->endDate) {
                        $paymentsQuery->whereBetween('payment_date', [$this->startDate, $this->endDate]);
                    }
                    
                    $revenue = (clone $paymentsQuery)->sum('amount');
                    $bookingCount = $car->bookings()->count();
                    
                    if ($revenue > $highestRevenue) {
                        $highestRevenue = $revenue;
                        $bestCarData = [
                            'Car Name' => $car->name,
                            'Total Revenue' => '₱' . number_format($revenue, 2),
                            'Total Bookings' => $bookingCount,
                            'Status' => $car->is_available ? 'Available' : 'Not Available'
                        ];
                    }
                }
                
                return [
                    'type' => 'best_car',
                    'title' => 'Best Performing Car',
                    'date_label' => $dateLabel,
                    'data' => $bestCarData ?? ['Message' => 'No car data found']
                ];

            // ============ CAR PERFORMANCE (Revenue from Payments) ============
            case 'car_performance':
                $carId = $params['car_id'] ?? null;
                $carQuery = Car::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })->when($this->partnerId, function($q) {
                    $q->where('partner_id', $this->partnerId);
                });
                
                if ($carId) {
                    $car = $carQuery->find($carId);
                    $carBookingIds = $car ? $car->bookings()->pluck('id') : collect();
                    
                    $paymentsQuery = BookingPayments::query()
                        ->whereHas('fundType', function($q) {
                            $q->where('name', '!=', "Partner's Fund");
                        })
                        ->whereIn('booking_id', $carBookingIds);
                    
                    if ($this->startDate && $this->endDate) {
                        $paymentsQuery->whereBetween('payment_date', [$this->startDate, $this->endDate]);
                    }
                    
                    $totalRevenue = (clone $paymentsQuery)->sum('amount');
                    $bookingCount = $carBookingIds->count();
                    
                    return [
                        'type' => 'car_performance',
                        'title' => 'Car Performance',
                        'date_label' => $this->getDateRangeLabel(),
                        'data' => [
                            'Car Name' => $car->name ?? 'N/A',
                            'Total Bookings' => $bookingCount,
                            'Total Revenue' => '₱' . number_format($totalRevenue, 2),
                        ]
                    ];
                }
                
                $cars = $carQuery->withCount(['bookings' => function($q) {
                    if ($this->startDate && $this->endDate) {
                        $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                    }
                    $q->where('status', 'approved');
                }])->limit(5)->get();
                
                return [
                    'type' => 'car_performance',
                    'title' => 'Top Performing Cars',
                    'date_label' => $this->getDateRangeLabel(),
                    'data' => $cars->map(function($car) {
                        return [
                            'Car Name' => $car->name,
                            'Total Bookings' => $car->bookings_count,
                        ];
                    })->toArray()
                ];

            // ============ CAR AVAILABILITY ============
            case 'car_availability':
                $dateLabel = $this->getDateRangeLabel();
                
                $cars = Car::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })->when($this->partnerId, function($q) {
                    $q->where('partner_id', $this->partnerId);
                })->get();
                
                $availabilityData = [];
                foreach ($cars as $car) {
                    // Check if car is currently available (for general availability query without dates)
                    $isCurrentlyBooked = Booking::where('car_id', $car->id)
                        ->where('status', 'approved')
                        ->where('start_datetime', '<=', now())
                        ->where('end_datetime', '>=', now())
                        ->exists();
                    
                    $availabilityData[] = [
                        'Car Name' => $car->name,
                        'Model' => $car->model ?? 'N/A',
                        'Status' => $isCurrentlyBooked ? 'Currently Rented' : 'Available',
                        'Availability' => $isCurrentlyBooked ? '❌ Not Available' : '✅ Available'
                    ];
                }
                
                $availableCount = collect($availabilityData)->filter(function($car) {
                    return str_contains($car['Availability'], 'Available');
                })->count();
                
                return [
                    'type' => 'car_availability',
                    'title' => 'Car Availability',
                    'date_label' => $dateLabel,
                    'data' => [
                        'summary' => [
                            'Total Cars' => count($availabilityData),
                            'Available Cars' => $availableCount,
                            'Rented Cars' => count($availabilityData) - $availableCount
                        ],
                        'cars' => $availabilityData
                    ]
                ];

            // ============ CAR AVAILABILITY BY DATE ============
            case 'car_availability_by_date':
                $startDate = $params['start_date'] ?? null;
                $endDate = $params['end_date'] ?? null;
                
                // If no end date, use start date + 1 day for single day availability
                if (!$endDate && $startDate) {
                    $endDate = $startDate->copy()->addDay();
                }
                
                // If no dates provided, use default range (today)
                if (!$startDate) {
                    $startDate = Carbon::now()->startOfDay();
                }
                if (!$endDate) {
                    $endDate = Carbon::now()->addDay()->endOfDay();
                }
                
                $dateLabel = $startDate->format('M d, Y') . ' to ' . $endDate->format('M d, Y');
                
                $cars = Car::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })->when($this->partnerId, function($q) {
                    $q->where('partner_id', $this->partnerId);
                })->get();
                
                $availabilityData = [];
                $availableCount = 0;
                $bookedCount = 0;
                
                foreach ($cars as $car) {
                    $isAvailable = Car::isAvailableAt(
                        $car->id,
                        $startDate,
                        $endDate
                    );
                    
                    $carData = [
                        'Car Name' => $car->name,
                        'Model' => $car->model ?? 'N/A',
                        'Plate Number' => $car->plate_number ?? 'N/A',
                        'Availability' => $isAvailable ? '✅ Available' : '❌ Not Available',
                        'Status' => $isAvailable ? 'Available' : 'Booked'
                    ];
                    
                    if ($isAvailable) {
                        $availableCount++;
                    } else {
                        $bookedCount++;
                    }
                    
                    $availabilityData[] = $carData;
                }
                
                // Sort: Available cars first
                usort($availabilityData, function($a, $b) {
                    return strcmp($b['Availability'], $a['Availability']);
                });
                
                return [
                    'type' => 'car_availability_by_date',
                    'title' => 'Car Availability',
                    'date_label' => $dateLabel,
                    'data' => [
                        'summary' => [
                            'Total Cars' => count($availabilityData),
                            'Available Cars' => $availableCount,
                            'Booked Cars' => $bookedCount,
                            'Date Range' => $dateLabel
                        ],
                        'cars' => $availabilityData
                    ]
                ];

            // ============ CAR EXPENSES ============
            case 'car_expenses':
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
                
                $generalExpensesQuery = Expense::whereNull('car_id');
                if ($this->companyId) {
                    $generalExpensesQuery->where('company_id', $this->companyId);
                }
                if ($this->startDate && $this->endDate) {
                    $generalExpensesQuery->whereBetween('created_at', [$this->startDate, $this->endDate]);
                }
                $generalExpenses = $generalExpensesQuery->sum('amount');
                $generalExpensesCount = $generalExpensesQuery->count();
                
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
                    ]
                ];

            // ============ FUND BALANCE ============
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

            // ============ PARTNER INFO ============
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

            // ============ PARTNER LIST ============
            case 'partner_list':
                $dateLabel = $this->getDateRangeLabel();
                
                $partners = Partners::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->withCount(['cars'])
                ->get()
                ->map(function($partner) {
                    $totalBookings = Booking::whereHas('car', function($q) use ($partner) {
                        $q->where('partner_id', $partner->id);
                    })->count();
                    
                    return [
                        'Partner Name' => $partner->name,
                        'Email' => $partner->email,
                        'Total Cars' => $partner->cars_count,
                        'Total Bookings' => $totalBookings,
                        'Contact' => $partner->contact_number ?? 'N/A'
                    ];
                })
                ->toArray();
                
                return [
                    'type' => 'partner_list',
                    'title' => 'Partners List',
                    'date_label' => $dateLabel,
                    'data' => $partners
                ];

            // ============ PARTNER PERFORMANCE ============
            case 'partner_performance':
                $dateLabel = $this->getDateRangeLabel();
                $topLimit = $this->extractTopNumber($intent['original_message'] ?? '');
                
                if (!$topLimit) {
                    $topLimit = 5;
                }
                
                $partners = Partners::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->withCount(['cars'])
                ->get()
                ->map(function($partner) {
                    $bookings = Booking::whereHas('car', function($q) use ($partner) {
                        $q->where('partner_id', $partner->id);
                    });
                    
                    $totalBookings = $bookings->count();
                    $totalRevenue = $bookings->sum('total_due');
                    $partnerEarnings = $bookings->sum('partner_commission');
                    
                    return [
                        'Partner Name' => $partner->name,
                        'Total Bookings' => $totalBookings,
                        'Total Revenue' => '₱' . number_format($totalRevenue, 2),
                        'Partner Earnings' => '₱' . number_format($partnerEarnings, 2),
                        'Total Cars' => $partner->cars_count
                    ];
                })
                ->filter(function($partner) {
                    return $partner['Total Bookings'] > 0;
                })
                ->sortByDesc('Total Bookings')
                ->take($topLimit)
                ->values()
                ->toArray();
                
                $rankLabel = $topLimit === 1 ? 'Top Partner' : 'Top ' . $topLimit . ' Partners';
                
                return [
                    'type' => 'partner_performance',
                    'title' => $rankLabel,
                    'date_label' => $dateLabel,
                    'data' => $partners
                ];

            // ============ TOP CUSTOMERS ============
            case 'top_customers':
                $dateLabel = $this->getDateRangeLabel();
                $topLimit = $this->extractTopNumber($intent['original_message'] ?? '');
                if (!$topLimit) {
                    $topLimit = 5;
                }
                
                $customers = Customer::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->with(['bookings' => function($q) use ($query) {
                    if ($this->startDate && $this->endDate) {
                        $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                    }
                    $q->where('status', 'approved');
                }])
                ->get()
                ->map(function($customer) {
                    $totalSpent = $customer->bookings->sum('total_due');
                    $bookingCount = $customer->bookings->count();
                    return [
                        'Customer Name' => $customer->customer_name,
                        'Total Spent' => '₱' . number_format($totalSpent, 2),
                        'Bookings' => $bookingCount,
                        'Contact' => $customer->contact_number ?? 'N/A'
                    ];
                })
                ->filter(function($customer) {
                    return $customer['Bookings'] > 0;
                })
                ->sortByDesc('Bookings')
                ->take($topLimit)
                ->values()
                ->toArray();
                
                $rankLabel = $topLimit === 1 ? 'Top Customer' : 'Top ' . $topLimit . ' Customers';
                
                return [
                    'type' => 'top_customers',
                    'title' => $rankLabel,
                    'date_label' => $dateLabel,
                    'data' => $customers
                ];

            // ============ CUSTOMERS WITH BALANCE ============
            case 'customers_with_balance':
                $dateLabel = $this->getDateRangeLabel();
                
                $customersWithBalance = Customer::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->with(['bookings' => function($q) {
                    $q->where('status', 'approved');
                    if ($this->startDate && $this->endDate) {
                        $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                    }
                }])
                ->get()
                ->map(function($customer) {
                    $totalBalance = $customer->bookings->sum('balance');
                    $totalPaid = $customer->bookings->sum('paid_amount');
                    $totalDue = $customer->bookings->sum('total_due');
                    return [
                        'Customer Name' => $customer->customer_name,
                        'Total Balance' => '₱' . number_format($totalBalance, 2),
                        'Total Paid' => '₱' . number_format($totalPaid, 2),
                        'Total Due' => '₱' . number_format($totalDue, 2),
                        'Contact' => $customer->contact_number ?? 'N/A'
                    ];
                })
                ->filter(function($customer) {
                    return $customer['Total Balance'] > 0;
                })
                ->sortByDesc('Total Balance')
                ->take(10)
                ->values()
                ->toArray();
                
                return [
                    'type' => 'customers_with_balance',
                    'title' => 'Customers with Outstanding Balance',
                    'date_label' => $dateLabel,
                    'data' => $customersWithBalance
                ];

            // ============ RECENT CUSTOMERS ============
            case 'recent_customers':
                $dateLabel = $this->getDateRangeLabel();
                
                $recentCustomers = Customer::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->with(['bookings' => function($q) {
                    $q->latest()->limit(1);
                }])
                ->whereHas('bookings', function($q) {
                    if ($this->startDate && $this->endDate) {
                        $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                    }
                })
                ->latest()
                ->limit(10)
                ->get()
                ->map(function($customer) {
                    $lastBooking = $customer->bookings->first();
                    return [
                        'Customer Name' => $customer->customer_name,
                        'Contact' => $customer->contact_number ?? 'N/A',
                        'Last Booking' => $lastBooking ? $lastBooking->created_at->format('M d, Y') : 'N/A',
                        'Email' => $customer->email ?? 'N/A'
                    ];
                })
                ->toArray();
                
                return [
                    'type' => 'recent_customers',
                    'title' => 'Recent Customers',
                    'date_label' => $dateLabel,
                    'data' => $recentCustomers
                ];

            // ============ CUSTOMER SUMMARY ============
            case 'customer_summary':
                $dateLabel = $this->getDateRangeLabel();
                
                $totalCustomers = Customer::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })->count();
                
                $newCustomers = Customer::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->when($this->startDate && $this->endDate, function($q) {
                    $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                })
                ->count();
                
                $customersWithBookings = Customer::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->whereHas('bookings', function($q) {
                    if ($this->startDate && $this->endDate) {
                        $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                    }
                })
                ->count();
                
                $totalSpent = Booking::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->when($this->startDate && $this->endDate, function($q) {
                    $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                })
                ->sum('total_due');
                
                return [
                    'type' => 'customer_summary',
                    'title' => 'Customer Summary',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Total Customers' => $totalCustomers,
                        'New Customers' => $newCustomers,
                        'Active Customers' => $customersWithBookings,
                        'Total Revenue from Customers' => '₱' . number_format($totalSpent, 2),
                        'Period' => ucfirst($dateLabel)
                    ]
                ];

            // ============ VIP CUSTOMERS ============
            case 'vip_customers':
                $dateLabel = $this->getDateRangeLabel();
                
                $vipCustomers = Customer::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->with(['bookings' => function($q) {
                    if ($this->startDate && $this->endDate) {
                        $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                    }
                    $q->where('status', 'approved');
                }])
                ->get()
                ->map(function($customer) {
                    $totalSpent = $customer->bookings->sum('total_due');
                    $bookingCount = $customer->bookings->count();
                    return [
                        'Customer Name' => $customer->customer_name,
                        'Total Spent' => '₱' . number_format($totalSpent, 2),
                        'Bookings' => $bookingCount,
                        'Contact' => $customer->contact_number ?? 'N/A'
                    ];
                })
                ->filter(function($customer) {
                    return $customer['Total Spent'] > 0;
                })
                ->sortByDesc('Total Spent')
                ->take(5)
                ->values()
                ->toArray();
                
                return [
                    'type' => 'vip_customers',
                    'title' => 'VIP Customers',
                    'date_label' => $dateLabel,
                    'data' => $vipCustomers
                ];

            // ============ CUSTOMER RETENTION ============
            case 'customer_retention':
                $dateLabel = $this->getDateRangeLabel();
                
                $allCustomers = Customer::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })->get();
                
                $totalCustomers = $allCustomers->count();
                
                $repeatCustomers = $allCustomers->filter(function($customer) {
                    return $customer->bookings()->where('status', 'approved')->count() > 1;
                })->count();
                
                $newCustomers = Customer::when($this->companyId, function($q) {
                    $q->where('company_id', $this->companyId);
                })
                ->when($this->startDate && $this->endDate, function($q) {
                    $q->whereBetween('created_at', [$this->startDate, $this->endDate]);
                })
                ->count();
                
                $retentionRate = $totalCustomers > 0 ? round(($repeatCustomers / $totalCustomers) * 100, 1) : 0;
                
                return [
                    'type' => 'customer_retention',
                    'title' => 'Customer Retention',
                    'date_label' => $dateLabel,
                    'data' => [
                        'Total Customers' => $totalCustomers,
                        'Repeat Customers' => $repeatCustomers,
                        'New Customers' => $newCustomers,
                        'Retention Rate' => $retentionRate . '%',
                        'Period' => ucfirst($dateLabel)
                    ]
                ];

            // ============ BOOKING SOURCE ============
            case 'booking_source':
                $dateLabel = $this->getDateRangeLabel();
                
                $bookingSources = (clone $query)
                    ->select('source_id', \DB::raw('count(*) as total'))
                    ->groupBy('source_id')
                    ->with('source')
                    ->get()
                    ->map(function($item) {
                        return [
                            'Source' => $item->source->source ?? 'Unknown',
                            'Total Bookings' => $item->total
                        ];
                    })
                    ->sortByDesc('Total Bookings')
                    ->values()
                    ->toArray();
                
                return [
                    'type' => 'booking_source',
                    'title' => 'Booking Sources',
                    'date_label' => $dateLabel,
                    'data' => $bookingSources
                ];

            // ============ PAYMENT SUMMARY ============
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

            // ============ RECENT PAYMENTS ============
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

            // ============ PAYMENT BREAKDOWN ============
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
                
                $fundBreakdown = $payments->groupBy('fund_type_id')->map(function($group) {
                    $fund = $group->first()->fundType;
                    return [
                        'Fund Name' => $fund->name ?? 'Uncategorized',
                        'Total Amount' => '₱' . number_format($group->sum('amount'), 2),
                        'Count' => $group->count()
                    ];
                })->values()->toArray();
                
                $resultData = [
                    'Total Payments' => '₱' . number_format($totalAmount, 2),
                    'Period' => ucfirst($dateLabel)
                ];
                
                foreach ($fundBreakdown as $fund) {
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

    protected function generateDynamicResponse($userMessage, $data, $intent)
    {
        // ... existing method
        $dataArray = $data['data'] ?? [];
        $title = $data['title'] ?? 'Information';
        $type = $data['type'] ?? 'general';
        $dateLabel = $data['date_label'] ?? '';

        if (empty($dataArray)) {
            if ($this->language === 'tl') {
                return '<p>Walang nakitang datos.</p>';
            }
            return '<p>No data found.</p>';
        }

        $context = json_encode($dataArray, JSON_PRETTY_PRINT);
        $isList = isset($dataArray[0]) && is_array($dataArray[0]);
        $dataType = $isList ? 'list of items' : 'key-value pairs';
        $languageInstruction = $this->getLanguagePrompt();

        $prompt = "
        You are a helpful assistant for KeyFleet, a car rental management platform.
        
        User question: '{$userMessage}'
        
        Data retrieved from the system:
        {$context}
        
        Data type: {$dataType}
        Title: {$title}
        Period: {$dateLabel}
        
        {$languageInstruction}
        
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
                    ['role' => 'system', 'content' => 'You are a helpful assistant for KeyFleet. Generate clean, well-formatted HTML responses. Do not use emojis. ' . $languageInstruction],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 800,
            ]);

            $htmlResponse = $response->choices[0]->message->content;
            
            if (!str_contains($htmlResponse, '<div')) {
                $htmlResponse = '<div class="space-y-2">' . $htmlResponse . '</div>';
            }
            
            return $htmlResponse;
        } catch (\Exception $e) {
            Log::error('AI dynamic response generation failed: ' . $e->getMessage());
            return $this->formatFallbackHTML($dataArray, $title, $isList);
        }
    }

    protected function generateCarAvailabilityResponse($userMessage, $data, $intent)
    {
        $dataArray = $data['data'] ?? [];
        $title = $data['title'] ?? 'Car Availability';
        $dateLabel = $data['date_label'] ?? '';
        $languageInstruction = $this->getLanguagePrompt();
        
        if (empty($dataArray) || empty($dataArray['cars'])) {
            if ($this->language === 'tl') {
                return '<p>Walang nakitang sasakyan.</p>';
            }
            return '<p>No cars found.</p>';
        }
        
        // Extract summary data with proper null checks
        $summary = $dataArray['summary'] ?? [];
        $totalCars = isset($summary['Total Cars']) ? $summary['Total Cars'] : 0;
        $availableCars = isset($summary['Available Cars']) ? $summary['Available Cars'] : 0;
        $bookedCars = isset($summary['Booked Cars']) ? $summary['Booked Cars'] : 0;
        $dateRange = isset($summary['Date Range']) ? $summary['Date Range'] : $dateLabel;
        
        $cars = $dataArray['cars'] ?? [];
        
        $prompt = "
        You are OTTO, a helpful assistant for KeyFleet.
        
        User question: '{$userMessage}'
        
        Here is the car availability data:
        
        Summary:
        - Total Cars: {$totalCars}
        - Available Cars: {$availableCars}
        - Booked Cars: {$bookedCars}
        - Date Range: {$dateRange}
        
        Car List:
        " . json_encode($cars, JSON_PRETTY_PRINT) . "
        
        {$languageInstruction}
        
        Your task:
        1. Generate a friendly, professional response about car availability
        2. Include the summary information prominently
        3. List the available cars first, then the booked ones
        4. If no cars are available, suggest alternative dates or times
        5. Use HTML formatting for better presentation
        6. Do not use emojis
        
        Format the response with:
        - A header with the title and date range
        - A summary card with key metrics with light and darkmode tailwind class
        - A table or list of cars with their availability status
        ";

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are OTTO, a helpful assistant for KeyFleet. Be professional and helpful. Do not use emojis. ' . $languageInstruction],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 800,
            ]);

            $htmlResponse = $response->choices[0]->message->content;
            
            if (!str_contains($htmlResponse, '<div')) {
                $htmlResponse = '<div class="space-y-2">' . $htmlResponse . '</div>';
            }
            
            return $htmlResponse;
            
        } catch (\Exception $e) {
            Log::error('Car availability response failed: ' . $e->getMessage());
            return $this->formatCarAvailabilityFallback($dataArray, $title);
        }
    }

    protected function formatCarAvailabilityFallback($dataArray, $title)
    {
        $summary = $dataArray['summary'] ?? [];
        $cars = $dataArray['cars'] ?? [];
        
        $html = [];
        $html[] = '<div class="space-y-3">';
        $html[] = '<h4 class="text-lg font-semibold text-gray-900 dark:text-white">' . $title . '</h4>';
        
        // Summary
        if (!empty($summary)) {
            $html[] = '<div class="grid grid-cols-2 gap-2 my-2">';
            foreach ($summary as $key => $value) {
                $html[] = '<div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">';
                $html[] = '<div class="text-xs text-gray-500 dark:text-gray-400">' . $key . '</div>';
                $html[] = '<div class="text-sm font-semibold text-gray-900 dark:text-white">' . $value . '</div>';
                $html[] = '</div>';
            }
            $html[] = '</div>';
        }
        
        // Car list
        if (!empty($cars)) {
            $html[] = '<div class="overflow-x-auto my-2">';
            $html[] = '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">';
            $html[] = '<thead class="bg-gray-50 dark:bg-gray-800">';
            $html[] = '<tr>';
            $html[] = '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Car Name</th>';
            $html[] = '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Model</th>';
            $html[] = '<th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>';
            $html[] = '</tr>';
            $html[] = '</thead>';
            $html[] = '<tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">';
            
            foreach ($cars as $car) {
                $statusClass = str_contains($car['Availability'], 'Available') ? 'text-green-600' : 'text-red-600';
                $html[] = '<tr class="hover:bg-gray-50 dark:hover:bg-gray-800">';
                $html[] = '<td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">' . ($car['Car Name'] ?? 'N/A') . '</td>';
                $html[] = '<td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">' . ($car['Model'] ?? 'N/A') . '</td>';
                $html[] = '<td class="px-3 py-2 whitespace-nowrap text-sm font-medium ' . $statusClass . '">' . ($car['Availability'] ?? 'N/A') . '</td>';
                $html[] = '</tr>';
            }
            
            $html[] = '</tbody></table></div>';
        }
        
        $html[] = '</div>';
        return implode("\n", $html);
    }

    protected function formatFallbackHTML($dataArray, $title, $isList)
    {
        // ... existing method
        $html = [];
        $html[] = '<div class="space-y-2">';
        $html[] = '<h4 class="text-lg font-semibold text-gray-900 dark:text-white">' . $title . '</h4>';
        
        if ($isList) {
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
        // ... existing method
        $languageInstruction = $this->getLanguagePrompt();
        
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
        - 'Who are my top customers?'
        - 'What is my net revenue?'
        
        Format the response with HTML.
        Keep the response professional. Do not use emojis.
        ";

        try {
            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant for KeyFleet. Be professional. Do not use emojis. Use HTML formatting. ' . $languageInstruction],
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

    /**
     * Extract specific date range from message (for availability queries)
     * Returns array with start_date and end_date
     */
    protected function extractSpecificDates($message)
    {
        $message = strtolower($message);
        $dates = ['start_date' => null, 'end_date' => null];
        
        // Pattern: "from YYYY-MM-DD to YYYY-MM-DD"
        if (preg_match('/from\s+(\d{4}-\d{2}-\d{2})\s+to\s+(\d{4}-\d{2}-\d{2})/', $message, $matches)) {
            $dates['start_date'] = Carbon::parse($matches[1]);
            $dates['end_date'] = Carbon::parse($matches[2]);
            return $dates;
        }
        
        // Pattern: "from YYYY-MM-DD to YYYY-MM-DD" (alternative)
        if (preg_match('/from\s+(\d{4}-\d{2}-\d{2})\s*(?:to|until|-)\s*(\d{4}-\d{2}-\d{2})/', $message, $matches)) {
            $dates['start_date'] = Carbon::parse($matches[1]);
            $dates['end_date'] = Carbon::parse($matches[2]);
            return $dates;
        }
        
        // Pattern: "YYYY-MM-DD to YYYY-MM-DD"
        if (preg_match('/(\d{4}-\d{2}-\d{2})\s+to\s+(\d{4}-\d{2}-\d{2})/', $message, $matches)) {
            $dates['start_date'] = Carbon::parse($matches[1]);
            $dates['end_date'] = Carbon::parse($matches[2]);
            return $dates;
        }
        
        // Pattern: "MM/DD/YYYY to MM/DD/YYYY"
        if (preg_match('/(\d{1,2}\/\d{1,2}\/\d{4})\s+to\s+(\d{1,2}\/\d{1,2}\/\d{4})/', $message, $matches)) {
            $dates['start_date'] = Carbon::parse($matches[1]);
            $dates['end_date'] = Carbon::parse($matches[2]);
            return $dates;
        }
        
        // Pattern: "MM-DD-YYYY to MM-DD-YYYY"
        if (preg_match('/(\d{1,2}-\d{1,2}-\d{4})\s+to\s+(\d{1,2}-\d{1,2}-\d{4})/', $message, $matches)) {
            $dates['start_date'] = Carbon::parse($matches[1]);
            $dates['end_date'] = Carbon::parse($matches[2]);
            return $dates;
        }
        
        // Pattern: "from tomorrow to next week" - handle relative dates
        if (preg_match('/from\s+(today|tomorrow|next\s+week)\s+to\s+(today|tomorrow|next\s+week|next\s+month)/', $message, $matches)) {
            $dates['start_date'] = $this->parseRelativeDate($matches[1]);
            $dates['end_date'] = $this->parseRelativeDate($matches[2]);
            return $dates;
        }
        
        // Pattern: "on YYYY-MM-DD" (single date)
        if (preg_match('/(?:on|for)\s+(\d{4}-\d{2}-\d{2})/', $message, $matches)) {
            $dates['start_date'] = Carbon::parse($matches[1]);
            $dates['end_date'] = Carbon::parse($matches[1])->copy()->endOfDay();
            return $dates;
        }
        
        // Pattern: "today" or "tomorrow" (single date)
        if (str_contains($message, 'today')) {
            $dates['start_date'] = Carbon::now()->startOfDay();
            $dates['end_date'] = Carbon::now()->endOfDay();
            return $dates;
        }
        
        if (str_contains($message, 'tomorrow')) {
            $dates['start_date'] = Carbon::tomorrow()->startOfDay();
            $dates['end_date'] = Carbon::tomorrow()->endOfDay();
            return $dates;
        }
        
        // Pattern: "this weekend"
        if (str_contains($message, 'this weekend')) {
            $dates['start_date'] = Carbon::now()->endOfWeek()->subDays(1)->startOfDay();
            $dates['end_date'] = Carbon::now()->endOfWeek()->endOfDay();
            return $dates;
        }
        
        // Pattern: "next weekend"
        if (str_contains($message, 'next weekend')) {
            $dates['start_date'] = Carbon::now()->addWeek()->endOfWeek()->subDays(1)->startOfDay();
            $dates['end_date'] = Carbon::now()->addWeek()->endOfWeek()->endOfDay();
            return $dates;
        }
        
        return $dates;
    }

    /**
     * Parse relative date strings
     */
    protected function parseRelativeDate($dateString)
    {
        $dateString = strtolower(trim($dateString));
        
        switch ($dateString) {
            case 'today':
                return Carbon::now()->startOfDay();
            case 'tomorrow':
                return Carbon::tomorrow()->startOfDay();
            case 'next week':
                return Carbon::now()->addWeek()->startOfDay();
            case 'next month':
                return Carbon::now()->addMonth()->startOfDay();
            default:
                return Carbon::now()->startOfDay();
        }
    }

    protected function getFallbackResponse($userMessage)
    {
        // ... existing method
        $intent = $this->simpleIntentDetection($userMessage);
        
        if ($intent && $intent['action'] !== 'general') {
            $data = $this->fetchData($intent);
            $dataArray = $data['data'] ?? [];
            $title = $data['title'] ?? 'Information';
            $isList = isset($dataArray[0]) && is_array($dataArray[0]);
            return $this->formatFallbackHTML($dataArray, $title, $isList);
        }
        
        if ($this->language === 'tl') {
            return '<div class="space-y-2">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">KeyFleet Assistant</h4>
                <p class="text-sm text-gray-600 dark:text-gray-300">Makakatulong ako sa iyo sa:</p>
                <ul class="list-disc pl-4 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                    <li><strong>Mga Nangungunang Customer</strong> - "Sino ang aking mga nangungunang customer?"</li>
                    <li><strong>Balans ng Customer</strong> - "Alin sa mga customer ang may outstanding balance?"</li>
                    <li><strong>Mga Bagong Customer</strong> - "Ipakita ang mga bagong customer"</li>
                    <li><strong>Buod ng Customer</strong> - "Ipakita ang buod ng customer"</li>
                    <li><strong>Net Revenue</strong> - "Magkano ang aking net revenue ngayong buwan?"</li>
                    <li><strong>Kita</strong> - "Magkano ang aking kinita ngayong buwan?"</li>
                    <li><strong>Mga Booking</strong> - "Ipakita ang aking mga booking ngayong araw"</li>
                    <li><strong>Mga Sasakyan</strong> - "Ilang sasakyan ang available?"</li>
                    <li><strong>Mga Bayad</strong> - "Ipakita ang aking summary ng mga bayad"</li>
                    <li><strong>Performance</strong> - "Aling sasakyan ang may pinakamagandang performance ngayong buwan?"</li>
                    <li><strong>Mga Gastos</strong> - "Ipakita ang aking mga gastos ngayong taon"</li>
                    <li><strong>Impormasyon ng Partner</strong> - "Ipakita ang aking impormasyon bilang partner"</li>
                    <li><strong>Mga Pondo</strong> - "Ipakita ang aking mga balans ng pondo"</li>
                </ul>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Tanungin mo ako ng kahit ano tungkol sa iyong fleet!</p>
            </div>';
        }
        
        return '<div class="space-y-2">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">KeyFleet Assistant</h4>
            <p class="text-sm text-gray-600 dark:text-gray-300">I can help you with:</p>
            <ul class="list-disc pl-4 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                <li><strong>Top Customers</strong> - "Who are my top customers?"</li>
                <li><strong>Customer Balances</strong> - "Which customers have outstanding balances?"</li>
                <li><strong>Recent Customers</strong> - "Show me recent customers"</li>
                <li><strong>Customer Summary</strong> - "Show me customer summary"</li>
                <li><strong>Net Revenue</strong> - "What is my net revenue this month?"</li>
                <li><strong>Earnings</strong> - "How much did I earn this month?"</li>
                <li><strong>Bookings</strong> - "Show me my bookings for today"</li>
                <li><strong>Cars</strong> - "How many cars are available?"</li>
                <li><strong>Payments</strong> - "Show me my payment summary"</li>
                <li><strong>Performance</strong> - "Which car is performing best this month?"</li>
                <li><strong>Expenses</strong> - "Show me my expenses this year"</li>
                <li><strong>Partner Info</strong> - "Show me my partner information"</li>
                <li><strong>Funds</strong> - "Show me my fund balances"</li>
            </ul>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Ask me anything about your fleet!</p>
        </div>';
    }
}