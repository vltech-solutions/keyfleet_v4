<?php

namespace App\Filament\Resources\ReservationsResource\Pages;

use App\Filament\Resources\ReservationsResource;
use App\Models\Booking;
use Carbon\Carbon;
use Filament\Resources\Pages\EditRecord;
use Filament\Facades\Filament; 
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Fieldset;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;

use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use App\Models\Car;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerRequirement;
use App\Services\SemaphoreService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;

class ViewReservation extends EditRecord
{
    protected static string $resource = ReservationsResource::class;

    protected static string $view = 'filament.pages.view-reservation';
    protected static ?string $title = '';

    public static function getLabel(): string
    {
        return 'Company Settings';
    }


    protected function getHeaderActions(): array
    {
        $start = $this->record->start_date;
        $end   = $this->record->end_date;
        $carId = $this->record->selected_car_id;

        // check availability
        $isAvailable = true;
        if ($start && $carId) {
            $isAvailable = Car::isAvailableAt($carId, $start, $end, $this->record->id);
        }

        if (! $isAvailable && $this->record->status === 'pending') {
            $this->record->update([
                'status' => 'declined',
                'decline_reason' => 'Dates are not available'
            ]);
        }

         if ($end && $end->isPast()) {
            $this->record->update([
                'status' => 'declined',
                'decline_reason' => 'Past Reservation'
            ]);
        }

        $daysRented = 0;
        $extendedHours = 0;

        if ($start && $end) {
            $diff = Carbon::parse($start)->diff(Carbon::parse($end));

            $daysRented = $diff->days; 
            $extendedHours = $diff->h; 
            
            if ($daysRented === 0 && $extendedHours > 0) {
                $daysRented = 1;
                $extendedHours = 0;
            }
        }

        $actions = auth()->user()->hasActiveSubscription() && $this->record->status === 'pending'
            ? [
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    // ->icon('heroicon-o-check')
                    ->closeModalByClickingAway(false)
                    ->form([
                        Wizard::make([
                            Step::make('Pricing & Charges')
                                ->description('Rental Fees and computation')
                                ->icon('heroicon-m-currency-dollar')
                                ->schema([
                                    Grid::make()
                                        ->columns([
                                            'sm' => 2,
                                            'lg' => 3,
                                            'xl' => 4
                                        ])
                                        ->schema([
                                            TextInput::make('days_rented')
                                                ->label('Days Rented')
                                                ->default($daysRented)
                                                ->readOnly(),

                                            TextInput::make('daily_rate')
                                                ->label('Daily Rate')
                                                ->numeric()
                                                ->default(0)
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                            TextInput::make('extend_hours')
                                                ->label('Extend Hours')
                                                ->default($extendedHours)
                                                ->readOnly(),

                                            TextInput::make('extend_due')
                                                ->label('Extend Fee')
                                                ->numeric()
                                                ->default(0)
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                            TextInput::make('delivery_fee')
                                                ->label('Delivery Fee')
                                                ->numeric()
                                                ->default(0)
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                            TextInput::make('discount')
                                                ->label('Discount')
                                                ->numeric()
                                                ->default(0)
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                            TextInput::make('driver_fee')
                                                ->label('Driver\'s Fee')
                                                ->numeric()
                                                ->default(0)
                                                ->minValue(0)
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                            TextInput::make('security_deposit')
                                                ->label('Security Deposit')
                                                ->numeric()
                                                ->default(0)
                                                ->minValue(0)
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),
                                        ]),
                                    Fieldset::make('Additional Charges')
                                        ->label('Optional charges based on usage or condition')
                                        ->schema([
                                            Grid::make()
                                                ->columns([
                                                    'sm' => 2,
                                                    'lg' => 3,
                                                    'xl' => 4,
                                                ])
                                                ->schema([
                                                    TextInput::make('fuel_charge')
                                                        ->label('Fuel Charge')
                                                        ->numeric()
                                                        ->default(0)
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                                    TextInput::make('out_of_bounds')
                                                        ->label('Out-of-Bounds Charge')
                                                        ->numeric()
                                                        ->default(0)
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                                    TextInput::make('rfid')
                                                        ->label('RFID Charge')
                                                        ->numeric()
                                                        ->default(0)
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                                    TextInput::make('damages')
                                                        ->label('Damage Fees')
                                                        ->numeric()
                                                        ->default(0)
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                                    TextInput::make('carwash_fee')
                                                        ->label('Car Wash Fees')
                                                        ->numeric()
                                                        ->default(0)
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                                    TextInput::make('insurance')
                                                        ->label('Insurance Fee')
                                                        ->numeric()
                                                        ->default(0)
                                                        ->live(onBlur: true)
                                                        ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),


                                                ]),
                                        ]),
                                ]),

                            Step::make('Billing Summary')
                                ->description('Final payment summary')
                                ->icon('heroicon-m-banknotes')
                                ->schema([
                                    Grid::make()
                                        ->columns([
                                            'sm' => 2,
                                            'lg' => 3,
                                            'xl' => 4
                                        ])
                                        ->schema([
                                            TextInput::make('total_rent_due')
                                                ->label('Total Rent')
                                                ->default(0)
                                                ->readOnly(),

                                            TextInput::make('total_due')
                                                ->label('Grand Total')
                                                ->default(0)
                                                ->readOnly()
                                                ->extraInputAttributes([
                                                    'class' => 'text-blue-800 font-bold'
                                                ]),

                                            TextInput::make('paid_amount')
                                                ->label('Amount Paid')
                                                ->numeric()
                                                ->default(0)
                                                ->readOnly()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                            TextInput::make('balance')
                                                ->label('Balance')
                                                ->default(0)
                                                ->readOnly()
                                                ->extraInputAttributes([
                                                    'class' => 'text-red-800 dark:text-red-800 font-bold'
                                                ]),

                                            Hidden::make('car_id')
                                                ->default($this->record->selected_car_id),

                                            TextInput::make('company_earnings')
                                                ->label('Company Commission')
                                                ->default(0)
                                                ->extraInputAttributes([
                                                    'class' => 'text-warning-800 font-bold'
                                                ])
                                                ->visible(function ($get) {
                                                    $carId = $this->record->selected_car_id;
                                                    $car = Car::find($carId);
                                                    return $car && $car->partner_id !== null;
                                                }),
                                        ]),
                                ]),
                        ]),
                    ])
                    ->action(function (array $data, $record) {
                       

                        // Compute totals
                        $totalRent = ($data['days_rented'] * $data['daily_rate']) + $data['extend_due'];
                        $totalDue = (
                            $totalRent +
                            $data['delivery_fee'] +
                            $data['driver_fee'] +
                            $data['security_deposit'] +
                            ($data['fuel_charge'] ?? 0) +
                            ($data['out_of_bounds'] ?? 0) +
                            ($data['rfid'] ?? 0) +
                            ($data['damages'] ?? 0) +
                            ($data['carwash_fee'] ?? 0)
                        ) - $data['discount'];

                        // Commission logic
                        $car = Car::find($record->selected_car_id);
                        $partner = $car?->partner;

                        $partnerCommission = 0;
                        $companyEarnings   = 0;

                        if ($partner) {
                            $baseAmount = match ($partner->commission_base) {
                                'total_due' => $totalDue,
                                default     => $totalRent,
                            };

                            if ($partner->commission_type === 'percentage') {
                                $companyEarnings = ($partner->commission_value / 100) * $baseAmount;
                            } else {
                                $companyEarnings = $partner->commission_value;
                            }

                            // partner gets the rest of the earnings
                            $partnerCommission = $totalDue - $companyEarnings;
                        } else {
                            $companyEarnings = $totalDue;
                        }

                        $booking = Booking::create([
                            'car_id'            => $record->selected_car_id,
                            'source_id'         => $record->source_id,
                            'start_datetime'    => $record->start_date,
                            'end_datetime'      => $record->end_date,
                            'customer_id'       => $record->customer_id,
                            'renter_name'       => $record->customer?->customer_name,
                            'renter_address'    => $record->customer?->address,
                            'contact_number'    => $record->customer?->contact_number,
                            'destination'       => $record->destination,
                            'delivery_address'  => $record->pickup_address,
                            'return_address'    => $record->return_address,
                            'daily_rate'        => $data['daily_rate'],
                            'days_rented'       => $data['days_rented'],
                            'extend_hours'      => $data['extend_hours'],
                            'extend_due'        => $data['extend_due'],
                            'total_rent_due'    => $totalRent,
                            'delivery_fee'      => $data['delivery_fee'],
                            'discount'          => $data['discount'],
                            'fuel_charge'       => $data['fuel_charge'] ?? 0,
                            'out_of_bounds'     => $data['out_of_bounds'] ?? 0,
                            'rfid'              => $data['rfid'] ?? 0,
                            'damages'           => $data['damages'] ?? 0,
                            'carwash_fee'       => $data['carwash_fee'] ?? 0,
                            'with_driver'       => $record->with_driver,
                            'driver_fee'        => $data['driver_fee'],
                            'security_deposit'  => $data['security_deposit'],
                            'remarks'           => $data['remarks'] ?? null,
                            'other_drivers'     => $record->other_drivers,
                            'company_id'        => $record->company_id,
                            'total_due'         => $totalDue,
                            'paid_amount'       => 0,
                            'balance'           => $totalDue,
                            'partner_commission'=> $partnerCommission,
                            'company_earnings'  => $companyEarnings,
                        ]);

                        $record->update([
                            'booking_id' => $booking->id,
                            'status' => 'approved',
                        ]);

                        $companyInfo = Company::find($record->company_id);

                        //approve all requirements also
                        if ($record->customer && $record->customer->requirements) {
                            $record->customer->requirements->each(function ($requirement) use ($companyInfo) {
                                if (is_null($requirement->expiration)) {
                                    $requirement->update([
                                        'status' => 'approved',
                                        'expiration' => now()->addMonths($companyInfo->requirements_expiry_months),
                                    ]);
                                }
                            });
                        }

                        //send message to the customer;
                        $message = "Booking approved! Track it using the booking form. Ref#: ".$record->reservation_number." - ".$companyInfo->name;
                        $renterNumber = $record->customer?->contact_number;
                         if (!empty($renterNumber)) {
                             try {
                                 $response = SemaphoreService::send($renterNumber, $message);

                             } catch (\Throwable $e) {
                                //  Don’t throw an error, just log silently
                                 \Log::warning('SMS sending failed', [
                                     'number' => $renterNumber,
                                     'error' => $e->getMessage(),
                                 ]);
                             }
                         }

                        Notification::make()
                            ->title('Reservation Approved & Booking Created with Pricing')
                            ->success()
                            ->send();

                    }),

                Action::make('decline')
                    ->label('Decline')
                    ->color('danger')
                    // ->icon('heroicon-o-x-mark')
                    ->form([
                        Textarea::make('decline_reason')
                            ->label('Please provide a reason for declining this reservation. This will be sent to the customer.')
                            ->required(),
                    ])
                    ->action(function (array $data, $record) {
                      
                      	//send message to the customer;
                        $message = "Booking declined. Reason: ".$data['decline_reason'];
                        $renterNumber = $record->customer?->contact_number;
                         if (!empty($renterNumber)) {
                             try {
                                 $response = SemaphoreService::send($renterNumber, $message);

                             } catch (\Throwable $e) {
                                 // Don’t throw an error, just log silently
                                 \Log::warning('SMS sending failed', [
                                     'number' => $renterNumber,
                                     'error' => $e->getMessage(),
                                 ]);
                             }
                         }

                        
                        $record->update([
                            'decline_reason' => $data['decline_reason'],
                            'status' => 'declined',
                            'declined_at' => now()
                        ]);
                      
                        Notification::make()
                              ->title('Reservation Declined!')
                              ->success()
                              ->send();

                    }),
            ]
            : [];
        
        return $actions;
    }


    public function getTitle(): string
    {
        return 'View Reservation';
    }

    protected function getQuery(): Builder
    {
        return parent::getQuery()
            ->with([
                'customer',
                'car'
            ]);
    }

    public function approveRequirement($id)
    {
        $requirement = CustomerRequirement::findOrFail($id);
        $requirement->update(['status' => 'approved']);

        Notification::make()
            ->title('Requirement Approved')
            ->success()
            ->send();
    }

    public function declineRequirement($id)
    {
        $requirement = CustomerRequirement::findOrFail($id);
        $requirement->update(['status' => 'declined']);

        $this->dispatchBrowserEvent('notify', [
            'type' => 'error',
            'message' => 'Requirement declined.',
        ]);
    }


}
