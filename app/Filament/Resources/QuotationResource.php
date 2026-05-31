<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Filament\Resources\QuotationResource\RelationManagers;
use App\Models\Booking;
use App\Models\Quotation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Car;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Facades\Filament;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Imports\BookingsImport;
use App\Exports\BookingTemplateExport;
use App\Exports\BookingExport;
use App\Filament\Widgets\BookingStatsWidget;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\View;
use App\Models\Customer;
use App\Models\FundType;
use App\Models\Partners;
use App\Models\Reservation;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Filament\Tables\Enums\ActionsPosition;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Filament\Actions\ActionGroup;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\ActionSize;

class QuotationResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    public static function getNavigationLabel(): string
    {
        return 'Quotations';
    }

    public static function getModelLabel(): string
    {
        return 'Quotation';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Quotations';
    }

    protected static ?int $navigationSort = 0;

    public static function getNavigationGroup(): ?string
    {
        return 'Transactions';
    }

    public static function getNavigationGroupSort(): ?int
    {
        return 0;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        $updateBalance = function (callable $set, callable $get) {
            $totalDue = $get('total_due') ?? 0;
            $paidAmount = $get('paid_amount') ?? 0;

            $set('balance', $totalDue - $paidAmount);
        };

        $updateTotalDue = function (callable $set, callable $get) use ($updateBalance) {
            $totalRent = $get('total_rent_due') ?? 0;
            $deliveryFee = $get('delivery_fee') ?? 0;
            $discount = $get('discount') ?? 0;

            $totalDue = ($totalRent + $deliveryFee) - $discount;
            $set('total_due', $totalDue);

            $updateBalance($set, $get);
        };

        $contractPreviewStep = function (callable $get) {
            if (! $get('id')) {
                return []; // Don't show this step on create
            }

            $previewUrl = route('contract.preview', ['booking' => $get('id')]);

            $company = auth()->user()->companies()->first();
            if(!$company->contract) {
                return [];   
            }
            
            return [
                Step::make('Contract Preview')
                    ->description('Preview generated contract')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        View::make('filament.components.booking-contract-preview')->viewData([
                            'previewUrl' => $previewUrl,
                        ]),
                    ]),
            ];
        };

        $disabled = ! auth()->user()->hasActiveSubscription();
        return $form
            ->schema([
                Hidden::make('status')->default('quotation'),
                Wizard::make()
                    ->schema(function (callable $get) use ($contractPreviewStep) {
                        return [
                        Step::make('Booking Information')
                            ->description('Car, dates, and renter')
                            ->icon('heroicon-m-clipboard-document-list')
                            ->schema([
                                Grid::make()
                                    ->columns([
                                        'sm' => 2,
                                        'lg' => 3,
                                        'xl' => 3
                                    ])
                                    ->schema([
                                        Select::make('car_id')
                                            ->label('Car')
                                            ->relationship(
                                                name: 'car',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: fn ($query) => $query->whereNull('deleted_at')
                                            )
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($set) {
                                                $set('start_datetime', null);
                                                $set('end_datetime', null);

                                                $set('daily_rate', 0);
                                                $set('days_rented', 0);
                                                $set('extend_due', 0);
                                                $set('extend_hours', 0);
                                                $set('delivery_fee', 0);
                                                $set('discount', 0);
                                                $set('fuel_charge', 0);
                                                $set('out_of_bounds', 0);
                                                $set('rfid', 0);
                                                $set('damages', 0);
                                                $set('carwash_fee', 0);
                                                $set('paid_amount', 0);
                                                
                                                // Reset computed totals
                                                $set('total_rent_due', 0);
                                                $set('total_due', 0);
                                                $set('balance', 0);
                                                $set('company_earnings', 0);
                                            }),
                                        
                                        Hidden::make('id')
                                            ->dehydrated(false) // Don't save it, just make it available
                                            ->visible(fn () => false),

                                        DateTimePicker::make('start_datetime')
                                            ->label('Start Date & Time')
                                            ->time(true)
                                            ->native(true)
                                            ->seconds(false)
                                            ->displayFormat('M j, Y h:i A')
                                            ->reactive()
                                            ->debounce(500)
                                            ->afterStateUpdated(function (callable $set, $state, $get) {
                                                $start = $get('start_datetime');
                                                $end = $get('end_datetime');
                                                $carId = $get('car_id');
                                                $bookingId = $get('id');
                                                if (!$carId) {
                                                    $set('start_datetime', null);
                                                    Notification::make()
                                                        ->title('Please select car first.')
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }

                                                if ($state && $carId) {
                                                    if (! Car::isAvailableAt($carId, $state,$bookingId)) {
                                                        $set('start_datetime', null);
                                                        Notification::make()
                                                            ->title('Car is not available at the selected start date/time.')
                                                            ->danger()
                                                            ->send();
                                                        return;
                                                    }
                                                }
                                                
                                                if ($start && $end) {
                                                    $startCarbon = Carbon::parse($start);
                                                    $endCarbon = Carbon::parse($end);

                                                    $diffInHours = $startCarbon->diffInHours($endCarbon);

                                                    if ($diffInHours < 24) {
                                                        // Less than 24 hrs = 1 day only, no extend hours
                                                        $set('days_rented', 1);
                                                        $set('extend_hours', 0);
                                                    } else {
                                                        $days = floor($diffInHours / 24);
                                                        $extendHours = $diffInHours % 24;

                                                        $set('days_rented', $days);
                                                        $set('extend_hours', $extendHours);
                                                    }
                                                }
                                            }),


                                        DateTimePicker::make('end_datetime')
                                            ->label('End Date & Time')
                                            ->time(true)
                                            ->native(true)
                                            ->seconds(false)
                                            ->displayFormat('M j, Y h:i A')
                                            ->reactive()
                                            ->debounce(500)
                                            ->minDate(fn ($get) => $get('start_datetime'))
                                            ->afterStateUpdated(function (callable $set, $state, $get) {
                                                $start = $get('start_datetime');
                                                $end = $get('end_datetime');
                                                $carId = $get('car_id');
                                            
                                                $bookingId = $get('id');

                                            if (!$carId) {
                                                $set('start_datetime', null);
                                                Notification::make()
                                                    ->title('Please select car first.')
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }
                                            
                                            if ($start && $end && Carbon::parse($end)->lessThan(Carbon::parse($start))) {
                                                $set('end_datetime', $start);
                                                Notification::make()
                                                    ->title('End date/time cannot be earlier than start date/time.')
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }

                                            if ($state && $carId && $start) {
                                                if (! Car::isAvailableAt($carId, $start,$end,$bookingId)) {
                                                    $set('start_datetime', null);
                                                    $set('end_datetime', null);
                                                    $set('days_rented', 0);
                                                    $set('extend_hours', 0);
                                                    Notification::make()
                                                        ->title('Car is not available at the selected date/times.')
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }
                                            }

                                            if ($start && $end) {
                                                $startCarbon = Carbon::parse($start);
                                                $endCarbon = Carbon::parse($end);

                                                $diffInHours = $startCarbon->diffInHours($endCarbon);

                                                if ($diffInHours < 24) {
                                                    // Less than 24 hrs = 1 day only, no extend hours
                                                    $set('days_rented', 1);
                                                    $set('extend_hours', 0);
                                                } else {
                                                    $days = floor($diffInHours / 24);
                                                    $extendHours = $diffInHours % 24;

                                                    $set('days_rented', $days);
                                                    $set('extend_hours', $extendHours);
                                                }
                                            }

                                            calculateTotalBookingDue($set, $get);
                                        }),

                                        Select::make('source_id')
                                            ->label('Booking Source')
                                            ->relationship('source', 'source')
                                            ->required(),

                                        TextInput::make('renter_name')
                                            ->label('Renter Name')
                                            ->helperText('Click the icon for existing renter.')
                                            ->required()
                                            ->reactive()
                                            ->readOnly(fn (callable $get) => $get('using_existing_customer')) // lock if existing renter
                                            ->suffixActions([
                                                \Filament\Forms\Components\Actions\Action::make('selectRenter')
                                                    ->label('Select Renter')
                                                    ->icon('heroicon-m-user')
                                                    ->modalHeading('Search Renter')
                                                    ->modalWidth('md')
                                                    ->modalButton('Use Renter')
                                                    ->disabled(! auth()->user()->hasActiveSubscription())
                                                    ->form([
                                                        Select::make('renter_id')
                                                            ->label('Select Existing Renter')
                                                            ->searchable()
                                                            ->options(Customer::where('company_id', Filament::getTenant()?->id)
                                                                ->pluck('customer_name', 'id'))
                                                            ->required(),
                                                    ])
                                                    ->action(function (array $data, callable $set) {
                                                        $customer = Customer::find($data['renter_id']);

                                                        $set('renter_name', $customer->customer_name);
                                                        $set('contact_number', $customer->contact_number);
                                                        $set('renter_address', $customer->address);
                                                        $set('customer_id', $customer->id);

                                                        // mark as using existing renter
                                                        $set('using_existing_customer', true);
                                                    }),

                                                // Reset renter
                                                \Filament\Forms\Components\Actions\Action::make('resetRenter')
                                                    ->label('Reset Renter')
                                                    ->icon('heroicon-m-x-circle')
                                                    ->disabled(! auth()->user()->hasActiveSubscription())
                                                    ->visible(fn (callable $get) => $get('using_existing_customer')) 
                                                    ->action(function (callable $set) {
                                                        $set('renter_name', null);
                                                        $set('contact_number', null);
                                                        $set('renter_address', null);
                                                        $set('customer_id', null);
                                                        $set('using_existing_customer', false);
                                                    }),
                                            ]),

                                        Hidden::make('customer_id'),

                                        Hidden::make('using_existing_customer')
                                            ->default(false) // for create
                                            ->afterStateHydrated(function ($set, $record) {
                                                $set('using_existing_customer', filled($record?->customer_id));
                                            }),
                                        
                                        TextInput::make('contact_number')
                                            ->label('Contact Number')
                                            ->required()
                                            ->tel(),

                                        
                                ]),

                                TextInput::make('renter_address')
                                    ->label('Address')
                                    ->columnSpanFull()
                                    ->placeholder('Unit 5A, 3rd Floor, ABC Tower, 123 Main St, Makati City, Metro Manila')
                                    ->required(),

                                Checkbox::make('with_driver')
                                    ->label('Include a Driver for this Booking')
                                    ->columnSpanFull(),

                                TextInput::make('other_drivers')
                                    ->label('Other Drivers')
                                    ->placeholder('Optional: If driver is different from the renter')
                                    ->columnSpanFull(),

                                Grid::make()
                                    ->columns([
                                        'sm' => 2,
                                        'lg' => 3,
                                        'xl' => 3
                                    ])
                                    ->schema([
                                        TextInput::make('destination')
                                            ->label('Destination'),

                                        TextInput::make('delivery_address')
                                            ->label('Delivery Address')
                                            // ->suffixIcon('heroicon-m-map-pin')
                                            ->placeholder('Enter where the car will be delivered'),

                                        TextInput::make('return_address')
                                            ->label('Return Address')
                                            // ->suffixIcon('heroicon-m-map-pin')
                                            ->placeholder('Enter where the car will be picked up after the rental'),
                                    ]),

                                Textarea::make('remarks')
                                    ->label('Remarks')
                                    ->columnSpanFull()
                                    ->maxLength(100)
                                    ->reactive()
                            ]),
                        
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
                                        ->default(0)
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
                                        ->default(0)
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
                                    ->label('Additional Charges - Optional charges based on usage or condition')
                                    ->schema([
                                        Grid::make()
                                            ->columns([
                                                'sm' => 2,
                                                'lg' => 3,
                                                'xl' => 4,
                                            ])
                                            ->schema([
                                                TextInput::make('fuel_charge')
                                                    ->label(new HtmlString(
                                                        '<span class="inline-flex items-center gap-1" title="Additional charge for fuel usage">
                                                            Fuel Charge
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                                            </svg>
                                                        </span>'
                                                    ))
                                                    ->numeric()
                                                    ->default(0)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                                TextInput::make('out_of_bounds')
                                                    ->label(new HtmlString(
                                                        '<span class="inline-flex items-center gap-1" title="Additional charge for trips outside the declared service area">
                                                            Out-of-Bounds Charge
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                                            </svg>
                                                        </span>'
                                                    ))
                                                    ->numeric()
                                                    ->default(0)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                                TextInput::make('rfid')
                                                    ->label(new HtmlString(
                                                        '<span class="inline-flex items-center gap-1" title="Existing RFID loads used on trip">
                                                            RFID Charge
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                                            </svg>
                                                        </span>'
                                                    ))
                                                    ->numeric()
                                                    ->default(0)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                                TextInput::make('damages')
                                                    ->label(new HtmlString(
                                                        '<span class="inline-flex items-center gap-1" title="Charges for any damage to the vehicle.">
                                                            Damage Fees
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                                            </svg>
                                                        </span>'
                                                    ))
                                                    ->numeric()
                                                    ->default(0)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                                TextInput::make('carwash_fee')
                                                    ->label(new HtmlString(
                                                        '<span class="inline-flex items-center gap-1" title="Charge for cleaning the vehicle after use.">
                                                            Car Wash Fees
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                                            </svg>
                                                        </span>'
                                                    ))
                                                    ->numeric()
                                                    ->default(0)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(fn ($set, $get) => calculateTotalBookingDue($set, $get)),

                                                TextInput::make('insurance')
                                                    ->label(new HtmlString(
                                                        '<span class="inline-flex items-center gap-1" title="Charge for vehicle insurance fee.">
                                                            Insurance Fee
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" >
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                                            </svg>
                                                        </span>'
                                                    ))
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

                                    TextInput::make('company_earnings')
                                        ->label('Company Commission')
                                        ->default(0)
                                        ->extraInputAttributes([
                                            'class' => 'text-warning-800 font-bold'
                                        ])
                                        ->visible(function ($get) {
                                            $carId = $get('car_id');
                                            $car = Car::find($carId);
                                            return $car && $car->partner_id !== null;
                                        }),
                                ]),
                            ]),
                        ];
                    })
                    ->submitAction(null)
                    ->columnSpanFull()
                    ->disabled($disabled),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'quotation');
    }

    public static function table(Table $table): Table
    {   
        $importAction = Action::make('importFromExcel')
            ->label('Import')
            ->button() 
            ->color('gray') 
            ->outlined()
            ->modalWidth('md')
            ->icon('heroicon-s-arrow-down-tray')
            ->form([
                Forms\Components\FileUpload::make('file')
                    ->label('Excel File')
                    ->helperText('Note: Use dd/mm/YYYY HH:mm format (e.g. 26/08/2023 13:00).')
                    ->required()
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/csv',
                    ])
                    ->storeFiles(false),
            ])
            ->action(function (array $data): void {
                $import = new BookingsImport;
                Excel::import($import, $data['file']);

                if ($import->failures()->isNotEmpty()) {
                    foreach ($import->failures() as $failure) {
                        $row = $failure->row()+1;
                        Notification::make()
                            ->title("Row {$row} Error")
                            ->body(implode(', ', $failure->errors()))
                            ->danger()
                            ->duration(2500)
                            ->send();
                    }
                    return;
                }
                
                Notification::make()
                    ->title('Import Complete')
                    ->body('Bookings have been imported successfully.')
                    ->success()
                    ->send();
            });
            
        $actions = (auth()->user()->hasActiveSubscription()) ? 
            [
                CreateAction::make()
                    ->label('Add New'),
                $importAction,
                Action::make('downloadTemplate')
                    ->label('Download Template')
                    ->button() 
                    ->color('gray') 
                    ->outlined()
                    ->tooltip('Download a blank template for importing bookings')
                    ->icon('heroicon-s-document-arrow-down')
                    ->action(fn () => Excel::download(new BookingTemplateExport, 'booking-template.xlsx')),
                Action::make('exportToExcel')
                    ->label('Export')
                    ->button()
                    ->color('gray')
                    ->outlined()
                    ->icon('heroicon-s-arrow-up-tray')
                    ->action(function ($livewire): BinaryFileResponse {
                        // Get the currently filtered query from the table
                        $query = $livewire->getFilteredTableQuery();

                        // Pass it to your export class
                        return Excel::download(new BookingExport($query), 'bookings.xlsx');
                    }),
            ] : [
                Action::make('exportToExcel')
                    ->label('Export')
                    ->button()
                    ->color('gray')
                    ->outlined()
                    ->icon('heroicon-s-arrow-up-tray')
                    ->action(function ($livewire): BinaryFileResponse {
                        // Get the currently filtered query from the table
                        $query = $livewire->getFilteredTableQuery();

                        // Pass it to your export class
                        return Excel::download(new BookingExport($query), 'bookings.xlsx');
                    }),
            ];

        return $table
            ->paginated([10, 25, 50, 100])
            ->defaultSort('start_datetime', 'asc') // sort upcoming first
            // ->headerActions($actions)
            // ->actionsPosition(ActionsPosition::BeforeColumns)
            ->columns([
                ImageColumn::make('car.image')
                    ->label('Rented Car')
                    ->toggleable(),
                TextColumn::make('car.name')
                    ->label('')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->car?->trashed()) {
                            return "{$state} (Deleted)";
                        }
                        return $state;
                    })
                    ->color(function ($record) {
                        return $record->car?->trashed() ? 'danger' : 'primary';
                    }),
                TextColumn::make('booking_period')
                    ->label('Booking Period')
                    ->html()
                    ->grow(false)
                    ->extraAttributes(['class' => 'w-64 truncate'])
                    ->getStateUsing(function ($record) {
                        $start = $record->start_datetime
                            ? Carbon::parse($record->start_datetime)->format('M d, Y h:i A')
                            : 'N/A';

                        $end = $record->end_datetime
                            ? Carbon::parse($record->end_datetime)->format('M d, Y h:i A')
                            : 'N/A';

                        return "
                            <div style='display: table; width: 100%;'>
                                <div style='display: table-row;'>
                                    <div style='display: table-cell; color: gray; width: 50px;'>Pickup: </div>
                                    <div style='display: table-cell;padding-left:5px'> {$start}</div>
                                </div>
                                <div style='display: table-row;'>
                                    <div style='display: table-cell; color: gray;'>Return: </div>
                                    <div style='display: table-cell;padding-left:5px'> {$end}</div>
                                </div>
                            </div>
                        ";
                    }),
                TextColumn::make('hours')
                    ->label('Days/Hours')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if (!$record->start_datetime || !$record->end_datetime) {
                            return 'N/A';
                        }
                        return (Carbon::parse($record->start_datetime)->diffInHours(Carbon::parse($record->end_datetime)) > 24
                            ? round(Carbon::parse($record->start_datetime)->diffInDays(Carbon::parse($record->end_datetime))) . ' days'
                            : Carbon::parse($record->start_datetime)->diffInHours(Carbon::parse($record->end_datetime)) . ' hours');
                    }),
                TextColumn::make('renter_name')
                    ->label('Renter Name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('total_due')
                    ->money('PHP')
                    ->label('Total Due')
                    ->money('PHP')
                    ->searchable()
                    ->color('green')
                    ->toggleable()
                    ->extraAttributes(fn ($record) => $record->total_due > 0
                        ? ['class' => 'font-bold']
                        : [])
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Date Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(),

            ])
            ->filters([
                SelectFilter::make('partner_filter')
                    ->label('Ownership')
                    ->options(function () {
                        $partners = Partners::orderBy('name')->pluck('name', 'id')->toArray();
                        return [
                            // '' => 'All',
                            'company_owned' => 'Company Owned',
                        ] + $partners;
                    })
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;

                        return match ($value) {
                            'company_owned' => $query->whereHas('car', fn ($q) => $q->whereNull('partner_id')),
                            '' => $query,
                            null => $query,
                            default => $query->whereHas('car', fn ($q) => $q->where('partner_id', $value)),
                        };
                    }),
                
                SelectFilter::make('car.name')
                    ->relationship('car', 'name'),

                Filter::make('start_datetime')
                    ->form([
                        Grid::make(2)->schema([ // 2-column grid
                            DatePicker::make('created_from')
                                ->native(true)  
                                ->label('From'),
                            DatePicker::make('created_until')
                                ->native(true)  
                                ->label('To'),
                        ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_datetime', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_datetime', '<=', $date),
                            );
                    }),
               
                    
            ])
            ->filtersLayout(FiltersLayout::Modal)
            ->filtersFormWidth('md')
            ->actions(
                auth()->user()->hasActiveSubscription()
                    ? [
                        Tables\Actions\ActionGroup::make([
                            Tables\Actions\Action::make('convertToBooking')
                                ->label('Convert to Booking')
                                ->icon('heroicon-o-check-circle')
                                ->color('success')
                                ->requiresConfirmation()
                                ->modalHeading('Convert Quotation to Booking')
                                ->modalSubheading('Are you sure you want to convert this quotation into an approved booking?')
                                ->modalButton('Yes, Convert')
                                ->action(function ($record, array $data = null) {

                                    $carId = $record->car_id;
                                    $start = $record->start_datetime;
                                    $end = $record->end_datetime;

                                    $isAvailable = Car::isAvailableAt($carId, $start, $end, $record->id);

                                    if (! $isAvailable) {
                                        Notification::make()
                                            ->title('Cannot convert')
                                            ->body('The selected car is not available for the chosen dates.')
                                            ->danger()
                                            ->send();

                                        return; 
                                    }
                                    // Convert quotation to booking
                                    $record->update([
                                        'status' => 'approved',
                                    ]);

                                    Notification::make()
                                        ->title('Quotation converted to Booking successfully!')
                                        ->success()
                                        ->send();
                                }),
                            Tables\Actions\EditAction::make()->color('gray'),
                            Tables\Actions\DeleteAction::make()->color('gray'),
                        ])
                            // ->label('Actions')
                            ->icon('heroicon-o-ellipsis-horizontal-circle')
                            ->size(ActionSize::ExtraLarge)
                    ]
                    : []
            )
            ->bulkActions(
                (auth()->user()->hasActiveSubscription()) ? [
                    // Tables\Actions\BulkActionGroup::make([
                        // Tables\Actions\DeleteBulkAction::make(),
                    // ]),
                ] : []
            );

    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
        ];
    }
}
