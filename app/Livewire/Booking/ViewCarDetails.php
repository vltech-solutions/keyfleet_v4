<?php

namespace App\Livewire\Booking;

use App\Models\Car;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerRequirement;
use App\Models\RequirementTypes;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ViewCarDetails extends Component implements HasForms
{
    use WithFileUploads,InteractsWithForms;
    
    public Company $company;
    public $car;
    public $primaryColor;

    public $currentStep = 1;

    // Step 1
    public $name, $contact, $address,$email,$facebook;

    // Step 2
    public $start_date, $end_date, $start_time, $end_time, $destination, $pickup_option, $pickup_address, $return_address, $with_driver, $other_drivers, $source;

    // Step 3
    public $selectedCarId;

    // Step 4
    public $requirements = [];
    public $enabledRequirements = [];

    public $tenant;
    public $cars;
    public $carTypes;
    public $selectedCarType = null;
    public $bookingSources;

    public $openRepeat = false;
    public $repeat_token = null;
    public $repeatRenterData = [];
    public $busyDates = [];

    public bool $agreeToPrivacy = false;
    protected $rules = [
        'agreeToPrivacy' => 'accepted',
    ];

    public function mount($tenant, Car $car)
    {
        $companyInfo = Company::where('slug', $tenant)->first();

        if (!$companyInfo) {
            abort(404);
        }

        // Subscription checks
        if(!$companyInfo->hasAddon('booking-pro')){
            if (!$companyInfo->hasNonBasicPaidSubscription()) {
                if (!$companyInfo->hasActiveFreeSubscription()) {
                    abort(403, 'The booking service is not available at the moment.');
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
        $this->car = $car;
        $this->selectedCarId = $this->car->id;

        $this->busyDates = $this->car->getBusyDates();
        $this->primaryColor = $this->company->primary_color;

        $this->enabledRequirements = $this->company->enabled_requirements;

        $this->form->fill();
    }

    public function getImagesProperty()
    {   
        if (!$this->car || !isset($this->car->images) || empty($this->car->images)) {
            return [];
        }

        $images = collect($this->car->images)
            ->sortBy(function ($image) {
                $type = is_array($image) ? ($image['image_type'] ?? null) : ($image->image_type ?? null);
                return $type === 'thumbnail' ? 0 : 1;
            })
            ->map(function ($image) {
                $path = is_array($image) ? ($image['path'] ?? null) : ($image->path ?? null);
                
                if (!$path) {
                    return null;
                }

                return Storage::disk('s3')->url($path);
            })
            ->filter()
            ->values();

        if ($images->isEmpty()) {
            if ($this->car->image) {
                return [Storage::disk('public')->url($this->car->image)];
            }
            
            return [asset('images/placeholder-car.jpg')];
        }

        return $images->toArray();
    }

    public function submitRepeatToken()
    {
        $customer = Customer::where('repeat_token', $this->repeat_token)
            ->where('company_id', $this->company->id)
            ->with('requirements')
            ->first();
        
        if (!$customer) {
            $this->addError('repeat_token', 'Renter not found. QR code may be invalid or expired.');
            return;
        }

        $this->name = $customer->customer_name;
        $this->email = $customer->email;
        $this->contact = $customer->contact_number;
        $this->facebook = $customer->facebook_name;
        $this->address = $customer->address;

        $validRequirements = $customer->requirements->filter(function ($req) {
            return is_null($req->expiration) || $req->expiration >= now();
        });

        $this->requirements = [];
        foreach ($validRequirements as $req) {
            $this->requirements[$req->requirement_type] = $req->path;
        }

        $this->form->fill([
            'name' => $this->name,
            'email' => $this->email,
            'contact' => $this->contact,
            'facebook' => $this->facebook,
            'address' => $this->address,
            'requirements' => $this->requirements,
        ]);

        Notification::make()
            ->title('Renter Verified')
            ->body("Welcome back, {$this->name}!")
            ->success()
            ->send();
    }

    public function getRequirementUrl($id)
    {
        $value = $this->requirements[$id] ?? null;

        if (!$value) return null;

        if (!is_string($value)) {
            try {
                return $value->temporaryUrl();
            } catch (\Exception $e) {
                return null;
            }
        }

        return Storage::disk('s3')->temporaryUrl(
            $value, 
            now()->addMinutes(10)
        );
    }

    public function getEstimateProperty()
    {
        if (!$this->start_date || !$this->end_date) return null;

        $pickup = \Carbon\Carbon::parse($this->start_date);
        $return = \Carbon\Carbon::parse($this->end_date);
        
        $days = max(1, $pickup->diffInDays($return));
        $dailyRate = $this->car['price_starts_at'] ?? 0;
        
        return [
            'days' => $days,
            'total' => $days * $dailyRate,
        ];
    }

    public function validateStep($step)
    {
        if ($step === 1) {
            $this->validate([
                'start_date' => 'required',
                'start_time' => 'required',
                'end_date' => 'required',
                'end_time' => 'required',
                'pickup_option' => 'required',
                'destination' => 'required',
            ]);
        } elseif ($step === 2) {
            $this->validate([
                'name' => 'required',
                'contact' => 'required|min:11|max:11',
                'address' => 'required',
            ]);
        }elseif ($step === 3) {
            $requirementTypes = RequirementTypes::whereIn('id', $this->enabledRequirements)->get();

            $rules = [];
            $messages = [];

            foreach ($requirementTypes as $req) {
                $field = "requirements.{$req->id}";
                $existingValue = $this->requirements[$req->id] ?? null;

                $isExistingPath = is_string($existingValue);
                
                if ($req->required) {
                    $rules[$field] = $isExistingPath ? 'nullable' : 'required';

                    if (!$isExistingPath && $existingValue !== null) {
                        $rules[$field] .= '|image|max:2048';
                    }

                    $messages["{$field}.required"] = "The {$req->label} is required.";
                    $messages["{$field}.image"] = "The {$req->label} must be a valid image file.";
                    $messages["{$field}.max"] = "The {$req->label} size must not exceed 2MB.";
                }
            }

            $this->validate($rules, $messages);

            $this->validate(
                ['agreeToPrivacy' => 'accepted']
            );
        }
        return true;
    }

    public function generateReservationNumber(): int
    {
        $companyId = $this->company->id;
        $date = Carbon::now()->format('Ymd');

        $lastReservation = Reservation::where('company_id', $companyId)
            ->whereDate('created_at', Carbon::today())
            ->orderByDesc('reservation_number')
            ->first();
        $lastSequence = 0;

        if ($lastReservation) {
            $lastSequence = (int) substr($lastReservation->reservation_number, -5);
        }

        $nextSequence = str_pad($lastSequence + 1, 5, '0', STR_PAD_LEFT);

        return (int) ($date . $companyId . $nextSequence);
    }

    public function saveBooking()
    {
        DB::beginTransaction();

        try {
            $this->validate();

            if ($this->repeat_token) {
                $customer = Customer::where('repeat_token', $this->repeat_token)->firstOrFail();

                $customer->update([
                    'customer_name'   => $this->name,
                    'contact_number'  => $this->contact,
                    'address'         => $this->address,
                    'email'           => $this->email,
                    'facebook_name'   => $this->facebook,
                    'company_id'      => $this->company->id,
                ]);
            } else {
                $customer = Customer::updateOrCreate(
                    [
                        'customer_name'   => $this->name,
                        'contact_number'  => $this->contact,
                        'company_id'      => $this->company->id,
                    ],
                    [
                        'address'       => $this->address,
                        'email'         => $this->email,
                        'facebook_name' => $this->facebook,
                    ]
                );
            }

            $reservationNumber = $this->generateReservationNumber();

            $reservation = Reservation::create([
                'customer_id'        => $customer->id,
                'start_date'         => \Carbon\Carbon::parse($this->start_date . ' ' . $this->start_time),
                'end_date'           => \Carbon\Carbon::parse($this->end_date . ' ' . $this->end_time),
                'destination'        => $this->destination,
                'pickup_option'      => $this->pickup_option,
                'pickup_address'     => $this->pickup_address,
                'return_address'     => $this->return_address,
                'with_driver'        => $this->with_driver ?? false,
                'other_drivers'      => $this->other_drivers,
                'selected_car_id'    => $this->selectedCarId,
                'status'             => 'pending',
                'company_id'         => $this->company->id,
                'reservation_number' => $reservationNumber,
                'source_id'          => $this->source
            ]);

            // third step save the requirements
            foreach ($this->enabledRequirements as $requirementId) {
                $file = $this->requirements[$requirementId] ?? null;

                if (!$file) continue;

                if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    
                    $existing = CustomerRequirement::where('customer_id', $customer->id)
                        ->where('requirement_type', $requirementId)
                        ->first();

                    if ($existing && Storage::disk('s3')->exists($existing->path)) {
                        Storage::disk('s3')->delete($existing->path);
                    }

                    // Store to S3
                    $path = $file->store("requirements/{$customer->id}", 's3');

                    CustomerRequirement::updateOrCreate(
                        [
                            'customer_id'      => $customer->id,
                            'requirement_type' => $requirementId,
                        ],
                        [
                            'path'          => $path,
                            'status'        => 'pending', 
                            'date_uploaded' => now(),
                        ]
                    );
                }
            }

            // Commit the transaction
            DB::commit();


            // $this->dispatch('notify', message: 'Your reservation #' . $reservationNumber . ' has been successfully submitted.',type:'success');

            Notification::make()
                ->title('Great!')
                ->body("Your reservation #".$reservationNumber." has been successfully submitted")
                ->success()
                ->send();
                

            // send sms
            $carDetails = Car::find($this->selectedCarId);
            $message = 'New reservation received! Car: '.$carDetails->name.', Reservation ID: '.$reservationNumber.'. Please check your account for details.';
            $notifNumber = $this->company?->notif_contact;

            // if (!empty($notifNumber)) {
            //     try {
            //         $response = SemaphoreService::send($notifNumber, $message);

            //     } catch (\Throwable $e) {
            //         \Log::warning('Company SMS sending failed', [
            //             'number' => $notifNumber,
            //             'error' => $e->getMessage(),
            //         ]);
            //     }
            // }

            // Optional: Reset properties or redirect
            return redirect()->to('/');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Booking Save Failed: ' . $e->getMessage());

            $this->dispatch('notify', message: 'There was a problem saving your booking.',type:'error');
        }
    }

    public function render()
    {
        return view('livewire.booking.view-car-details')
            ->layout('components.layouts.client-website', ['company' => $this->company]);;
    }
}
