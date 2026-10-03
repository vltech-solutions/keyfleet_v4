<?php

namespace App\Filament\Resources;

use App\Exports\BookingExport;
use App\Exports\BookingTemplateExport;
use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\Pages\ViewBooking;
use App\Imports\BookingsImport;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Customer;
use App\Models\FundType;
use App\Models\Partners;
use App\Models\Reservation;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookingResource extends TenantResource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?int $navigationSort = 1;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationGroup(): ?string
    {
        return 'Transactions';
    }

    public static function getNavigationGroupSort(): ?int
    {
        return 1;
    }

    /*
    |--------------------------------------------------------------------------
    | FORM
    |--------------------------------------------------------------------------
    */

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

            $company = auth()->user()->company;
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
                                                modifyQueryUsing: fn ($query) => $query->whereNull('deleted_at')->where('is_available',true)
                                            )
                                            ->required()
                                            ->live()
                                            // This allows rendering HTML in the dropdown list
                                            ->allowHtml() 
                                            // This allows rendering HTML for the currently selected item
                                            ->getOptionLabelFromRecordUsing(fn (Car $record) => "
                                                <div class='flex flex-col sm:flex-row items-start sm:items-center gap-4 py-3 px-1'>
                                                    <div class='flex-shrink-0'>
                                                        <img src='" . asset('storage/' . $record->image) . "' 
                                                            class='w-16 h-12 sm:w-12 sm:h-12 rounded-lg object-contain shadow-sm ' />
                                                    </div>

                                                    <div class='flex flex-col flex-1 min-w-0'>
                                                        <div class='flex items-baseline justify-between sm:justify-start gap-2'>
                                                            <span class='text-sm font-bold text-gray-900 dark:text-white truncate'>
                                                                {$record->name}
                                                            </span>
                                                            <span class='sm:hidden text-[10px] px-2 py-0.5 rounded-full bg-primary-50 text-primary-600 font-medium'>
                                                                " . ($record->partner?->name ?? 'Company') . "
                                                            </span>
                                                        </div>
                                                        
                                                        <span class='text-xs text-gray-500 truncate'>
                                                            {$record->plate_number} • {$record->transmission}
                                                        </span>
                                                        
                                                        <span class='text-[11px] text-gray-400 italic sm:not-italic sm:text-gray-500'>
                                                            {$record->brand} {$record->model} ({$record->year})
                                                        </span>
                                                    </div>

                                                    <div class='hidden sm:block ml-auto text-right pr-2  pl-4'>
                                                        <span class='text-[10px] text-gray-400 uppercase tracking-tighter block leading-none mb-1'>Owner</span>
                                                        <span class='text-xs font-semibold text-primary-600 whitespace-nowrap'>
                                                            " . ($record->partner?->name ?? 'Company Owned') . "
                                                        </span>
                                                    </div>
                                                </div>
                                            ")
                                            ->afterStateUpdated(function ($state, $set) {
                                                // Fetch the car record to pre-fill the daily rate automatically
                                                $car = \App\Models\Car::find($state);
                                                
                                                $set('daily_rate', $car ? $car->price_starts_at : 0);

                                                // Reset your fields
                                                $set('start_datetime', null);
                                                $set('end_datetime', null);
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
                                            })
                                            ->columnSpanFull()
                                            ->searchable()
                                            ->preload(),
                                        
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
                                            ->required()
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
                                                    if (! Car::isAvailableAt($carId, $state, null, $bookingId)) {
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
                                            ->required()
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
                                            ->placeholder('Enter where the car will be delivered')
                                            ->live()
                                            // ->suffixAction(
                                            //     \Filament\Forms\Components\Actions\Action::make('view_delivery_map')
                                            //         ->icon('heroicon-m-map-pin')
                                            //         ->color('info')
                                            //         ->tooltip('View on Map')
                                            //         ->disabled(fn ($get) => blank($get('delivery_address')))
                                            //         ->modalHeading('Delivery Location Preview')
                                            //         ->modalSubmitAction(false) // Removes the save button
                                            //         ->modalContent(fn ($get) => view('filament.forms.components.google-map-modal', [
                                            //             'address' => $get('delivery_address'),
                                            //         ]))
                                            // )
                                            ,

                                        TextInput::make('return_address')
                                            ->label('Return Address')
                                            ->placeholder('Enter where the car will be picked up')
                                            ->live()
                                            // ->suffixAction(
                                            //     \Filament\Forms\Components\Actions\Action::make('view_return_map')
                                            //         ->icon('heroicon-m-map-pin')
                                            //         ->color('info')
                                            //         ->tooltip('View on Map')
                                            //         ->disabled(fn ($get) => blank($get('return_address')))
                                            //         ->modalHeading('Return Location Preview')
                                            //         ->modalSubmitAction(false)
                                            //         ->modalContent(fn ($get) => view('filament.forms.components.google-map-modal', [
                                            //             'address' => $get('return_address'),
                                            //         ]))
                                            // )
                                            ,
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
                        
                        ...$contractPreviewStep($get),
                            
                        ];
                    })
                        ->submitAction(null)
                        ->columnSpanFull()
                        ->disabled($disabled),
                TableRepeater::make('payments')
                    ->label('Booking Payments')
                    ->relationship()
                    ->live(onBlur: true)
                    ->defaultItems(0)
                    ->addActionLabel('Add Payment')
                    ->schema([
                        Select::make('fund_type_id')
                            ->label('Fund')
                            ->relationship('fundType', 'name')
                            ->required(),

                        TextInput::make('amount')
                            ->numeric()
                            ->required(),

                        TextInput::make('payment_notes')
                            ->label('Payment Note'),

                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->required(),
                    ])
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        $total = collect($get('payments'))
                            ->pluck('amount')
                            ->filter()
                            ->sum();

                        $set('paid_amount', $total);

                        $totalDue = $get('total_due') ?? 0;
                        $set('balance', floatval($totalDue) - floatval($total));
                    })
                ->visible(fn () => Filament::getTenant()?->advance_booking_form)
                ->collapsible()
                ->disabled($disabled),
            ]);
    }

    protected static function rentalDetailsStep(): Step
    {
        return Step::make('Rental Details')
            ->description('Vehicle and rental schedule')
            ->icon('heroicon-m-calendar-days')
            ->schema([
                Section::make('Vehicle')
                    ->description('Choose the vehicle for this booking.')
                    ->schema([
                        Select::make('car_id')
                            ->label('Vehicle')
                            ->relationship(
                                name: 'car',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query
                                    ->whereNull('deleted_at')
                                    ->where('is_available', true)
                            )
                            ->getOptionLabelFromRecordUsing(function (Car $record) {
                                $owner = $record->partner?->name ?? 'Company Owned';

                                return collect([
                                    $record->name,
                                    trim(($record->brand ?? '') . ' ' . ($record->model ?? '')),
                                    $record->plate_number,
                                    $owner,
                                ])->filter()->implode(' · ');
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $car = Car::find($state);

                                $set('daily_rate', $car?->price_starts_at ?? 0);
                                $set('start_datetime', null);
                                $set('end_datetime', null);
                                $set('days_rented', 0);
                                $set('extend_hours', 0);
                                $set('extend_due', 0);
                                $set('delivery_fee', 0);
                                $set('discount', 0);
                                $set('driver_fee', 0);
                                $set('security_deposit', 0);
                                $set('fuel_charge', 0);
                                $set('out_of_bounds', 0);
                                $set('rfid', 0);
                                $set('damages', 0);
                                $set('carwash_fee', 0);
                                $set('insurance', 0);
                                $set('total_rent_due', 0);
                                $set('total_due', 0);
                                $set('paid_amount', 0);
                                $set('balance', 0);
                                $set('company_earnings', 0);
                            }),

                        Hidden::make('id')
                            ->dehydrated(false),
                    ]),

                Section::make('Rental Schedule')
                    ->description('Set pickup and return schedule.')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        DateTimePicker::make('start_datetime')
                            ->label('Pickup Date & Time')
                            ->native(true)
                            ->seconds(false)
                            ->displayFormat('M j, Y h:i A')
                            ->live()
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $carId = $get('car_id');

                                if (! $carId) {
                                    $set('start_datetime', null);
                                    self::notifyError('Please select a vehicle first.');
                                    return;
                                }

                                if (
                                    $state &&
                                    ! Car::isAvailableAt(
                                        $carId,
                                        $state,
                                        $get('id')
                                    )
                                ) {
                                    $set('start_datetime', null);
                                    self::notifyError(
                                        'Vehicle is not available at the selected pickup date/time.'
                                    );
                                    return;
                                }

                                self::updateRentalDuration($set, $get);
                            }),

                        DateTimePicker::make('end_datetime')
                            ->label('Return Date & Time')
                            ->native(true)
                            ->seconds(false)
                            ->displayFormat('M j, Y h:i A')
                            ->minDate(fn ($get) => $get('start_datetime'))
                            ->live()
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $carId = $get('car_id');
                                $start = $get('start_datetime');

                                if (! $carId) {
                                    $set('end_datetime', null);
                                    self::notifyError('Please select a vehicle first.');
                                    return;
                                }

                                if (
                                    $start &&
                                    $state &&
                                    Carbon::parse($state)->lessThan(Carbon::parse($start))
                                ) {
                                    $set('end_datetime', null);
                                    self::notifyError(
                                        'Return date/time cannot be earlier than pickup.'
                                    );
                                    return;
                                }

                                if (
                                    $state &&
                                    $start &&
                                    ! Car::isAvailableAt(
                                        $carId,
                                        $start,
                                        $state,
                                        $get('id')
                                    )
                                ) {
                                    $set('start_datetime', null);
                                    $set('end_datetime', null);
                                    $set('days_rented', 0);
                                    $set('extend_hours', 0);

                                    self::notifyError(
                                        'Vehicle is not available for the selected schedule.'
                                    );

                                    return;
                                }

                                self::updateRentalDuration($set, $get);
                                calculateTotalBookingDue($set, $get);
                            }),

                        Select::make('source_id')
                            ->label('Booking Source')
                            ->relationship('source', 'source')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected static function customerAndTripStep(): Step
    {
        return Step::make('Customer & Trip')
            ->description('Renter and trip information')
            ->icon('heroicon-m-user')
            ->schema([
                Section::make('Customer Information')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        TextInput::make('renter_name')
                            ->label('Renter Name')
                            ->required()
                            ->readOnly(fn ($get) => $get('using_existing_customer'))
                            ->suffixActions([
                                Forms\Components\Actions\Action::make('selectRenter')
                                    ->label('Existing Customer')
                                    ->icon('heroicon-m-user')
                                    ->modalHeading('Select Existing Customer')
                                    ->modalWidth('md')
                                    ->modalButton('Use Customer')
                                    ->disabled(! auth()->user()->hasActiveSubscription())
                                    ->form([
                                        Select::make('renter_id')
                                            ->label('Customer')
                                            ->options(
                                                Customer::query()
                                                    ->where(
                                                        'company_id',
                                                        Filament::getTenant()?->id
                                                    )
                                                    ->orderBy('customer_name')
                                                    ->pluck('customer_name', 'id')
                                            )
                                            ->searchable()
                                            ->required(),
                                    ])
                                    ->action(function (array $data, callable $set) {
                                        $customer = Customer::find($data['renter_id']);

                                        if (! $customer) {
                                            return;
                                        }

                                        $set('renter_name', $customer->customer_name);
                                        $set('contact_number', $customer->contact_number);
                                        $set('renter_address', $customer->address);
                                        $set('customer_id', $customer->id);
                                        $set('using_existing_customer', true);
                                    }),

                                Forms\Components\Actions\Action::make('resetRenter')
                                    ->label('Clear Customer')
                                    ->icon('heroicon-m-x-circle')
                                    ->visible(fn ($get) => $get('using_existing_customer'))
                                    ->action(function (callable $set) {
                                        $set('renter_name', null);
                                        $set('contact_number', null);
                                        $set('renter_address', null);
                                        $set('customer_id', null);
                                        $set('using_existing_customer', false);
                                    }),
                            ]),

                        TextInput::make('contact_number')
                            ->label('Contact Number')
                            ->tel()
                            ->required(),

                        TextInput::make('renter_address')
                            ->label('Address')
                            ->placeholder('Customer address')
                            ->columnSpanFull()
                            ->required(),

                        Hidden::make('customer_id'),

                        Hidden::make('using_existing_customer')
                            ->default(false)
                            ->afterStateHydrated(
                                fn ($set, $record) => $set(
                                    'using_existing_customer',
                                    filled($record?->customer_id)
                                )
                            ),
                    ]),

                Section::make('Trip Details')
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->schema([
                        Checkbox::make('with_driver')
                            ->label('Include a driver'),

                        TextInput::make('other_drivers')
                            ->label('Other Driver')
                            ->placeholder('Optional driver name'),

                        TextInput::make('destination')
                            ->label('Destination'),

                        TextInput::make('delivery_address')
                            ->label('Delivery Address')
                            ->placeholder('Where the vehicle will be delivered'),

                        TextInput::make('return_address')
                            ->label('Return Address')
                            ->placeholder('Where the vehicle will be returned'),

                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(3)
                            ->maxLength(100),

                    ]),
            ]);
    }

    protected static function pricingStep(): Step
    {
        return Step::make('Pricing')
            ->description('Rental rate and charges')
            ->icon('heroicon-m-banknotes')
            ->schema([
                Section::make('Core Pricing')
                    ->description('Primary charges used for this rental.')
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        TextInput::make('days_rented')
                            ->label('Rental Days')
                            ->default(0)
                            ->readOnly(),

                        TextInput::make('extend_hours')
                            ->label('Extension Hours')
                            ->default(0)
                            ->readOnly(),

                        self::moneyField('daily_rate', 'Daily Rate', true),
                        self::moneyField('extend_due', 'Extension Fee', true),
                        self::moneyField('delivery_fee', 'Delivery Fee', true),
                        self::moneyField('driver_fee', 'Driver Fee', true),
                        self::moneyField('security_deposit', 'Security Deposit', true),
                        self::moneyField('discount', 'Discount', true),
                    ]),

                Section::make('Additional Charges')
                    ->description('Optional charges based on actual usage or vehicle condition.')
                    ->icon('heroicon-o-plus-circle')
                    ->collapsible()
                    ->collapsed()
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'xl' => 3,
                    ])
                    ->schema([
                        self::moneyField('fuel_charge', 'Fuel Charge'),
                        self::moneyField('out_of_bounds', 'Out-of-Bounds Charge'),
                        self::moneyField('rfid', 'RFID Charge'),
                        self::moneyField('damages', 'Damage Fees'),
                        self::moneyField('carwash_fee', 'Car Wash Fee'),
                        self::moneyField('insurance', 'Insurance Fee'),
                    ]),
            ]);
    }

    protected static function billingStep(): Step
    {
        return Step::make('Billing Summary')
            ->description('Review totals before saving')
            ->icon('heroicon-m-calculator')
            ->schema([
                Section::make('Booking Total')
                    ->description('Computed financial summary for this booking.')
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'xl' => 4,
                    ])
                    ->schema([
                        TextInput::make('total_rent_due')
                            ->label('Total Rent')
                            ->prefix('₱')
                            ->default(0)
                            ->readOnly(),

                        TextInput::make('total_due')
                            ->label('Grand Total')
                            ->prefix('₱')
                            ->default(0)
                            ->readOnly()
                            ->extraInputAttributes([
                                'class' => 'font-bold',
                            ]),

                        TextInput::make('paid_amount')
                            ->label('Amount Paid')
                            ->prefix('₱')
                            ->numeric()
                            ->default(0)
                            ->readOnly(),

                        TextInput::make('balance')
                            ->label('Balance')
                            ->prefix('₱')
                            ->default(0)
                            ->readOnly()
                            ->extraInputAttributes([
                                'class' => 'font-bold',
                            ]),

                        TextInput::make('company_earnings')
                            ->label('Company Commission')
                            ->prefix('₱')
                            ->default(0)
                            ->visible(function ($get) {
                                $car = Car::find($get('car_id'));

                                return $car?->partner_id !== null;
                            }),
                    ]),
            ]);
    }

    protected static function moneyField(
        string $name,
        string $label,
        bool $required = false
    ): TextInput {
        return TextInput::make($name)
            ->label($label)
            ->prefix('₱')
            ->numeric()
            ->minValue(0)
            ->default(0)
            ->required($required)
            ->live(onBlur: true)
            ->afterStateUpdated(
                fn ($set, $get) => calculateTotalBookingDue($set, $get)
            );
    }

    protected static function updateRentalDuration(
        callable $set,
        callable $get
    ): void {
        $start = $get('start_datetime');
        $end = $get('end_datetime');

        if (! $start || ! $end) {
            return;
        }

        $hours = Carbon::parse($start)
            ->diffInHours(Carbon::parse($end));

        if ($hours < 24) {
            $set('days_rented', 1);
            $set('extend_hours', 0);
            return;
        }

        $set('days_rented', floor($hours / 24));
        $set('extend_hours', $hours % 24);
    }

    protected static function notifyError(string $message): void
    {
        Notification::make()
            ->title($message)
            ->danger()
            ->send();
    }

    protected static function contractPreviewStep(callable $get): array
    {
        if (! $get('id')) {
            return [];
        }

        $company = auth()->user()->company;

        if (! $company?->contract) {
            return [];
        }

        return [
            Step::make('Contract')
                ->description('Preview generated rental contract')
                ->icon('heroicon-m-document-text')
                ->schema([
                    View::make('filament.components.booking-contract-preview')
                        ->viewData([
                            'previewUrl' => route(
                                'contract.preview',
                                ['booking' => $get('id')]
                            ),
                        ]),
                ]),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    protected static function getTableQuery(): Builder
    {
        return Booking::query()
            ->where('status', '!=', 'quotation')
            ->with(['car.partner']);
    }

    public static function table(Table $table): Table
    {
        $headerActions = self::tableHeaderActions();
        $rowActions = self::tableRowActions();

        return $table
            ->query(static::getTableQuery())
            ->defaultSort('start_datetime', 'asc')
            ->paginated([10, 25, 50, 100])
            ->headerActions($headerActions)
            ->recordUrl(
                fn ($record) => ViewBooking::getUrl([
                    'record' => $record->id,
                ])
            )
            ->columns([
                /*
                |--------------------------------------------------------------------------
                | Mobile
                |--------------------------------------------------------------------------
                */

                ViewColumn::make('mobile_booking')
                    ->label('')
                    ->view('filament.tables.columns.booking-mobile-card')
                    ->hiddenFrom('md')
                    ->grow()
                    ->extraAttributes([
                        'class' => '!w-full !max-w-none !whitespace-normal !p-0',
                    ]),

                /*
                |--------------------------------------------------------------------------
                | Desktop
                |--------------------------------------------------------------------------
                */

                ImageColumn::make('car.image')
                    ->label('')
                    ->visibleFrom('md'),

                TextColumn::make('car.name')
                    ->label('Vehicle')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(function ($record) {
                        $details = trim(
                            ($record->car?->brand ?? '')
                            . ' '
                            . ($record->car?->model ?? '')
                        );

                        return collect([
                            $details,
                            $record->car?->plate_number,
                        ])
                            ->filter()
                            ->implode(' · ');
                    })
                    ->formatStateUsing(
                        fn ($state, $record) => $record->car?->trashed()
                            ? "{$state} (Deleted)"
                            : ($state ?: 'Vehicle unavailable')
                    )
                    ->color(
                        fn ($record) => $record->car?->trashed()
                            ? 'danger'
                            : null
                    )
                    ->visibleFrom('md'),

                TextColumn::make('renter_name')
                    ->label('Renter')
                    ->searchable()
                    ->weight('medium')
                    ->description(
                        fn ($record) => $record->contact_number ?: null
                    )
                    ->visibleFrom('md'),

                /*
                |--------------------------------------------------------------------------
                | Rental Period + Duration
                |--------------------------------------------------------------------------
                */

                TextColumn::make('booking_period')
                    ->label('Rental Period')
                    ->html()
                    ->getStateUsing(function ($record) {
                        if (
                            ! $record->start_datetime ||
                            ! $record->end_datetime
                        ) {
                            return "
                                <div class='text-sm text-gray-400'>
                                    Schedule unavailable
                                </div>
                            ";
                        }

                        $start = Carbon::parse($record->start_datetime);
                        $end = Carbon::parse($record->end_datetime);

                        $startLabel = $start->format('M d, Y · h:i A');
                        $endLabel = $end->format('M d, Y · h:i A');

                        $totalHours = (int) floor(
                            $start->diffInMinutes($end) / 60
                        );

                        if ($totalHours < 24) {
                            $duration = $totalHours . ' '
                                . ($totalHours === 1 ? 'hour' : 'hours');
                        } else {
                            $days = intdiv($totalHours, 24);
                            $hours = $totalHours % 24;

                            $duration = $days . ' '
                                . ($days === 1 ? 'day' : 'days');

                            if ($hours > 0) {
                                $duration .= ' · ' . $hours . ' '
                                    . ($hours === 1 ? 'hour' : 'hours');
                            }
                        }

                        return "
                            <div class='min-w-[210px] space-y-1'>
                                <div class='text-sm font-medium text-gray-800 dark:text-gray-200'>
                                    {$startLabel}
                                </div>

                                <div class='text-xs text-gray-400'>
                                    to {$endLabel}
                                </div>

                                <div class='pt-1'>
                                    <span class='inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-600 dark:bg-white/5 dark:text-gray-300'>
                                        {$duration}
                                    </span>
                                </div>
                            </div>
                        ";
                    })
                    ->visibleFrom('md'),

                /*
                |--------------------------------------------------------------------------
                | Billing
                |--------------------------------------------------------------------------
                */

                TextColumn::make('billing_summary')
                    ->label('Billing')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $total = number_format(
                            (float) ($record->total_due ?? 0),
                            2
                        );

                        $paid = number_format(
                            (float) ($record->paid_amount ?? 0),
                            2
                        );

                        $balanceValue = (float) ($record->balance ?? 0);

                        $balance = number_format(
                            $balanceValue,
                            2
                        );

                        $balanceHtml = $balanceValue > 0
                            ? "
                                <span class='font-semibold text-rose-600 dark:text-rose-400'>
                                    ₱{$balance}
                                </span>
                            "
                            : "
                                <span class='font-semibold text-emerald-600 dark:text-emerald-400'>
                                    Paid
                                </span>
                            ";

                        return "
                            <div class='min-w-[150px] space-y-1.5 text-xs'>
                                <div class='flex items-center justify-between gap-4'>
                                    <span class='text-gray-400'>
                                        Total
                                    </span>

                                    <span class='font-semibold text-gray-800 dark:text-gray-200'>
                                        ₱{$total}
                                    </span>
                                </div>

                                <div class='flex items-center justify-between gap-4'>
                                    <span class='text-gray-400'>
                                        Paid
                                    </span>

                                    <span class='font-semibold text-emerald-600 dark:text-emerald-400'>
                                        ₱{$paid}
                                    </span>
                                </div>

                                <div class='flex items-center justify-between gap-4 border-t border-gray-100 pt-1.5 dark:border-white/5'>
                                    <span class='text-gray-400'>
                                        Balance
                                    </span>

                                    {$balanceHtml}
                                </div>
                            </div>
                        ";
                    })
                    ->visibleFrom('md'),

                /*
                |--------------------------------------------------------------------------
                | Status - AFTER Billing
                |--------------------------------------------------------------------------
                */

                TextColumn::make('booking_state')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(
                        fn ($record) => self::bookingState($record)
                    )
                    ->color(fn ($state) => match ($state) {
                        'Upcoming' => 'info',
                        'On Trip' => 'warning',
                        'Completed' => 'success',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->visibleFrom('md'),

                /*
                |--------------------------------------------------------------------------
                | Audit Columns
                |--------------------------------------------------------------------------
                */

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->description(
                        fn ($record) =>
                            $record->creator &&
                            ! $record->creator->is_active
                                ? 'Inactive'
                                : null
                    )
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),

                TextColumn::make('updater.name')
                    ->label('Last Updated By')
                    ->description(
                        fn ($record) =>
                            $record->updated_at?->format(
                                'M d, Y h:i A'
                            )
                    )
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visibleFrom('md'),
            ])
            ->filters(self::tableFilters())
            ->filtersLayout(FiltersLayout::Modal)
            ->filtersFormWidth('md')
            ->actions(
                auth()->user()->hasActiveSubscription()
                    ? [
                        Tables\Actions\ActionGroup::make($rowActions)
                            ->icon('heroicon-o-ellipsis-horizontal-circle')
                            ->size(ActionSize::ExtraLarge),
                    ]
                    : []
            )
            ->bulkActions([]);
    }

    protected static function bookingState(Booking $booking): string
    {
        if ($booking->status === 'cancelled') {
            return 'Cancelled';
        }

        if (! $booking->start_datetime || ! $booking->end_datetime) {
            return ucfirst($booking->status ?? 'Booking');
        }

        $now = now();
        $start = Carbon::parse($booking->start_datetime);
        $end = Carbon::parse($booking->end_datetime);

        if ($start->gt($now)) {
            return 'Upcoming';
        }

        if ($start->lte($now) && $end->gte($now)) {
            return 'On Trip';
        }

        if ($end->lt($now)) {
            return 'Completed';
        }

        return ucfirst($booking->status ?? 'Booking');
    }

    /*
    |--------------------------------------------------------------------------
    | HEADER ACTIONS
    |--------------------------------------------------------------------------
    */

    protected static function tableHeaderActions(): array
    {
        $export = Action::make('exportToExcel')
            ->visible(
                fn () => auth()->user()?->hasPermission('bookings.export') ?? false
            )
            ->label('Export')
            ->color('gray')
            ->outlined()
            ->icon('heroicon-s-arrow-up-tray')
            ->action(function ($livewire): BinaryFileResponse {
                return Excel::download(
                    new BookingExport($livewire->getFilteredTableQuery()),
                    'bookings.xlsx'
                );
            });

        if (! auth()->user()->hasActiveSubscription()) {
            return [$export];
        }

        return [
            // CreateAction::make()
            //     ->label('Add Booking'),

            // Action::make('importFromExcel')
            //     ->visible(
            //         fn () => auth()->user()?->hasPermission('bookings.import') ?? false
            //     )
            //     ->label('Import')
            //     ->color('gray')
            //     ->outlined()
            //     ->icon('heroicon-s-arrow-down-tray')
            //     ->modalWidth('md')
            //     ->form([
            //         Forms\Components\FileUpload::make('file')
            //             ->label('Excel File')
            //             ->helperText(
            //                 'Use dd/mm/YYYY HH:mm format, e.g. 26/08/2026 13:00.'
            //             )
            //             ->acceptedFileTypes([
            //                 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            //                 'text/csv',
            //             ])
            //             ->storeFiles(false)
            //             ->required(),
            //     ])
            //     ->action(function (array $data): void {
            //         $import = new BookingsImport;

            //         Excel::import($import, $data['file']);

            //         if ($import->failures()->isNotEmpty()) {
            //             foreach ($import->failures() as $failure) {
            //                 Notification::make()
            //                     ->title('Row ' . ($failure->row() + 1) . ' Error')
            //                     ->body(implode(', ', $failure->errors()))
            //                     ->danger()
            //                     ->send();
            //             }

            //             return;
            //         }

            //         Notification::make()
            //             ->title('Import Complete')
            //             ->body('Bookings imported successfully.')
            //             ->success()
            //             ->send();
            //     }),

            // Action::make('downloadTemplate')
            //     ->label('Template')
            //     ->color('gray')
            //     ->outlined()
            //     ->icon('heroicon-s-document-arrow-down')
            //     ->action(
            //         fn () => Excel::download(
            //             new BookingTemplateExport,
            //             'booking-template.xlsx'
            //         )
            //     ),

            // $export,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ROW ACTIONS
    |--------------------------------------------------------------------------
    */

    protected static function tableRowActions(): array
    {
        return [
            Tables\Actions\Action::make('addPayment')
                ->label('Add Payment')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(
                    fn ($record) =>
                        (auth()->user()?->hasPermission('bookings.payments') ?? false)
                        && $record->status !== 'cancelled'
                )
                ->form([
                    Forms\Components\Select::make('fund_type_id')
                        ->label('Fund')
                        ->options(FundType::pluck('name', 'id'))
                        ->required(),

                    Forms\Components\TextInput::make('amount')
                        ->label('Amount')
                        ->prefix('₱')
                        ->numeric()
                        ->required(),

                    Forms\Components\TextInput::make('payment_notes')
                        ->label('Payment Note'),

                    Forms\Components\DatePicker::make('payment_date')
                        ->label('Payment Date')
                        ->default(now())
                        ->required(),
                ])
                ->action(function ($record, array $data) {
                    $record->payments()->create($data);

                    $totalPaid = (float) $record
                        ->payments()
                        ->sum('amount');

                    $record->update([
                        'paid_amount' => $totalPaid,
                        'balance' => (float) $record->total_due - $totalPaid,
                    ]);

                    Notification::make()
                        ->title('Payment added successfully.')
                        ->success()
                        ->send();
                })
                ->modalWidth('md')
                ->modalHeading('Add Booking Payment')
                ->modalButton('Save Payment'),

            Tables\Actions\Action::make('viewBooking')
                ->label('View Booking')
                ->icon('heroicon-o-eye')
                ->url(
                    fn ($record) => ViewBooking::getUrl([
                        'record' => $record->id,
                    ])
                ),

            Tables\Actions\EditAction::make()
                ->label('Edit Booking')
                ->color('gray')
                ->visible(
                    fn ($record) =>
                        (auth()->user()?->hasPermission('bookings.update') ?? false)
                        && $record->status !== 'cancelled'
                ),

            Tables\Actions\Action::make('cancelBooking')
                ->label('Cancel Booking')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancel Booking')
                ->modalSubheading(
                    'Are you sure you want to cancel this booking?'
                )
                ->modalButton('Yes, Cancel')
                ->visible(
                    fn ($record) =>
                        (auth()->user()?->hasPermission('bookings.cancel') ?? false)
                        && $record->status !== 'cancelled'
                        && $record->end_datetime
                        && Carbon::parse($record->end_datetime)->isFuture()
                )
                ->action(function ($record) {
                    $record->update([
                        'status' => 'cancelled',
                    ]);

                    Notification::make()
                        ->title('Booking cancelled successfully.')
                        ->success()
                        ->send();
                }),

            Tables\Actions\DeleteAction::make()
                ->color('gray')
                ->visible(
                    fn ($record) =>
                        (auth()->user()?->hasPermission('bookings.delete') ?? false)
                        && $record->status !== 'cancelled'
                ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    protected static function tableFilters(): array
    {
        return [
            SelectFilter::make('booking_status')
                ->label('Status')
                ->default('upcoming')
                ->options([
                    'upcoming' => 'Upcoming',
                    'ongoing' => 'On Trip',
                    'finished' => 'Completed',
                    'cancelled' => 'Cancelled',
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value'] ?? null;
                    $now = now();

                    return match ($value) {
                        'upcoming' => $query
                            ->where('status', 'approved')
                            ->where('start_datetime', '>', $now),

                        'ongoing' => $query
                            ->where('status', 'approved')
                            ->where('start_datetime', '<=', $now)
                            ->where('end_datetime', '>=', $now),

                        'finished' => $query
                            ->where('status', 'approved')
                            ->where('end_datetime', '<', $now),

                        'cancelled' => $query
                            ->where('status', 'cancelled'),

                        default => $query,
                    };
                }),

            SelectFilter::make('partner_filter')
                ->label('Ownership')
                ->options(function () {
                    return [
                        'company_owned' => 'Company Owned',
                    ] + Partners::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray();
                })
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value'] ?? null;

                    if (! $value) {
                        return $query;
                    }

                    if ($value === 'company_owned') {
                        return $query->whereHas(
                            'car',
                            fn ($car) => $car->whereNull('partner_id')
                        );
                    }

                    return $query->whereHas(
                        'car',
                        fn ($car) => $car->where('partner_id', $value)
                    );
                }),

            SelectFilter::make('car.name')
                ->label('Vehicle')
                ->relationship('car', 'name')
                ->searchable()
                ->preload(),

            Filter::make('rental_period')
                ->label('Rental Period')
                ->form([
                    Grid::make(2)->schema([
                        DatePicker::make('from')
                            ->label('From')
                            ->native(true),

                        DatePicker::make('until')
                            ->label('To')
                            ->native(true),
                    ]),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['from'] ?? null,
                            fn (Builder $query, $date) =>
                                $query->whereDate(
                                    'start_datetime',
                                    '>=',
                                    $date
                                )
                        )
                        ->when(
                            $data['until'] ?? null,
                            fn (Builder $query, $date) =>
                                $query->whereDate(
                                    'start_datetime',
                                    '<=',
                                    $date
                                )
                        );
                }),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RESOURCE
    |--------------------------------------------------------------------------
    */

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
            'view' => Pages\ViewBooking::route('/{record}/view'),
        ];
    }

    public static function getWidgets(): array
    {
        return [];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Reservation::query()
            ->where('status', 'pending')
            ->count();

        return $count > 0 ? (string) $count : null;
    }
}