<?php

namespace App\Livewire;

use App\Models\Car;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ClientLandingPage extends Component
{
    public Company $company;
    public $selectedBrand = 'All';
    public $selectedType = 'All';
    public $selectedTransmission = 'All';
    public $perPage = 8;
    public $primaryColor;

    public function mount($tenant)
    {
        $companyInfo = Company::where('slug', $tenant)->first();

        if (!$companyInfo) {
            abort(404);
        }

        // Subscription checks
        if(!$companyInfo->hasAddon('booking-pro')){
            if (!$companyInfo->hasNonBasicPaidSubscription()) {
                if (!$companyInfo->hasActiveFreeSubscription()) {
                    // abort(403, 'The booking service is not available at the moment.');
                    return redirect()->route('booking.wizard.v2',['tenant' => $tenant]);
                }else{
                    return redirect()->route('booking.wizard.v2',['tenant' => $tenant]);
                }
            }else{
                if(!$companyInfo->hasAddon('booking-pro')){
                    return redirect()->route('booking.wizard.v2',['tenant' => $tenant]);
                }
            }
        }

        $this->company = $companyInfo;
        $this->primaryColor = $this->company->primary_color;
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
        // Close drawer via Alpine
        $this->dispatch('filter-applied');
    }

    public function resetFilters()
    {
        $this->selectedBrand = 'All';
        $this->selectedType = 'All';
        $this->selectedTransmission = 'All'; 
        $this->dispatch('filter-applied');
    }

    // Computed Property: Access in view via $this->cars
    public function getCarsProperty()
    {
        $query = Car::with('carType')
            ->where('is_available',true)
            ->where('company_id', $this->company->id);

        if ($this->selectedBrand !== 'All') {
            // Case-insensitive database match
            $query->whereRaw('LOWER(brand) = ?', [strtolower($this->selectedBrand)]);
        }

        if ($this->selectedType !== 'All') {
            $query->whereHas('carType', function ($q) {
                $q->where('car_type', $this->selectedType);
            });
        }

        if ($this->selectedTransmission !== 'All') {
            $query->where('transmission', $this->selectedTransmission);
        }

        return $query->get();
    }

    public function loadMore()
    {
        $this->perPage = 999; 
    }

    public function render()
    {
        // Fetch fresh filter options based on the company's total fleet
        $allCars = Car::where('company_id', $this->company->id)->with('carType')->get();

        $brands = $allCars->pluck('brand')
            ->map(fn($b) => ucwords(strtolower(trim($b))))
            ->unique()->sort()->values();

        $types = $allCars->pluck('carType.car_type')
            ->filter()->unique()->sort()->values();

        $transmissions = $allCars->pluck('transmission')
            ->filter()->unique()->sort()->values();

            // dd($transmissions);

        return view('livewire.client-landing-page', [
            'company' => $this->company,
            'companyLogo' => $this->company->avatar_url ? Storage::url($this->company->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($this->company->name),
            'cars' => $this->cars, 
            'brands' => $brands,
            'types' => $types,
            'transmissions' => $transmissions,
            'selectedTransmission' => $this->selectedTransmission,
        ])
        ->layout('components.layouts.client-website', ['company' => $this->company]);
    }
}