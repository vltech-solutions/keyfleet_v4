<?php

namespace App\Livewire;

use App\Models\Partners;
use App\Models\Booking;
use App\Models\Car;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\WithPagination;

class PartnerReport extends Component
{
    use WithPagination;
    public $token;
    public $partner;
    public $dateRange = 'this_year';
    public $startDate;
    public $endDate;
    public $selectedCar = 'all';
    public $activeTab = 'earnings';
    public $loading = true;

    // Custom date range
    public $customStartDate;
    public $customEndDate;

    // Filters for bookings
    public $bookingStatus = 'all';
    public $searchTerm = '';

    protected $queryString = [
        'dateRange' => ['except' => 'this_month'],
        'selectedCar' => ['except' => 'all'],
        'activeTab' => ['except' => 'earnings'],
        'bookingStatus' => ['except' => 'all'],
        'searchTerm' => ['except' => ''],
    ];

    public function mount($token)
    {
        $this->token = $token;
        $this->partner = Partners::where('access_token', $token)->firstOrFail();
        $this->setDateRange($this->dateRange);
        $this->loading = false;

        // Set default tab to earnings
        if (!isset($this->activeTab) || $this->activeTab === 'summary') {
            $this->activeTab = 'earnings';
        }

        // Set custom dates to current month if not set
        if (!$this->customStartDate) {
            $this->customStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        }
        if (!$this->customEndDate) {
            $this->customEndDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        }
        
    }

    public function setDateRange($range)
    {
        $this->dateRange = $range;
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                $this->startDate = $now->copy()->startOfDay();
                $this->endDate = $now->copy()->endOfDay();
                break;
            case 'this_week':
                $this->startDate = $now->copy()->startOfWeek();
                $this->endDate = $now->copy()->endOfWeek();
                break;
            case 'this_month':
                $this->startDate = $now->copy()->startOfMonth();
                $this->endDate = $now->copy()->endOfMonth();
                break;
            case 'last_month':
                $this->startDate = $now->copy()->subMonth()->startOfMonth();
                $this->endDate = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'this_quarter':
                $this->startDate = $now->copy()->startOfQuarter();
                $this->endDate = $now->copy()->endOfQuarter();
                break;
            case 'this_year':
                $this->startDate = $now->copy()->startOfYear();
                $this->endDate = $now->copy()->endOfYear();
                break;
            case 'custom':
                // Use custom dates if set, otherwise default to this month
                if ($this->customStartDate && $this->customEndDate) {
                    $this->startDate = Carbon::parse($this->customStartDate)->startOfDay();
                    $this->endDate = Carbon::parse($this->customEndDate)->endOfDay();
                } else {
                    $this->startDate = $now->copy()->startOfMonth();
                    $this->endDate = $now->copy()->endOfMonth();
                }
                break;
            default:
                $this->startDate = $now->copy()->startOfMonth();
                $this->endDate = $now->copy()->endOfMonth();
        }
    }

    public function updatedDateRange($value)
    {
        if ($value !== 'custom') {
            $this->setDateRange($value);
        }
    }

    public function applyCustomDateRange()
    {
        if ($this->customStartDate && $this->customEndDate) {
            $this->dateRange = 'custom';
            $this->setDateRange('custom');
        }
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function resetFilters()
    {
        $this->selectedCar = 'all';
        $this->bookingStatus = 'all';
        $this->searchTerm = '';
        $this->dateRange = 'this_month';
        $this->setDateRange('this_month');
    }

    // ============ COMPUTED PROPERTIES ============

    public function getCarsProperty()
    {
        return $this->partner->cars()
            ->withCount(['bookings as total_bookings' => function($query) {
                $query->where('status', 'approved');
            }])
            ->get();
    }

    public function getCarOptionsProperty()
    {
        return $this->partner->cars->pluck('name', 'id')->toArray();
    }

    public function getBookingsQueryProperty()
    {
        $query = Booking::whereHas('car', function($q) {
            $q->where('partner_id', $this->partner->id);
        })
        ->with(['car', 'customer'])
        ->when($this->selectedCar !== 'all', function($q) {
            $q->where('car_id', $this->selectedCar);
        })
        ->when($this->bookingStatus !== 'all', function($q) {
            $q->where('status', $this->bookingStatus);
        })
        ->whereBetween('created_at', [$this->startDate, $this->endDate])
        ->when($this->searchTerm, function($q) {
            $q->where(function($query) {
                $query->where('renter_name', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('booking_id', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('contact_number', 'like', '%' . $this->searchTerm . '%');
            });
        })
        ->orderBy('created_at', 'desc');

        return $query;
    }

    public function getBookingsProperty()
    {
        return $this->bookingsQuery->paginate(10);
    }

    public function getSummaryStatsProperty()
    {
        $baseQuery = Booking::whereHas('car', function($q) {
            $q->where('partner_id', $this->partner->id);
        });


        // Total Bookings (all time)
        $totalBookings = (clone $baseQuery)->count();

        // Bookings in date range
        $rangeQuery = (clone $baseQuery)
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);


        $bookingsInRange = (clone $rangeQuery)->count();
        $revenueInRange = (clone $rangeQuery)->sum('total_due');
        $paidInRange = (clone $rangeQuery)->sum('paid_amount');
        $balanceInRange = (clone $rangeQuery)->sum('balance');
        
        // Partner earnings (after commission)
        $partnerEarnings = (clone $rangeQuery)
            ->whereNotNull('partner_commission')
            ->sum('partner_commission');

        $companyEarnings = (clone $rangeQuery)
            ->whereNotNull('company_earnings')
            ->sum('company_earnings');

        // Upcoming bookings
        $upcomingBookings = (clone $baseQuery)
            ->where('status', 'approved')
            ->where('start_datetime', '>', now())
            ->count();

        // Ongoing bookings
        $ongoingBookings = (clone $baseQuery)
            ->where('status', 'approved')
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now())
            ->count();

        // Completed bookings in range
        $completedInRange = (clone $rangeQuery)
            ->where('status', 'approved')
            ->where('end_datetime', '<', now())
            ->count();

        return [
            'total_bookings' => $totalBookings,
            'bookings_in_range' => $bookingsInRange,
            'revenue_in_range' => $revenueInRange,
            'paid_in_range' => $paidInRange,
            'balance_in_range' => $balanceInRange,
            'partner_earnings' => $partnerEarnings,
            'upcoming_bookings' => $upcomingBookings,
            'ongoing_bookings' => $ongoingBookings,
            'completed_in_range' => $completedInRange,
            'company_earnings' => $companyEarnings,
        ];
    }

    public function getCarPerformanceProperty()
    {
        $cars = $this->partner->cars()->with(['bookings' => function($query) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('status', 'approved');
        }])->get();

        return $cars->map(function($car) {
            $bookings = $car->bookings;
            $totalRevenue = $bookings->sum('total_due');
            $totalPaid = $bookings->sum('paid_amount');
            $totalBalance = $bookings->sum('balance');
            $partnerEarnings = $bookings->sum('partner_commission');
            $bookingCount = $bookings->count();

            // Calculate utilization
            $daysInRange = $this->startDate->diffInDays($this->endDate) + 1;
            $rentedDays = $bookings->sum(function($booking) {
                return $booking->start_datetime->diffInDays($booking->end_datetime) + 1;
            });
            $utilizationRate = $daysInRange > 0 ? ($rentedDays / ($daysInRange * max($bookingCount, 1))) * 100 : 0;

            // Get car status
            $status = 'Available';
            $statusColor = 'green';
            
            $currentBookings = $bookings->where('start_datetime', '<=', now())
                                       ->where('end_datetime', '>=', now());
            
            if ($currentBookings->count() > 0) {
                $status = 'Currently Rented';
                $statusColor = 'yellow';
            } elseif (!$car->is_available) {
                $status = 'Not Available';
                $statusColor = 'red';
            }

            // Get car image URL
            $imageUrl = $car->getThumbnailOrDefaultUrlAttribute();

            return [
                'id' => $car->id,
                'name' => $car->name,
                'brand' => $car->brand,
                'model' => $car->model,
                'plate_number' => $car->plate_number,
                'image' => $imageUrl,
                'status' => $status,
                'status_color' => $statusColor,
                'booking_count' => $bookingCount,
                'total_revenue' => $totalRevenue,
                'total_paid' => $totalPaid,
                'total_balance' => $totalBalance,
                'partner_earnings' => $partnerEarnings,
                'utilization_rate' => round($utilizationRate, 2),
            ];
        })->sortByDesc('total_revenue')->values();
    }

    public function getMonthlyRevenueProperty()
    {
        $months = collect();
        $start = $this->startDate->copy()->startOfMonth();
        $end = $this->endDate->copy()->endOfMonth();

        for ($date = $start; $date <= $end; $date->addMonth()) {
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $revenue = Booking::whereHas('car', function($q) {
                $q->where('partner_id', $this->partner->id);
            })
            ->where('status', 'approved')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('total_due');

            $earnings = Booking::whereHas('car', function($q) {
                $q->where('partner_id', $this->partner->id);
            })
            ->where('status', 'approved')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('partner_commission');

            $months->push([
                'month' => $date->format('M Y'),
                'revenue' => $revenue,
                'earnings' => $earnings,
            ]);
        }

        return $months;
    }

    public function render()
    {
        return view('livewire.partner-report', [
            'cars' => $this->cars,
            'carOptions' => $this->carOptions,
            'bookings' => $this->bookings,
            'summaryStats' => $this->summaryStats,
            'carPerformance' => $this->carPerformance,
            'monthlyRevenue' => $this->monthlyRevenue,
        ])->layout('layouts.guest');
    }
}