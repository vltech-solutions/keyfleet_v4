<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Pages\Tenancy\EditTenantProfile;
use IbrahimBougaoua\RadioButtonImage\Actions\RadioButtonImage;
use App\Models\InvoiceTemplate;
use App\Models\RequirementTypes;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Company Settings';
    }

    public function form(Form $form): Form
    {
        $tabs = [
            Tab::make('Profile Settings')
                ->icon('heroicon-o-building-office-2')
                ->schema([
                    FileUpload::make('avatar_url')
                        ->label('Company Logo')
                        ->image()
                        ->maxSize(512)
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                        ->imageEditor()
                        ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                        ->hint('Max: 512 KB.')
                        ->hintAction(
                            Action::make('compress')
                                ->label('Compress your image here')
                                ->url('https://squoosh.app', shouldOpenInNewTab: true)
                        )
                        ->columnSpanFull(),

                    TextInput::make('name')->required(),
                    Textarea::make('address')->required(),
                    Textarea::make('contacts')
                        ->helperText('This will be shown on the invoice.')
                        ->required(),

                    ColorPicker::make('primary_color')
                        ->hint('Example: #FFFFFF')
                        ->regex('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})\b$/')
                        ->required(),

                    RadioButtonImage::make('invoice_template')
                        ->label('Invoice Template')
                        ->options(
                            InvoiceTemplate::all()->pluck('template', 'template')->toArray()
                        ),
                ]),
        ];

        // Check tenant/company subscription
        $company = filament()->getTenant();

        if ($company && ($company->hasNonBasicPaidSubscription() || $company->hasActiveFreeSubscription() ||  $company->hasAddon('booking-pro'))) {
            $tabs[] = Tab::make('Online Booking Form')
                ->icon('heroicon-o-calendar-days')
                ->schema([
                    TextInput::make('booking_link')
                        ->label('Online Booking Form Link')
                        ->readonly()
                        ->formatStateUsing(fn ($record) => $record ? route('booking.wizard', $record->slug) : '')
                        ->suffixAction(
                            Action::make('viewForm')
                                ->label('View Booking Form')
                                ->icon('heroicon-o-link')
                                ->url(fn ($record) => route('booking.wizard', $record->slug), true)
                                ->visible(fn ($record) => filled($record?->slug))
                        ),

                    TextInput::make('requirements_expiry_months')
                        ->label('Requirements Expiry (in Months)')
                        ->numeric()
                        ->minValue(1)
                        ->helperText('Number of months before client requirements expire. This will apply when you approve a pending reservation of a client.')
                        ->placeholder('e.g. 6'),

                    TextInput::make('notif_contact')
                        ->label('Contact Number')
                        ->helperText('You will be notified in this number once you have a new reservation.')
                        ->required()
                        ->maxLength(11)
                        ->extraInputAttributes([
                            'inputmode' => 'numeric',
                            'maxlength' => 11,
                            'pattern' => '[0-9]{10,11}',
                            'oninput' => "this.value = this.value.replace(/[^0-9]/g, '').slice(0,11)",
                        ])
                        ->placeholder('09xxxxxxxxx')
                        ->rule('regex:/^[0-9]{10,11}$/'),

                    Select::make('enabled_requirements')
                        ->label('Enabled Requirements')
                        ->helperText('Please select all requirements you required for your customers.')
                        ->multiple()
                        ->options(
                            RequirementTypes::all()
                                ->mapWithKeys(fn ($item) => [
                                    $item->id => ($item->sample && $item->sample != '-')
                                        ? $item->label . ' — (' . $item->sample . ')'
                                        : $item->label,
                                ])
                                ->toArray()
                        ),

                    Select::make('delivery_methods')
                        ->label('Delivery & Collection Methods')
                        ->placeholder('Select Delivery Methods')
                        ->multiple() // This enables multiple selection
                        ->options([
                            'renter_pickup_renter_return' => 'Renter Pickup & Renter Return',
                            'renter_pickup_owner_collection' => 'Renter Pickup & Owner Collection',
                            'owner_delivery_renter_return' => 'Owner Delivery & Renter Return',
                            'owner_delivery_owner_collection' => 'Owner Delivery & Owner Collection',
                        ])
                        ->searchable()
                        ->preload()
                        ->required(),

                    Toggle::make('booking_form_dark_mode')
                        ->label('Dark Mode')
                        ->helperText('Enable or disable dark mode for the booking form.'),

                    Toggle::make('offer_driver_service')
                        ->label('Offer Driver Service')
                        ->helperText('Include a professional driver option'),
                ]);
            
            
                
                //company website
            
                // $tabs[] = Tab::make('Website Customization')
                // ->icon('heroicon-o-globe-alt')
                // ->schema([
                //     Section::make('Public Profile')
                //         ->description('Customize how your car rental page looks to the public.')
                //         ->schema([
                //             Grid::make(2)->schema([
                //                 TextInput::make('website.header_text')
                //                     ->label('Banner Title')
                //                     ->placeholder('e.g. Best Car Rental in Manila'),
                                    
                //                 TextInput::make('website.subheader')
                //                     ->label('Banner Subtitle')
                //                     ->placeholder('e.g. Affordable & Reliable Vehicles'),
                //             ]),

                //             Grid::make(2)->schema([
                //                 FileUpload::make('website.banner')
                //                     ->label('Homepage Banner Image')
                //                     ->image()
                //                     ->disk('s3')
                //                     ->directory('company/banners')
                //                     ->visibility('private')
                //                     ->helperText('Recommended size: 1920x600px'),

                //                 FileUpload::make('website.about_us_image')
                //                     ->label('About Us Image')
                //                     ->image()
                //                     ->disk('s3')
                //                     ->directory('company/about')
                //                     ->visibility('private')
                //                     ->helperText('Recommended: Square or Portrait image.'),
                //             ]),

                //             RichEditor::make('website.about_us')
                //                 ->label('About Us / Terms')
                //                 ->placeholder('Tell your story or list your general terms...')
                //                 ->columnSpanFull(),

                //             Grid::make(1)->schema([
                //                 TextInput::make('website.map_url')
                //                     ->label('Google Maps Embed URL')
                //                     ->placeholder('https://www.google.com/maps/embed?pb=...')
                //                     ->helperText('Go to Google Maps > Share > Embed a map and copy only the src URL.')
                //                     ->reactive(),

                //                 // Map Preview
                //                 ViewField::make('map_preview')
                //                     ->view('filament.forms.components.map-preview')
                //                     ->columnSpanFull()
                //                     ->hidden(fn ($get) => ! $get('website.map_url')),

                //                 Textarea::make('website.business_address')
                //                     ->label('Office Address (Text)')
                //                     ->rows(3)
                //                     ->placeholder('House No, Street, Barangay, City, Province')
                //                     ->columnSpanFull(),
                //             ]),
                //         ])
                // ]);
        }

        return $form->schema([
            Tabs::make('Settings')->tabs($tabs)->columnSpanFull(),
        ]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $websiteData = $data['website'] ?? [];
        unset($data['website']);

        $record->update($data);

        $record->website()->updateOrCreate(
            ['company_id' => $record->id],
            $websiteData
        );

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->tenant;
        
        if ($record && $record->website) {
            $data['website'] = $record->website->toArray();
        }

        return $data;
    }

}
