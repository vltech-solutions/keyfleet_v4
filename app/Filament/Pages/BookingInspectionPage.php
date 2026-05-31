<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Booking;
use App\Models\BookingInspection;
use App\Models\ChecklistItem;
use App\Models\Inspection;
use App\Models\InspectionItem;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class BookingInspectionPage extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions, WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'livewire.booking.inspection-page';
    protected static ?string $slug = 'booking-inspection/{bookingId}/{type}';
    protected static bool $shouldRegisterNavigation = false;

    public $booking;
    public $inspectionType;

    public $currentStep = 1; // Defaulting to Exterior for this demo
    public $activeZone = null;
    
    // Form Data
    public $vehicle_name;
    public $inspectionData = [];

    public $selectedPoint = null;

    // Coordinate mapping (Top and Left percentages)
    public $points = [
        1  => ['top' => 5,  'left' => 50],
        2  => ['top' => 12, 'left' => 82],
        3  => ['top' => 28, 'left' => 92],
        4  => ['top' => 50, 'left' => 92],
        5  => ['top' => 72, 'left' => 92],
        6  => ['top' => 88, 'left' => 82],
        7  => ['top' => 95, 'left' => 50],
        8  => ['top' => 88, 'left' => 18],
        9  => ['top' => 72, 'left' => 8],
        10 => ['top' => 50, 'left' => 8],
        11 => ['top' => 28, 'left' => 8],
        12 => ['top' => 12, 'left' => 18],
    ];

    public $interiorPoints = [
        1 => ['top' => 33, 'left' => 34], // Steering wheel / Driver area
        2 => ['top' => 50, 'left' => 49], // Center Console / Front seats
        3 => ['top' => 70, 'left' => 49], // Rear seats
    ];

    public ?array $data = [
        'fuel_level' => 50, // Default to half tank
        'odometer' => null,
        'autosweep_balance' => null,
        'easytrip_balance' => null,
    ];

    public $functions = [
        'all_lights' => true,
        'doors_and_locks' => true,
        'wipers' => true,
        'aircon' => true,
        'handbrake' => true,
        'power_windows_sunroof' => true,
        'radio_infotainment' => true,
        'horn' => true,
        'dashcam' => true,
        'fuel_cap' => true
    ];

    public $tires = [
        'front_left' => 'Good',
        'front_right' => 'Good',
        'rear_left' => 'Good',
        'rear_right' => 'Good',
        'spare_tire' => 'Present',
        'tools_jack' => 'Present',
        'early_warning' => 'Present',
    ];

    public $signee_name;
    public $general_notes;
    public $signature;

    public function mount($bookingId, $type)
    {
        $this->booking = Booking::with(['car', 'customer'])->findOrFail($bookingId);

        $this->inspectionType = $type;

        if ($this->booking->vehicle) {
            $this->vehicle_name = "{$this->booking->vehicle->model} ({$this->booking->vehicle->plate_number})";
        }
        
        // Preload data if this is a POST (return) inspection
        if ($this->inspectionType === 'post' && $this->booking->preInspection) {
            $pre = $this->booking->preInspection;

            // 1. Preload Basic Data (Counters & Fuel)
            $this->data = [
                'fuel_level' => $pre->gas,
                'odometer' => $pre->odo,
                'autosweep_balance' => $pre->autosweep,
                'easytrip_balance' => $pre->easytrip,
            ];

            // 2. Preload Arrays (Tires & Functions)
            // Ensure these are cast to arrays in your BookingInspection model
            $this->tires = $pre->tires ?? $this->tires;
            $this->functions = $pre->functions ?? $this->functions;

            // 3. Preload Zone Items (Damage/Notes)
            // This allows the user to see what was marked during pickup
            foreach ($pre->items as $item) {
                $this->inspectionData[$item->zone_id] = [
                    'condition' => $item->condition,
                    'notes' => "[PRE: " . ($item->notes ?? 'No notes') . "]", // Visual indicator of old notes
                    'pre_photo' => $item->photo_path, // Keep reference to old photo if needed
                ];
            }
        }

        $this->vehicle_name = $this->booking->car->brand." ".$this->booking->car->model." ".$this->booking->car->year ." (".$this->booking->car->name.")" ;
    }

    public function selectPoint($number)
    {
        $zoneId = ($this->currentStep == 3) ? "Interior_{$number}" : $number;
        $this->selectedPoint = ($this->currentStep == 3) ? "Interior_{$number}" : $number;
        $this->activeZone = $zoneId;
    }

    public function setStep($step) { $this->currentStep = $step; }
    
    public function next() { $this->currentStep++; }
    public function back() { $this->currentStep--; }

    public function openZone($zone) { $this->activeZone = $zone; }
    public function closeZone() { $this->activeZone = null; }
    

    public function getPrePhotoUrl($zone)
    {
        $path = $this->inspectionData[$zone]['pre_photo'] ?? null;

        if (!$path) return null;

        // This creates a signed URL valid for 20 minutes
        return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(20));
    }

    public function getPhotoPreview($zoneId)
    {
        $photo = $this->inspectionData[$zoneId]['photo'] ?? null;

        if ($photo instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            try {
                // dd($photo);
                return Storage::url($photo->originalPath);
                // $photo->temporaryUrl();

            } catch (\Exception $e) {
                // Fallback: If the URL fails, return the base64 encoded string
                return 'data:image/jpeg;base64,' . base64_encode(file_get_contents($photo->getRealPath()));
            }
        }

        return null;
    }

    protected function cleanupTemporaryFiles()
    {
        foreach ($this->inspectionData as $zoneId => $details) {
            if (isset($details['photo']) && $details['photo'] instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                // Delete the file from the temporary disk
                $details['photo']->delete();
            }
        }

        // Reset the photo index in the array so the UI knows they are gone
        foreach ($this->inspectionData as $zoneId => $details) {
            unset($this->inspectionData[$zoneId]['photo']);
        }
    }

    public function removePhoto($zoneId)
    {
        $photo = $this->inspectionData[$zoneId]['photo'] ?? null;

        // Check if it's a valid temporary file object
        if ($photo instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            $photo->delete(); // This physically removes it from storage/app/public/livewire-tmp
        }

        // Reset the data in the array
        $this->inspectionData[$zoneId]['photo'] = null;
        
        // Optional: If you used the Alpine.js localPreview, 
        // you might want to dispatch an event to clear it
        $this->dispatch('photo-removed-' . $zoneId);
    }

    public function saveInspection()
    {
        DB::beginTransaction();
        try {

            $signaturePath = null;

            // 1. Handle the Signature Upload
            if ($this->signature) {
                $image = str_replace('data:image/png;base64,', '', $this->signature);
                $image = str_replace(' ', '+', $image);
                
                $filename = "sig_" . now()->timestamp . ".png";
                $signaturePath = "inspections/{$this->booking->id}/signatures/{$filename}";
                
                // Upload decoded binary to S3
                Storage::disk('s3')->put($signaturePath, base64_decode($image));
            }
            
            // 1. Create the Main Inspection Record
            $inspection = BookingInspection::create([
                'booking_id'   => $this->booking->id,
                'type'         => $this->inspectionType,
                'odo'          => $this->data['odometer'],
                'autosweep'    => $this->data['autosweep_balance'],
                'easytrip'     => $this->data['easytrip_balance'],
                'gas'          => $this->data['fuel_level'],
                'inspected_by' => auth()->id(),
                'tires'        => $this->tires,
                'functions'    => $this->functions,
                'customer_signature' => $signaturePath,
                'signee_name'        => $this->signee_name,
                'general_notes'      => $this->general_notes,
            ]);

            // 2. Process Zone Items (Damage Photos)
            foreach ($this->inspectionData as $zoneId => $details) {
                $photoPath = null;

                // Check if a NEW photo was uploaded during this session
                if (isset($details['photo']) && $details['photo'] instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    $filename = "booking_{$this->booking->id}_zone_{$zoneId}_" . now()->timestamp . "." . $details['photo']->getClientOriginalExtension();
                    
                  	$img = imagecreatefromstring(file_get_contents($details['photo']->getRealPath()));
                  	$tempPath = tempnam(sys_get_temp_dir(), 'keyfleet');
                  	imagejpeg($img, $tempPath, 70);
                  	
                  	$photoPath = "inspections/{$this->booking->id}/damages/{$filename}";
                  	Storage::disk('s3')->put($photoPath, file_get_contents($tempPath));
                  
                    // $photoPath = $details['photo']->storeAs(
                    //    "inspections/{$this->booking->id}/damages", 
                    //    $filename, 
                    //    's3'
                    // );
                  
                  	imagedestroy($img);
                  	unlink($tempPath);
                } 
                // If no new photo, check if there was a pre-existing photo we should carry over
                elseif (isset($details['pre_photo']) && !empty($details['pre_photo'])) {
                    $photoPath = $details['pre_photo'];
                }

                $inspection->items()->create([
                    'zone_id'    => $zoneId,
                    'condition'  => $details['condition'] ?? 'good',
                    'notes'      => $details['notes'] ?? null,
                    'photo_path' => $photoPath, // Now correctly persists the old path if no new one is taken
                ]);
            }

            DB::commit();

            $this->cleanupTemporaryFiles();

            Notification::make()
                ->title('Inspection Saved Successfully!')
                ->success()
                ->send();

            // $reportUrl = route('inspection.report', $inspection->id);
            $backUrl = url("app/{$this->booking->company->slug}/view-inspection/{$inspection->id}"); 
          	
          	// window.open('{$reportUrl}', '_blank');
            $this->js("
                window.location.href = '{$backUrl}';
            ");

        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->title('Error saving inspection')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

}