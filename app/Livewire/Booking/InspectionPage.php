<?php

namespace App\Livewire\Booking;

use Livewire\Component;
use Livewire\WithFileUploads;

class InspectionPage extends Component
{
    use WithFileUploads;

    public $currentStep = 1; // Defaulting to Exterior for this demo
    public $activeZone = null;
    
    // Form Data
    public $vehicle_name = "Mitsubishi Xpander (13)";
    public $inspectionData = [
        'front' => ['condition' => 'good', 'photo' => null],
        'rear'  => ['condition' => 'good', 'photo' => null],
        'left'  => ['condition' => 'good', 'photo' => null],
        'right' => ['condition' => 'good', 'photo' => null],
        'roof'  => ['condition' => 'good', 'photo' => null],
    ];

    // Navigation
    public function setStep($step) { $this->currentStep = $step; }
    
    public function next() { $this->currentStep++; }
    public function back() { $this->currentStep--; }

    // Modal Logic
    public function openZone($zone) { $this->activeZone = $zone; }
    public function closeZone() { $this->activeZone = null; }

    public function render()
    {
        return view('livewire.pre-rent-inspection');
    }
}