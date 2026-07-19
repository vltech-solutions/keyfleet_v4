<?php

namespace App\Livewire\Booking;

use App\Models\Car;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerRequirement;
use App\Models\FundType;
use App\Models\RequirementTypes;
use App\Models\Reservation;
use App\Notifications\NewReservationNotification;
use App\Rules\SecureImage;
use App\Services\SemaphoreService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\Facades\Image;

class ViewCarDetails extends Component implements HasForms
{
    use WithFileUploads, InteractsWithForms;
    
    public Company $company;
    public $car;
    public $primaryColor;

    public $currentStep = 1;

    // Step 1
    public $name, $contact, $address, $email, $facebook;

    // Step 2
    public $start_date, $end_date, $start_time, $end_time, $destination, $pickup_option, $pickup_address, $return_address, $with_driver, $other_drivers, $source;

    // Step 3
    public $selectedCarId;

    // Step 4
    public $requirements = [];
    public $enabledRequirements = [];

    // Step 5 - Reservation Fee
    public $selectedFundType;
    public $reservation_fee_receipt;
    public $agreeToPrivacy = false;

    public $tenant;
    public $cars;
    public $carTypes;
    public $selectedCarType = null;
    public $bookingSources;

    public $openRepeat = false;
    public $repeat_token = null;
    public $repeatRenterData = [];
    public $busyDates = [];

    public $showQRModal = false;
    public $qrCodeData = '';
    public $qrCustomerName = '';
    public $qrReservationNumber = '';
    public $qrRepeatToken = '';
    public $qrIsNewCustomer = false;

    // Customer session data
    public $isLoggedIn = false;
    public $loggedInCustomer = null;

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
        if (!$companyInfo->hasAddon('booking-pro')) {
            if (!$companyInfo->hasNonBasicPaidSubscription()) {
                if (!$companyInfo->hasActiveFreeSubscription()) {
                    abort(403, 'The booking service is not available at the moment.');
                } else {
                    return redirect()->route('booking.wizard.v2', ['tenant' => $tenant]);
                }
            } else {
                if (!$companyInfo->hasAddon('booking-pro')) {
                    // return redirect()->route('booking.wizard.v2', ['tenant' => $tenant]);
                }
            }
        }

        $this->company = $companyInfo;
        $this->car = $car;
        $this->selectedCarId = $this->car->id;

        $this->reservation_fee_receipt = null;

        $this->busyDates = $this->car->getBusyDates();
        $this->primaryColor = $this->company->primary_color;

        $this->enabledRequirements = $this->company->enabled_requirements;

        // Check if customer is logged in and auto-populate
        $this->loadCustomerSession();

        $this->form->fill();
    }

    /**
     * Load customer data from session if logged in
     */
    protected function loadCustomerSession()
    {
        $customerToken = session('customer_token');
        
        if (!empty($customerToken)) {
            $customer = Customer::where('repeat_token', $customerToken)
                ->where('company_id', $this->company->id)
                ->with('requirements')
                ->first();

            if ($customer) {
                $this->isLoggedIn = true;
                $this->loggedInCustomer = $customer;
                
                // Auto-populate the form fields
                $this->name = $customer->customer_name;
                $this->contact = $customer->contact_number;
                $this->email = $customer->email;
                $this->facebook = $customer->facebook_name;
                $this->address = $customer->address;

                // Load valid requirements
                $validRequirements = $customer->requirements->filter(function ($req) {
                    return is_null($req->expiration) || $req->expiration >= now();
                });

                foreach ($validRequirements as $req) {
                    $this->requirements[$req->requirement_type] = $req->path;
                }
            }
        }
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

    public function getFundsWithBankDetails()
    {
        return FundType::where('company_id', $this->company->id)
            ->whereNotNull('account_number')
            ->whereNotNull('account_name')
            ->get();
    }

    public function getReservationFeeAmount()
    {
        if (!$this->company->is_reservation_fee_enabled) {
            return 0;
        }

        return $this->company->reservation_fee_amount ?? 0;
    }

     /**
     * Handle secure file upload
     */
    protected function handleSecureFileUpload($file, $customerId, $requirementId)
    {
        // Generate a secure filename
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . uniqid() . '.' . $extension;
        
        // Sanitize the filename
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Store in a secure directory structure
        $path = $file->storeAs(
            "requirements/{$customerId}/" . date('Y/m/d'),
            $filename,
            's3'
        );
        
        return $path;
    }

    /**
     * Validate file before upload
     */
    protected function validateFileUpload($file, $requirementId)
    {
        // Additional server-side validation
        $validator = validator(['file' => $file], [
            'file' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp,bmp',
                'max:5120',
                new SecureImage(),
            ]
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first('file'));
        }

        return true;
    }

    /**
     * Clean old files
     */
    protected function cleanOldFile($customerId, $requirementId)
    {
        $existing = CustomerRequirement::where('customer_id', $customerId)
            ->where('requirement_type', $requirementId)
            ->first();

        if ($existing && Storage::disk('s3')->exists($existing->path)) {
            Storage::disk('s3')->delete($existing->path);
        }
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
        } elseif ($step === 3) {
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
                        $rules[$field] .= '|image|mimes:jpeg,png,jpg,gif,webp,bmp|max:5120'; // 5MB max
                        $rules[$field] .= '|dimensions:min_width=50,min_height=50,max_width=10000,max_height=10000'; // Add dimensions validation
                    }

                    $messages["{$field}.required"] = "The {$req->label} is required.";
                    $messages["{$field}.image"] = "The {$req->label} must be a valid image file.";
                    $messages["{$field}.mimes"] = "The {$req->label} must be a JPG, PNG, GIF, WEBP, or BMP file.";
                    $messages["{$field}.max"] = "The {$req->label} size must not exceed 5MB.";
                    $messages["{$field}.dimensions"] = "The {$req->label} must be at least 50x50 pixels.";
                }
            }

            $this->validate($rules, $messages);
        } elseif ($step === 4) {
            $rules = [
                'agreeToPrivacy' => 'accepted',
            ];

            if ($this->company->is_reservation_fee_enabled) {
                $rules['selectedFundType'] = 'required';
                $rules['reservation_fee_receipt'] = 'required|image|max:2048';
            }

            $this->validate($rules);
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

            $isNewCustomer = false;

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
                $existingCustomer = Customer::where('customer_name', $this->name)
                    ->where('contact_number', $this->contact)
                    ->where('company_id', $this->company->id)
                    ->first();

                if (!$existingCustomer) {
                    $isNewCustomer = true;
                }

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
                'source_id'          => $this->source,
                'fund_type_id'       => $this->selectedFundType,
                'reservation_fee'    => $this->getReservationFeeAmount(),
            ]);

            // Save reservation fee receipt
            if ($this->reservation_fee_receipt && $this->company->is_reservation_fee_enabled) {
                $path = $this->reservation_fee_receipt->store("reservation-fees/{$reservation->id}", 's3');
                $reservation->update([
                    'reservation_fee_receipt' => $path,
                ]);
            }

            // Save requirements with secure upload
            foreach ($this->enabledRequirements as $requirementId) {
                $file = $this->requirements[$requirementId] ?? null;

                if (!$file) continue;

                if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    try {
                        // Validate the file
                        $this->validateFileUpload($file, $requirementId);
                        
                        // Clean old file
                        $this->cleanOldFile($customer->id, $requirementId);
                        
                        // Upload file securely
                        $path = $this->handleSecureFileUpload($file, $customer->id, $requirementId);
                        
                        // Save to database
                        CustomerRequirement::updateOrCreate(
                            [
                                'customer_id'      => $customer->id,
                                'requirement_type' => $requirementId,
                            ],
                            [
                                'path'          => $path,
                                'status'        => 'pending',
                                'date_uploaded' => now(),
                                'file_name'     => $file->getClientOriginalName(),
                                'file_size'     => $file->getSize(),
                                'mime_type'     => $file->getMimeType(),
                            ]
                        );
                    } catch (\Exception $e) {
                        \Log::error('File upload failed: ' . $e->getMessage());
                        throw new \Exception('Failed to upload requirement: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            // Generate QR Code data
            $this->qrCodeData = $this->generateQRCode($customer);
            $this->qrCustomerName = $customer->customer_name;
            $this->qrReservationNumber = (string) $reservationNumber;
            $this->qrRepeatToken = $customer->repeat_token;
            $this->qrIsNewCustomer = $isNewCustomer;
            $this->showQRModal = true;

            //send sms
            // $carDetails = Car::find($this->selectedCarId);
            // $message = 'New reservation received! Car: '.$carDetails->name.', Reservation ID: '.$reservationNumber.'. Please check your account for details.';
            // $notifNumber = $this->company?->notif_contact;

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

            // Pushnotif
            // $admins = $this->company->users; 

            // foreach ($admins as $admin) {
            //     $admin->notify(new NewReservationNotification($reservation));
            // }

            Notification::make()
                ->title('Great!')
                ->body("Your reservation #" . $reservationNumber . " has been successfully submitted")
                ->success()
                ->send();

            return null;

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Booking Save Failed: ' . $e->getMessage());

            Notification::make()
                ->title('Error!')
                ->body('There was a problem saving your booking. Please try again.')
                ->danger()
                ->send();
        }
    }

    /**
     * Generate QR Code for customer
     */
    protected function generateQRCode($customer)
    {
        try {
            // Generate QR Code
            $qrBase64 = base64_encode(
                QrCode::format('png')
                    ->size(500)
                    ->margin(2)
                    ->color(0, 0, 0)
                    ->backgroundColor(255, 255, 255)
                    ->generate($customer->repeat_token)
            );

            $qrBinary = base64_decode($qrBase64);
            $image = Image::make($qrBinary);

            // Add company logo if available
            if ($this->company->avatar_url) {
                $logoPath = storage_path('app/public/' . $this->company->avatar_url);
                if (file_exists($logoPath)) {
                    $logoSize = 70;
                    $logo = Image::make($logoPath)->resize($logoSize, $logoSize, function ($c) {
                        $c->aspectRatio();
                        $c->upsize();
                    });

                    $boxSize = $logoSize + 20;
                    $box = Image::canvas($boxSize, $boxSize, '#ffffff');
                    $box->rectangle(0, 0, $boxSize - 1, $boxSize - 1, function ($draw) {
                        $draw->border(1, '#000');
                        $draw->background('#dddddd');
                    });
                    $box->mask(Image::canvas($boxSize, $boxSize), true);
                    $box->insert($logo, 'center');
                    $image->insert($box, 'center');
                }
            }

            return 'data:image/png;base64,' . base64_encode($image->encode('png'));
        } catch (\Exception $e) {
            \Log::error('QR Code Generation Failed: ' . $e->getMessage());
            return null;
        }
    }

    public function render()
    {
        return view('livewire.booking.view-car-details', [
            'isLoggedIn' => $this->isLoggedIn,
            'loggedInCustomer' => $this->loggedInCustomer,
        ])->layout('components.layouts.client-website', ['company' => $this->company]);
    }
}