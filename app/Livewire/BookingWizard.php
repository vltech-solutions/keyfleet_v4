<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerRequirement;
use App\Models\RequirementTypes;
use App\Models\Reservation;
use App\Services\SemaphoreService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Livewire\Attributes\On; 
use App\Notifications\NewReservationNotification;
use App\Models\User;

class BookingWizard extends Component implements HasForms
{
    use WithFileUploads;

    use InteractsWithForms;

    public $currentStep = 1;

    // Step 1
    public $name, $contact, $address,$email,$facebook;

    // Step 2
    public $start_date, $end_date, $start_time, $end_time, $destination, $pickup_option, $pickup_address, $return_address, $with_driver, $other_drivers, $source;

    // Step 3
    public $selectedCarId;

    // Step 4
    public $requirements = [];

     public $tenant;
     public $cars;
     public $carTypes;
     public $selectedCarType = null;
     public $bookingSources;

     public $openRepeat = false;
     public $repeat_token = null;
     public $repeatRenterData = [];

    public bool $agreeToPrivacy = false;
    protected $rules = [
        'agreeToPrivacy' => 'accepted',
    ];

    public Company $company;

    public function mount($tenant)
    {   
        $companyInfo = Company::where('slug', $tenant)->first();

        if (! $companyInfo) {
            abort(404); 
        }

        if (! $companyInfo->hasNonBasicPaidSubscription()) {
            // check if has free trial
            if(!$companyInfo->hasActiveFreeSubscription()) {
                abort(403, 'The booking service is not available at the moment.');
            }
        }
      
      //  if (! $companyInfo->hasNonBasicPaidSubscription()) {
      //           if($companyInfo->hasAddon('booking-pro')){
      //               return redirect()->route('client.page',['tenant' => $tenant]);
      //           }else{
      //               if(!$companyInfo->hasActiveFreeSubscription()) {
      //                   abort(403, 'The booking service is not available at the moment.');
      //               }
      //           }
      //       }else{
      //           if($companyInfo->hasAddon('booking-pro')){
      //               return redirect()->route('client.page',['tenant' => $tenant]);
      //           }
      //       }

        $this->company = $companyInfo;
        $this->cars = Car::with('carType')->where('company_id',$this->company->id)->get();
        $this->carTypes = $this->cars->pluck('carType')->unique('id');

        $this->bookingSources = $this->company->sources;

        $this->form->fill();

    }

    public function updatedSelectedCarType($value)
    {
        $cars = $this->getAvailableCars();

        if ($value) {
            $cars = $cars->where('car_type_id', $value);
        }

        $this->cars = $cars;
    }

    public function render()
    {
        return view('livewire.booking-wizard', [
                'company' => $this->company,
                'companyLogo' => Storage::url($this->company->avatar_url),
                'form' => $this->form,
            ])
            ->layout('components.layouts.booking',['company' => $this->company]);
    }


    public function firstStepSubmit()
    {
        $this->validate([
            'name' => 'required',
            'contact' => 'required',
            'address' => 'required',
            'email' => 'email|nullable',
            'facebook' => [
                'nullable',
                'url',
                'regex:/^https:\/\/(www\.)?(facebook\.com|fb\.com)\/.+$/i',
            ],
        ]);

        $this->currentStep = 2;
    }

    public function secondStepSubmit()
    {
        $this->validate([
            'start_date'     => 'required',
            'end_date'       => 'required',
            'start_time'     => 'required',
            'end_time'       => 'required',
            'destination'    => 'required',
            'pickup_option'  => 'required',
            'source'         => 'required',
            'pickup_address' => $this->pickup_option === 'owner_delivery_renter_return' 
                                || $this->pickup_option === 'owner_delivery_owner_collection'
                                ? 'required|string|max:255'
                                : 'nullable',

            'return_address' => $this->pickup_option === 'renter_pickup_owner_collection' 
                                || $this->pickup_option === 'owner_delivery_owner_collection'
                                ? 'required|string|max:255'
                                : 'nullable',
        ]);

        $start = Carbon::parse("{$this->start_date} {$this->start_time}");
        $end   = Carbon::parse("{$this->end_date} {$this->end_time}");
        $now   = Carbon::now();
        
        if ($start->lessThan($now)) {
            $this->start_time = null;
            throw ValidationException::withMessages([
                'start_time' => 'The trip start must be in the future.',
            ]);
        }

        if ($end->lessThanOrEqualTo($start)) {
            $this->end_time = null;
            throw ValidationException::withMessages([
                'end_time' => 'The trip end must be after the trip start.',
            ]);
        }

        if($this->getAvailableCars()->isEmpty()) {
            Notification::make()
                ->title('No available cars')
                ->danger()
                ->body('There are no available cars for the selected dates.')
                ->send();
        }else{
            $this->cars = $this->getAvailableCars();
            $this->currentStep = 3;
        }
    }

    public function thirdStepSubmit()
    {
        if($this->selectedCarId == null){
            Notification::make()
                ->title('No car selected')
                ->danger()
                ->body('Please select a car.')
                ->send();
            return;
        }

        $this->currentStep = 4;
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

    public function submitForm()
    {
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

        //second step save the reservation
        $reservation = Reservation::create([
            'customer_id'     => $customer->id,
            'start_date'      => Carbon::parse($this->start_date . ' ' . $this->start_time),
            'end_date'        => Carbon::parse($this->end_date . ' ' . $this->end_time),
            'destination'     => $this->destination,
            'pickup_option'   => $this->pickup_option,
            'pickup_address'  => $this->pickup_address,
            'return_address'  => $this->return_address,
            'with_driver'     => $this->with_driver ?? false,
            'other_drivers'   => $this->other_drivers,
            'selected_car_id' => $this->selectedCarId,
            'status'          => 'pending',
            'company_id' => $this->company->id,
            'reservation_number' => $reservationNumber,
            'source_id'       => $this->source
        ]);

        // third step save the requirements
        $saveFileUpload = $this->form->getState();
        $enabled = $this->company->enabled_requirements ?? [];

        foreach ($enabled as $requirementId) {
            $path = $saveFileUpload['requirements'][$requirementId] ?? null;

            if (! $path) {
                continue;
            }
            
            if (is_array($path)) {
                $path = $path[0];
            }

            $existing = CustomerRequirement::where('customer_id', $customer->id)
                ->where('requirement_type', $requirementId) 
                ->first();

            $expiration = null;
            $dateUploaded = now();

            if ($existing) {
                if ($existing->path === $path) {
                    $expiration = $existing->expiration;
                    $dateUploaded = $existing->date_uploaded;
                } else {
                    if (Storage::disk('public')->exists($existing->path)) {
                        Storage::disk('public')->delete($existing->path);
                    }
                }
            }

            CustomerRequirement::updateOrCreate(
                [
                    'customer_id'      => $customer->id,
                    'requirement_type' => $requirementId,   
                ],
                [
                    'path'          => $path,
                    'status'        => 'approved',
                    'date_uploaded' => $dateUploaded,
                    'expiration'    => $expiration,
                ]
            );
        }


        //send sms
        $carDetails = Car::find($this->selectedCarId);
        $message = 'New reservation received! Car: '.$carDetails->name.', Reservation ID: '.$reservationNumber.'. Please check your account for details.';
        $notifNumber = $this->company?->notif_contact;

        

      	if (!empty($notifNumber)) {
             try {
                 $response = SemaphoreService::send($notifNumber, $message);

             } catch (\Throwable $e) {
                 \Log::warning('Company SMS sending failed', [
                     'number' => $notifNumber,
                     'error' => $e->getMessage(),
                 ]);
             }
         }
      
      	$admins = $this->company->users; 

        foreach ($admins as $admin) {
            $admin->notify(new NewReservationNotification($reservation));
        }

        return redirect()->route('reservation.success', [
            'reservationNumber' => $reservationNumber,
            'tenant' => $this->company->slug,
        ]);
    }

    public function back()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    protected function getAvailableCars()
    {
        if (! $this->start_date || ! $this->start_time || ! $this->end_date || ! $this->end_time) {
            return collect(); 
        }

        $start = Carbon::parse("{$this->start_date} {$this->start_time}");
        $end   = Carbon::parse("{$this->end_date} {$this->end_time}");

        $unavailableCarIds = Booking::where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_datetime', [$start, $end])  
                ->orWhereBetween('end_datetime', [$start, $end])   
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('start_datetime', '<=', $start)    
                        ->where('end_datetime', '>=', $end);
                });
            })
            ->pluck('car_id');

        $cars =  Car::with('carType')
            ->where('company_id', $this->company->id)
            ->whereNotIn('id', $unavailableCarIds)
            ->get();

        $this->carTypes = $cars->pluck('carType')->unique('id');

        return $cars;
    }

    protected function getFormSchema(): array
    {
        $enabled = $this->company->enabled_requirements ?? [];
        $schema = [];

        if (empty($enabled)) {
            return $schema;
        }

        $requirements = RequirementTypes::whereIn('id', $enabled)->get();

        foreach ($requirements as $req) {
            $schema[] = FileUpload::make("requirements.{$req->id}")
                ->label($req->label)
                ->helperText($req->helper)
                ->disk('s3')
                ->directory('requirements')
                ->visibility('private')
                ->image()
                ->acceptedFileTypes(['image/jpeg', 'image/png'])
                ->required($req->required)
                ->multiple(false)
                ->resize(50)
                ->maxSize(5120)
              	->preserveFilenames();
        }

        return $schema;
    }

    public function submitRepeatToken()
    {
        $customer = Customer::where('repeat_token', $this->repeat_token)
            ->where('company_id', $this->company->id)
            ->with('requirements')
            ->first();
        
        if (! $customer) {
            $this->addError('repeat_token', 'Renter not found. QR code may have expired.');
            $this->openRepeat = true;
            return;
        }

        Notification::make()
            ->title('Renter found')
            ->body('Name: '.$customer->customer_name)
            ->success()
            ->duration(3000)
            ->send();

        $this->openRepeat = false;

        // Base renter details
        $fillupData = [
            'name'     => $customer->customer_name,
            'email'    => $customer->email,
            'address'  => $customer->address,
            'contact'  => $customer->contact_number,
            'facebook' => $customer->facebook_name,
        ];

        // Valid requirements only
        $validRequirements = $customer->requirements
            ->filter(fn ($req) => $req->expiration >= now());

        // Map requirements.{id} => path
        $requirementsData = [];
        foreach ($validRequirements as $req) {
            $requirementsData[$req->requirement_type] = $req->path;
        }

        // Merge into fillupData
        $fillupData['requirements'] = $requirementsData;

        // Fill the Livewire form
        $this->form->fill($fillupData);
      
    }

    #[On('qrDownloadFailed')] 
    public function qrDownloadFailed()
    {
        Notification::make()
            ->danger()
            ->title('QR Download Error')
            ->body('Invalid QR code. Please try again.')
            ->send();
    }

}
