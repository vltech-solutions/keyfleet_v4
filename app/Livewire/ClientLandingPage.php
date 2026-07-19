<?php

namespace App\Livewire;

use App\Models\Car;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Carbon\Carbon;

class ClientLandingPage extends Component
{
    public Company $company;
    public $selectedBrand = 'All';
    public $selectedType = 'All';
    public $selectedTransmission = 'All';
    public $primaryColor;
    
    // Filter properties
    public $startDate;
    public $endDate;
    public $searchTerm = '';
    public $selectedVehicleType = 'All';
    public $sortBy = 'popular';

    // Website settings
    public $websiteSettings;
    public $bannerUrl;

    public function mount($tenant)
    {
        $companyInfo = Company::where('slug', $tenant)->first();

        if (!$companyInfo) {
            abort(404);
        }
        
        if(!$companyInfo->hasProOrMasterSubscription() && !$companyInfo->hasActiveFreeSubscription())
        {
            if (!$companyInfo->hasNonBasicPaidSubscription()) {
                abort(403, 'Service Not Available');
            }

            return redirect()->route('booking.wizard',['tenant' => $tenant]);
        }

        $this->company = $companyInfo;
        $this->primaryColor = $this->company->primary_color;
        
        // Load website settings
        $this->websiteSettings = $this->company->website;
        
        // Set banner URL
        if ($this->websiteSettings && $this->websiteSettings->banner) {
            $this->bannerUrl = Storage::disk('s3')->temporaryUrl(
                $this->websiteSettings->banner, 
                now()->addMinutes(5)
            );
        }

        // Set tenant in session for use in other controllers
        session(['tenant_id' => $companyInfo->id]);
        session(['tenant_slug' => $companyInfo->slug]);
        
        // Set default dates
        // $this->startDate = Carbon::now()->format('Y-m-d');
        // $this->endDate = Carbon::now()->addDays(3)->format('Y-m-d');
    }

    public function selectBrand($brand)
    {
        $this->selectedBrand = $brand;
    }

    public function selectType($type)
    {
        $this->selectedType = $type;
    }

    public function selectTransmission($transmission)
    {
        $this->selectedTransmission = $transmission;
    }

    public function applyFilter()
    {
        $this->dispatch('filter-applied');
    }

    public function resetFilters()
    {
        $this->selectedBrand = 'All';
        $this->selectedType = 'All';
        $this->selectedTransmission = 'All';
        $this->searchTerm = '';
        $this->sortBy = 'popular';
        $this->selectedVehicleType = 'All';
        
        // Reset dates to default
        $this->startDate = Carbon::now()->format('Y-m-d');
        $this->endDate = Carbon::now()->addDays(3)->format('Y-m-d');
        
        $this->dispatch('filter-applied');
    }

    public function searchCars()
    {
        $this->dispatch('filter-applied');
        $this->dispatch('scroll-to-cars');
    }

    // Helper method to check car availability
    protected function isCarAvailable($carId, $startDate, $endDate)
    {
        return Car::isAvailableAt($carId, $startDate, $endDate);
    }

    // Helper method to check if date filter is active
    protected function hasDateFilter()
    {
        $defaultStart = Carbon::now()->format('Y-m-d');
        $defaultEnd = Carbon::now()->addDays(3)->format('Y-m-d');
        
        return $this->startDate !== $defaultStart || $this->endDate !== $defaultEnd;
    }

    // Computed Property - Loads ALL available cars
    public function getCarsProperty()
    {
        $query = Car::with(['carType', 'images'])
            ->where('is_available', true)
            ->where('company_id', $this->company->id)
            ->whereNull('deleted_at'); // Exclude soft-deleted cars

        // Brand filter
        if ($this->selectedBrand !== 'All') {
            $query->whereRaw('LOWER(brand) = ?', [strtolower($this->selectedBrand)]);
        }

        // Type filter
        if ($this->selectedType !== 'All') {
            $query->whereHas('carType', function ($q) {
                $q->where('car_type', $this->selectedType);
            });
        }

        // Transmission filter
        if ($this->selectedTransmission !== 'All') {
            $query->where('transmission', $this->selectedTransmission);
        }

        // Vehicle type filter (from banner)
        if ($this->selectedVehicleType !== 'All') {
            $query->whereHas('carType', function ($q) {
                $q->where('car_type', $this->selectedVehicleType);
            });
        }

        // Search term
        if (!empty($this->searchTerm)) {
            $query->where(function($q) {
                $q->where('brand', 'LIKE', '%' . $this->searchTerm . '%')
                  ->orWhere('model', 'LIKE', '%' . $this->searchTerm . '%')
                  ->orWhere('name', 'LIKE', '%' . $this->searchTerm . '%')
                  ->orWhereHas('carType', function($subQ) {
                      $subQ->where('car_type', 'LIKE', '%' . $this->searchTerm . '%');
                  });
            });
        }

        // Get all cars first (without limit) to check availability
        $allCars = $query->get();
        
        // Only filter by availability if user has explicitly set dates
        $availableCars = $allCars;
        if ($this->hasDateFilter()) {
            $availableCars = $allCars->filter(function ($car) {
                if ($this->startDate && $this->endDate) {
                    return $this->isCarAvailable($car->id, $this->startDate, $this->endDate);
                }
                return true;
            });
        }

        // Apply sorting
        $sortedCars = $availableCars;
        
        switch ($this->sortBy) {
            case 'price_low':
                $sortedCars = $sortedCars->sortBy('price_starts_at');
                break;
            case 'price_high':
                $sortedCars = $sortedCars->sortByDesc('price_starts_at');
                break;
            case 'newest':
                $sortedCars = $sortedCars->sortByDesc('created_at');
                break;
            default:
                $sortedCars = $sortedCars->sortByDesc('id');
                break;
        }

        // Return ALL cars (no limit)
        return $sortedCars->values();
    }

    // Helper to convert hex to RGB
    protected function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        
        return "$r, $g, $b";
    }

    // Get total available cars count
    public function getTotalAvailableCarsCount()
    {
        return $this->cars->count();
    }

    public function render()
    {
        // Fetch fresh filter options - Exclude soft-deleted cars
        $allCars = Car::where('company_id', $this->company->id)
            ->with('carType')
            ->where('is_available', true)
            ->whereNull('deleted_at') // Exclude soft-deleted cars
            ->get();

        $brands = $allCars->pluck('brand')
            ->map(fn($b) => ucwords(strtolower(trim($b))))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $types = $allCars->pluck('carType.car_type')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $transmissions = $allCars->pluck('transmission')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('livewire.client-landing-page', [
            'company' => $this->company,
            'companyLogo' => $this->company->avatar_url ? Storage::url($this->company->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($this->company->name),
            'cars' => $this->cars, 
            'brands' => $brands,
            'types' => $types,
            'transmissions' => $transmissions,
            'selectedTransmission' => $this->selectedTransmission,
            'totalCars' => $allCars->count(),
            'totalAvailable' => $this->getTotalAvailableCarsCount(),
            'headerText' => $this->websiteSettings->header_text ?? 'Find Your Perfect Ride',
            'subheader' => $this->websiteSettings->subheader ?? 'Explore our premium fleet of vehicles. Book the car that matches your style and needs.',
            'bannerUrl' => $this->bannerUrl,
        ])
        ->layout('components.layouts.client-website', ['company' => $this->company]);
    }
}