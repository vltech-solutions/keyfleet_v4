<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Car;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Resources\Resource;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Storage;
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

use App\Filament\Pages\BookingInspectionPage;
use App\Filament\Resources\BookingResource\Pages\ViewBooking;
use Filament\Forms\Components\ViewField;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\F;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Transactions';
    }

    public static function getNavigationGroupSort(): ?int
    {
        return 1;
    }

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    protected static function getTableQuery(): Builder
    {
        return Booking::query()->where('status','!=', 'quotation');
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
        
        $company = filament()->getTenant();
        $tableActions = (auth()->user()->hasActiveSubscription()) 
            ? [
                Tables\Actions\Action::make('addPayment')
                        ->label('Add Payment')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->visible(fn ($record) => $record->status !== 'cancelled')
                        ->form([
                            Forms\Components\Select::make('fund_type_id')
                                ->label('Fund')
                                ->options(FundType::pluck('name', 'id'))
                                ->required(),

                            Forms\Components\TextInput::make('amount')
                                ->label('Amount')
                                ->numeric()
                                ->required(),

                            Forms\Components\TextInput::make('payment_notes')
                                ->label('Payment Note'),

                            Forms\Components\DatePicker::make('payment_date')
                                ->label('Payment Date')
                                ->required()
                                ->default(now()),
                        ])
                        ->action(function ($record, array $data) {
                            // Create a single payment record for this booking
                            $record->payments()->create($data);

                            // Recalculate total paid and balance
                            $totalPaid = $record->payments()->sum('amount');
                            $record->update([
                                'paid_amount' => $totalPaid,
                                'balance' => $record->total_due - $totalPaid,
                            ]);

                            Notification::make()
                                ->title('Payment added successfully!')
                                ->success()
                                ->send();
                        })
                        ->modalWidth('md')
                        ->modalHeading('Add Booking Payment')
                        ->modalButton('Save Payment'),
                    Tables\Actions\Action::make('cancelBooking')
                        ->label('Cancel Booking')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Cancel Booking')
                        ->visible(fn ($record) => 
                            $record->status !== 'cancelled' &&
                            $record->end_datetime > now()
                        )
                        ->modalSubheading('Are you sure you want to cancel this booking?')
                        ->modalButton('Yes, Cancel')
                        ->action(function ($record, array $data = null) {
                            $record->update(['status' => 'cancelled']);

                            Notification::make()
                                ->title('Booking cancelled successfully!')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('viewBooking')
                        ->label('View Booking')
                        ->icon('heroicon-o-eye')
                        ->url(fn ($record) => ViewBooking::getUrl(['record' => $record->id])),
                    Tables\Actions\EditAction::make()->color('gray')
                        ->visible(fn ($record) => $record->status !== 'cancelled'),
                    Tables\Actions\DeleteAction::make()->color('gray')
                        ->visible(fn ($record) => $record->status !== 'cancelled'),
            ] : [];
        
        // Define columns for desktop
        $desktopColumns = [
            ImageColumn::make('car.image')
                ->label('Rented Car')
                ->toggleable()
                ->visibleFrom('md'), // Hide on mobile
            TextColumn::make('car.name')
                ->label('')
                ->badge()
                ->searchable()
                ->visibleFrom('md')
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
                ->visibleFrom('md') // Hide on mobile
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
                ->visibleFrom('md')
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
                ->visibleFrom('md')
                ->toggleable(),
            TextColumn::make('total_due')
                ->money('PHP')
                ->label('Total Due')
                ->searchable()
                ->toggleable()
                ->alignRight()
                ->visibleFrom('md') // Hide on mobile
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->money('PHP')
                        ->label('')
                ]),
            TextColumn::make('paid_amount')
                ->money('PHP')
                ->label('Paid Amount')
                ->searchable()
                ->toggleable()
                ->alignRight()
                ->visibleFrom('md') // Hide on mobile
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->money('PHP')
                        ->label('')
                ]),
            TextColumn::make('balance')
                ->money('PHP')
                ->searchable()
                ->color(fn ($record) => $record->balance > 0 ? 'danger' : 'black')
                ->toggleable()
                ->alignRight()
                ->visibleFrom('md') // Hide on mobile
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->money('PHP')
                        ->label('')
                ])
                ->extraAttributes(fn ($record) => $record->balance > 0
                    ? ['class' => 'font-bold']
                    : []
                ),
        ];
        
        // Mobile columns - stack layout
        $mobileColumns = [
            // Custom column that shows all info in a stack
            TextColumn::make('mobile_view')
                ->label('Booking Details')
                ->html()
                ->visibleFrom('sm')
                ->hiddenFrom('md')
                ->extraAttributes(['class' => 'w-full'])
                ->getStateUsing(function ($record) {
                    $carName = $record->car?->name ?? 'N/A';
                    $renterName = $record->renter_name ?? 'N/A';
                    
                    $start = $record->start_datetime
                        ? Carbon::parse($record->start_datetime)->format('M d, Y h:i A')
                        : 'N/A';
                    $end = $record->end_datetime
                        ? Carbon::parse($record->end_datetime)->format('M d, Y h:i A')
                        : 'N/A';
                    
                    $totalDue = number_format($record->total_due ?? 0, 2);
                    $paidAmount = number_format($record->paid_amount ?? 0, 2);
                    $balance = number_format($record->balance ?? 0, 2);
                    
                    $balanceColor = $record->balance > 0 ? '#ef4444' : '#000000';
                    
                    $hours = (!$record->start_datetime || !$record->end_datetime) 
                        ? 'N/A'
                        : (Carbon::parse($record->start_datetime)->diffInHours(Carbon::parse($record->end_datetime)) > 24
                            ? round(Carbon::parse($record->start_datetime)->diffInDays(Carbon::parse($record->end_datetime))) . ' days'
                            : Carbon::parse($record->start_datetime)->diffInHours(Carbon::parse($record->end_datetime)) . ' hours');
                    
                    $imageUrl = $record->car?->image 
                        ? Storage::url($record->car->image) 
                        : asset('images/placeholder-car.png');
                    
                    // Calculate the balance display before the HTML
                    $balanceDisplay = $balance > 0 ? '₱' . $balance : 'Paid';

                    return "
                        <div class='filament-mobile-stack p-3 pb-2 border-b border-gray-100 dark:border-gray-700'>
                            <div class='flex gap-3 mb-3'>
                                <img src='{$imageUrl}' class='w-20 h-20 object-contain rounded-lg bg-gray-50 dark:bg-gray-900' alt='Car'>
                                <div class='flex-1'>
                                    <div class='text-base font-semibold text-gray-900 dark:text-white'>{$carName}</div>
                                    <div class='text-sm text-gray-500 dark:text-gray-400 mt-0.5'>{$renterName}</div>
                                    <div class='mt-2'>
                                        <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'>{$hours}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class='mb-3 text-sm space-y-2'>
                                <div>
                                    <span class='text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide'>Pickup: </span>
                                    <span class='text-gray-900 dark:text-white font-medium'>{$start}</span>
                                </div>
                                <div>
                                    <span class='text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide'>Return: </span>
                                    <span class='text-gray-900 dark:text-white font-medium'>{$end}</span>
                                </div>
                            </div>
                            
                            <div class='grid grid-cols-3 gap-2 pt-2 text-sm'>
                                <div class='text-center'>
                                    <div class='text-gray-500 dark:text-gray-400 text-xs'>Total Due</div>
                                    <div class='text-gray-900 dark:text-white font-semibold mt-1'>₱{$totalDue}</div>
                                </div>
                                <div class='text-center'>
                                    <div class='text-gray-500 dark:text-gray-400 text-xs'>Paid</div>
                                    <div class='text-emerald-600 dark:text-emerald-400 font-semibold mt-1'>₱{$paidAmount}</div>
                                </div>
                                <div class='text-center'>
                                    <div class='text-gray-500 dark:text-gray-400 text-xs'>Balance</div>
                                    <div class='font-semibold mt-1' style='color: {$balanceColor};'>{$balanceDisplay}</div>
                                </div>
                            </div>
                        </div>
                    ";
                }),
        ];
        
        return $table
            ->query(static::getTableQuery())
            ->paginated([10, 25, 50, 100])
            ->defaultSort('start_datetime', 'asc')
            ->recordUrl(fn ($record) => ViewBooking::getUrl(['record' => $record->id]))
            ->columns([
                // Mobile view (visible only on mobile)
                ...$mobileColumns,
                // Desktop view (visible only on md screens and up)
                ...$desktopColumns,
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
                SelectFilter::make('booking_status')
                    ->label('Status')
                    ->default('upcoming')
                    ->options([
                        'upcoming'  => 'Upcoming',
                        'ongoing'   => 'Ongoing',
                        'finished'  => 'Finished',
                        'cancelled' => 'Cancelled', 
                    ])
                    ->query(function (Builder $query, array $data) {
                        $status = $data['value'] ?? null;
                        $now = now();

                        return match ($status) {
                            'upcoming' => $query->where('start_datetime', '>', $now)->where('status','approved'),
                            'ongoing'  => $query
                                ->where('start_datetime', '<=', $now)
                                ->where('end_datetime', '>=', $now)
                                ->where('status','approved'),
                            'finished' => $query->where('end_datetime', '<', $now)->where('status','approved'),
                            'cancelled'=> $query->where('status', 'cancelled'), 
                            default    => $query,
                        };
                    })
            ])
            ->filtersLayout(FiltersLayout::Modal)
            ->filtersFormWidth('md')
            ->actions(
                auth()->user()->hasActiveSubscription()
                    ? 
                    [
                        Tables\Actions\ActionGroup::make($tableActions)
                            ->icon('heroicon-o-ellipsis-horizontal-circle')
                            ->size(ActionSize::ExtraLarge)
                    ]
                    : []
            )
            ->bulkActions([]);
    }

    // Stack display
    // public static function table(Table $table): Table
    // {   
    //     $importAction = Action::make('importFromExcel')
    //         ->label('Import')
    //         ->button() 
    //         ->color('gray') 
    //         ->outlined()
    //         ->modalWidth('md')
    //         ->icon('heroicon-s-arrow-down-tray')
    //         ->form([
    //             Forms\Components\FileUpload::make('file')
    //                 ->label('Excel File')
    //                 ->helperText('Note: Use dd/mm/YYYY HH:mm format (e.g. 26/08/2023 13:00).')
    //                 ->required()
    //                 ->acceptedFileTypes([
    //                     'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    //                     'text/csv',
    //                 ])
    //                 ->storeFiles(false),
    //         ])
    //         ->action(function (array $data): void {
    //             $import = new BookingsImport;
    //             Excel::import($import, $data['file']);

    //             if ($import->failures()->isNotEmpty()) {
    //                 foreach ($import->failures() as $failure) {
    //                     $row = $failure->row()+1;
    //                     Notification::make()
    //                         ->title("Row {$row} Error")
    //                         ->body(implode(', ', $failure->errors()))
    //                         ->danger()
    //                         ->duration(2500)
    //                         ->send();
    //                 }
    //                 return;
    //             }
                
    //             Notification::make()
    //                 ->title('Import Complete')
    //                 ->body('Bookings have been imported successfully.')
    //                 ->success()
    //                 ->send();
    //         });
            
    //     $actions = (auth()->user()->hasActiveSubscription()) ? 
    //         [
    //             CreateAction::make()
    //                 ->label('Add New'),
    //             $importAction,
    //             Action::make('downloadTemplate')
    //                 ->label('Download Template')
    //                 ->button() 
    //                 ->color('gray') 
    //                 ->outlined()
    //                 ->tooltip('Download a blank template for importing bookings')
    //                 ->icon('heroicon-s-document-arrow-down')
    //                 ->action(fn () => Excel::download(new BookingTemplateExport, 'booking-template.xlsx')),
    //             Action::make('exportToExcel')
    //                 ->label('Export')
    //                 ->button()
    //                 ->color('gray')
    //                 ->outlined()
    //                 ->icon('heroicon-s-arrow-up-tray')
    //                 ->action(function ($livewire): BinaryFileResponse {
    //                     $query = $livewire->getFilteredTableQuery();
    //                     return Excel::download(new BookingExport($query), 'bookings.xlsx');
    //                 }),
    //         ] : [
    //             Action::make('exportToExcel')
    //                 ->label('Export')
    //                 ->button()
    //                 ->color('gray')
    //                 ->outlined()
    //                 ->icon('heroicon-s-arrow-up-tray')
    //                 ->action(function ($livewire): BinaryFileResponse {
    //                     $query = $livewire->getFilteredTableQuery();
    //                     return Excel::download(new BookingExport($query), 'bookings.xlsx');
    //                 }),
    //         ];

    //     $company = filament()->getTenant();
    //     $tableActions = (auth()->user()->hasActiveSubscription()) 
    //         ? [
    //             Tables\Actions\Action::make('addPayment')
    //                     ->label('Add Payment')
    //                     ->icon('heroicon-o-currency-dollar')
    //                     ->color('success')
    //                     ->visible(fn ($record) => $record->status !== 'cancelled')
    //                     ->form([
    //                         Forms\Components\Select::make('fund_type_id')
    //                             ->label('Fund')
    //                             ->options(FundType::pluck('name', 'id'))
    //                             ->required(),
    //                         Forms\Components\TextInput::make('amount')
    //                             ->label('Amount')
    //                             ->numeric()
    //                             ->required(),
    //                         Forms\Components\TextInput::make('payment_notes')
    //                             ->label('Payment Note'),
    //                         Forms\Components\DatePicker::make('payment_date')
    //                             ->label('Payment Date')
    //                             ->required()
    //                             ->default(now()),
    //                     ])
    //                     ->action(function ($record, array $data) {
    //                         $record->payments()->create($data);
    //                         $totalPaid = $record->payments()->sum('amount');
    //                         $record->update([
    //                             'paid_amount' => $totalPaid,
    //                             'balance' => $record->total_due - $totalPaid,
    //                         ]);
    //                         Notification::make()
    //                             ->title('Payment added successfully!')
    //                             ->success()
    //                             ->send();
    //                     })
    //                     ->modalWidth('md')
    //                     ->modalHeading('Add Booking Payment')
    //                     ->modalButton('Save Payment'),
    //             Tables\Actions\Action::make('cancelBooking')
    //                     ->label('Cancel Booking')
    //                     ->icon('heroicon-o-x-circle')
    //                     ->color('danger')
    //                     ->requiresConfirmation()
    //                     ->modalHeading('Cancel Booking')
    //                     ->visible(fn ($record) => 
    //                         $record->status !== 'cancelled' &&
    //                         $record->end_datetime > now()
    //                     )
    //                     ->modalSubheading('Are you sure you want to cancel this booking?')
    //                     ->modalButton('Yes, Cancel')
    //                     ->action(function ($record, array $data = null) {
    //                         $record->update(['status' => 'cancelled']);
    //                         Notification::make()
    //                             ->title('Booking cancelled successfully!')
    //                             ->success()
    //                             ->send();
    //                     }),
    //             Tables\Actions\Action::make('viewBooking')
    //                     ->label('View Booking')
    //                     ->icon('heroicon-o-eye')
    //                     ->url(fn ($record) => ViewBooking::getUrl(['record' => $record->id])),
    //             Tables\Actions\EditAction::make()->color('gray')
    //                     ->visible(fn ($record) => $record->status !== 'cancelled'),
    //             Tables\Actions\DeleteAction::make()->color('gray')
    //                     ->visible(fn ($record) => $record->status !== 'cancelled'),
    //         ] : [];
        
    //     if ($company && ($company->hasNonBasicPaidSubscription() || $company->hasActiveFreeSubscription())) {
    //         $tableActions[] = Tables\Actions\Action::make('preInspection')
    //             ->label(fn ($record) => $record->inspections()->where('type', 'pre')->exists() ? 'View Pre Inspection' : 'Start Pre Inspection')
    //             ->visible(fn ($record) => !($record->end_datetime && \Carbon\Carbon::parse($record->end_datetime)->isPast()))
    //             ->icon('heroicon-o-clipboard-document-check')
    //             ->color('primary')
    //             ->url(function ($record) {
    //                 $pre = $record->inspections()->where('type', 'pre')->first();
    //                 return $pre ? \App\Filament\Pages\ViewInspectionPage::getUrl(['record' => $pre->id]) : \App\Filament\Pages\BookingInspectionPage::getUrl(['bookingId' => $record->id, 'type' => 'pre']);
    //             });

    //         $tableActions[] = Tables\Actions\Action::make('postInspection')
    //             ->label(fn ($record) => $record->inspections()->where('type', 'post')->exists() ? 'View Post Inspection' : 'Start Post Inspection')
    //             ->icon('heroicon-o-clipboard-document-check')
    //             ->color('secondary')
    //             ->url(function ($record) {
    //                 $post = $record->inspections()->where('type', 'post')->first();
    //                 return $post ? \App\Filament\Pages\ViewInspectionPage::getUrl(['record' => $post->id]) : \App\Filament\Pages\BookingInspectionPage::getUrl(['bookingId' => $record->id, 'type' => 'post']);
    //             })
    //             ->visible(fn ($record) => !($record->end_datetime && \Carbon\Carbon::parse($record->end_datetime)->isPast()) && $record->inspections()->where('type', 'pre')->exists());
    //     }

    //     return $table
    //         ->query(static::getTableQuery())
    //         ->paginated([10, 25, 50, 100])
    //         ->defaultSort('start_datetime', 'asc')
    //         ->headerActions($actions)
    //         ->recordUrl(fn ($record) => ViewBooking::getUrl(['record' => $record->id]))
    //         ->contentGrid([
    //             'md' => 2,
    //             'xl' => 3,
    //         ])
    //         ->columns([
    //             Tables\Columns\Layout\Stack::make([
    //                 ImageColumn::make('car.image')
    //                     ->height('180px')
    //                     ->width('100%')
    //                     ->extraImgAttributes(['class' => 'object-cover rounded-t-xl']),

    //                 Tables\Columns\Layout\Stack::make([
    //                     Tables\Columns\Layout\Split::make([
    //                         TextColumn::make('car.name')
    //                             ->weight('bold')
    //                             ->size('lg')
    //                             ->color(fn ($record) => $record->car?->trashed() ? 'danger' : 'primary')
    //                             ->formatStateUsing(fn ($state, $record) => $record->car?->trashed() ? "{$state} (Deleted)" : $state),
                            
    //                         TextColumn::make('hours')
    //                             ->badge()
    //                             ->color('gray')
    //                             ->getStateUsing(function ($record) {
    //                                 if (!$record->start_datetime || !$record->end_datetime) return 'N/A';
    //                                 $diff = Carbon::parse($record->start_datetime)->diffInHours(Carbon::parse($record->end_datetime));
    //                                 return ($diff > 24 ? round($diff / 24) . ' days' : $diff . ' hours');
    //                             }),
    //                     ]),

    //                     TextColumn::make('renter_name')
    //                         ->icon('heroicon-m-user')
    //                         ->size('sm')
    //                         ->color('gray'),

    //                     TextColumn::make('booking_period')
    //                         ->icon('heroicon-m-calendar-days')
    //                         ->size('xs')
    //                         ->getStateUsing(fn ($record) => 
    //                             Carbon::parse($record->start_datetime)->format('M d, H:i') . ' → ' . 
    //                             Carbon::parse($record->end_datetime)->format('M d, H:i')
    //                         ),

    //                     Tables\Columns\Layout\Split::make([
    //                         TextColumn::make('total_due')
    //                             ->money('PHP')
    //                             ->weight('bold')
    //                             ->size('sm'),
    //                         TextColumn::make('balance')
    //                             ->money('PHP')
    //                             ->alignRight()
    //                             ->weight('bold')
    //                             ->color(fn ($record) => $record->balance > 0 ? 'danger' : 'success')
    //                             ->formatStateUsing(fn ($state) => $state > 0 ? "Bal: {$state}" : 'Paid'),
    //                     ])->extraAttributes(['class' => 'bg-gray-50 dark:bg-white/5 p-2 rounded-lg mt-2']),
    //                 ])->space(2)->extraAttributes(['class' => 'p-4']),
    //             ]),
    //         ])
    //         ->filters([
    //             SelectFilter::make('partner_filter')
    //                 ->label('Ownership')
    //                 ->options(function () {
    //                     $partners = Partners::orderBy('name')->pluck('name', 'id')->toArray();
    //                     return ['company_owned' => 'Company Owned'] + $partners;
    //                 })
    //                 ->query(function (Builder $query, array $data) {
    //                     $value = $data['value'] ?? null;
    //                     return match ($value) {
    //                         'company_owned' => $query->whereHas('car', fn ($q) => $q->whereNull('partner_id')),
    //                         '' , null => $query,
    //                         default => $query->whereHas('car', fn ($q) => $q->where('partner_id', $value)),
    //                     };
    //                 }),
    //             SelectFilter::make('car.name')->relationship('car', 'name'),
    //             Filter::make('start_datetime')
    //                 ->form([
    //                     Grid::make(2)->schema([
    //                         DatePicker::make('created_from')->native(true)->label('From'),
    //                         DatePicker::make('created_until')->native(true)->label('To'),
    //                     ]),
    //                 ])
    //                 ->query(function (Builder $query, array $data): Builder {
    //                     return $query
    //                         ->when($data['created_from'], fn(Builder $query, $date) => $query->whereDate('start_datetime', '>=', $date))
    //                         ->when($data['created_until'], fn(Builder $query, $date) => $query->whereDate('start_datetime', '<=', $date));
    //                 }),
    //             SelectFilter::make('booking_status')
    //                 ->label('Status')
    //                 ->default('upcoming')
    //                 ->options([
    //                     'upcoming'  => 'Upcoming',
    //                     'ongoing'   => 'Ongoing',
    //                     'finished'  => 'Finished',
    //                     'cancelled' => 'Cancelled', 
    //                 ])
    //                 ->query(function (Builder $query, array $data) {
    //                     $status = $data['value'] ?? null;
    //                     $now = now();
    //                     return match ($status) {
    //                         'upcoming' => $query->where('start_datetime', '>', $now)->where('status','approved'),
    //                         'ongoing'  => $query->where('start_datetime', '<=', $now)->where('end_datetime', '>=', $now)->where('status','approved'),
    //                         'finished' => $query->where('end_datetime', '<', $now)->where('status','approved'),
    //                         'cancelled'=> $query->where('status', 'cancelled'), 
    //                         default    => $query,
    //                     };
    //                 })
    //         ])
    //         ->filtersLayout(FiltersLayout::Modal)
    //         ->filtersFormWidth('md')
    //         ->actions(
    //             auth()->user()->hasActiveSubscription()
    //                 ? [
    //                     Tables\Actions\ActionGroup::make($tableActions)
    //                         ->icon('heroicon-o-ellipsis-horizontal-circle')
    //                         ->size(ActionSize::ExtraLarge)
    //                 ] : []
    //         )
    //         ->bulkActions(
    //             (auth()->user()->hasActiveSubscription()) ? [] : []
    //         );
    // }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
        return [
            // BookingStatsWidget::class
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Example: show count of pending bookings
        $count = Reservation::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }
}
