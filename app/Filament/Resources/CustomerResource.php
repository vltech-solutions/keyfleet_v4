<?php

namespace App\Filament\Resources;

use App\Exports\CustomerExport;
use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Company;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\Facades\Image;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    

    protected static ?int $navigationSort = 4;

    protected function isReadOnly(): bool
    {
        return false; 
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Transactions';
    }

    public static function getNavigationGroupSort(): ?int
    {
        return 4;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('customer_name')
                    ->required()
                    ->columnSpan('full'),
                Textarea::make('address')
                    ->required()
                    ->columnSpan('full'),
                TextInput::make('contact_number')
                    ->required()
                    ->columnSpan('full'),
                TextInput::make('email')
                    ->email(),
                TextInput::make('facebook_name')
                ->label('Facebook Link')
                ->url() 
                ->rules([
                    // 'required',
                    'url',
                    'regex:/^https:\/\/(www\.)?(facebook\.com|fb\.com)\/.+$/i',
                ])
                ->placeholder('https://facebook.com/username')
            ]);
    }

    public static function table(Table $table): Table
    {
        
        return $table
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Customer')
                    // ->sortable(),
                    ->searchable(),
                
                TextColumn::make('contact_number')
                    ->label('Contact Number')
                    // ->sortable(),
                    ->searchable(),
                
                TextColumn::make('email')
                    ->label('Email')
                    // ->sortable(),
                    ->searchable(),

            //    TextColumn::make('customer_contact')
            //         ->label('Customer Details')
            //         ->html()
            //         ->getStateUsing(function ($record) {
            //             $rows = [];
            //             if (!empty($record->address)) {
            //                 $rows[] = "
            //                     <div style='display: table-row;'>
            //                         <div style='display: table-cell; color: gray; width: 70px;'>Address:</div>
            //                         <div style='display: table-cell; padding-left: 5px;'>{$record->address}</div>
            //                     </div>
            //                 ";
            //             }
            //             if (!empty($record->contact_number)) {
            //                 $rows[] = "
            //                     <div style='display: table-row;'>
            //                         <div style='display: table-cell; color: gray; width: 70px;'>Phone:</div>
            //                         <div style='display: table-cell; padding-left: 5px;'>{$record->contact_number}</div>
            //                     </div>
            //                 ";
            //             }
            //             if (!empty($record->email)) {
            //                 $rows[] = "
            //                     <div style='display: table-row;'>
            //                         <div style='display: table-cell; color: gray; width: 70px;'>Email:</div>
            //                         <div style='display: table-cell; padding-left: 5px;'>{$record->email}</div>
            //                     </div>
            //                 ";
            //             }

            //             if (empty($rows)) {return '-';}

            //             return "
            //                 <div style='display: table; width: 100%;'>
            //                     " . implode('', $rows) . "
            //                 </div>
            //             ";
            //         })

            //         ->searchable(['contact_number', 'email', 'address']),
                
                // TextColumn::make('repeat_token')
                //     ->label('Booking Token for Reservation')
                //     ->copyable()
                //     ->copyMessage('Token copied!'),
                
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y h:i A') 
                    ->sortable(),

                TextColumn::make('bookings_count')
                    ->label('Bookings Count')
                    ->counts('bookings')
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Total Balance')
                    ->money('PHP')
                    ->getStateUsing(fn ($record) => $record->bookings()->sum('balance'))
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray') // red if > 0
                    ->extraAttributes(fn ($state) => $state > 0 ? ['class' => 'font-bold'] : []),


            ])
            ->defaultSort('bookings_count', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('facebook')
                        ->label('Facebook')
                        ->icon('heroicon-o-user-circle')
                        ->color('info')
                        ->tooltip('View Facebook Profile')
                        ->url(fn ($record) => $record->facebook_name ? $record->facebook_name : null, true)
                        ->visible(fn ($record) => !empty($record->facebook_name)),

                    // Tables\Actions\Action::make('copyToken')
                    //     ->label('Copy Token')
                    //     ->icon('heroicon-o-clipboard')
                    //     ->color('success')
                    //     ->tooltip('Click to copy the renter token for reservation')
                    //     ->action(function ($record, $livewire) {
                    //         $livewire->dispatch('copy-to-clipboard', [
                    //             'value' => $record->repeat_token,
                    //         ]);

                    //         Notification::make()
                    //             ->title('Token Copied')
                    //             ->success()
                    //             ->send();
                    //     }),

                    Tables\Actions\Action::make('downloadQR')
                        ->label('Booking QR Code')
                        ->icon('heroicon-o-qr-code')
                        ->color('success')
                        ->tooltip('This QR will be use for online reservation, download and send this to the renter.')
                        ->action(function ($record, $livewire) {
                            $companyName = 'VL TECH'; // or $record->company->name if you have relation

                            $qrBase64 = base64_encode(
                                QrCode::format('png')
                                    ->size(500)
                                    ->margin(2)
                                    // ->eye('circle')
                                    ->color(0, 0, 0)
                                    ->backgroundColor(255, 255, 255)
                                    ->generate($record->repeat_token)
                            );

                            $qrBinary = base64_decode($qrBase64);

                            $image = Image::make($qrBinary);

                            // $image->resizeCanvas($image->width(), $image->height() + 80, 'top', false, [255, 255, 255]);

                            $companyId = Filament::getTenant()?->id;
                            $company = Company::find(Filament::getTenant()?->id);
                            
                            // Load logo
                            $logoPath = storage_path('app/public/' . $company->avatar_url);
                            if (file_exists($logoPath)) {
                                $logoSize = 70;
                                $logo = Image::make($logoPath)->resize($logoSize, $logoSize, function ($c) {
                                    $c->aspectRatio();
                                    $c->upsize();
                                });

                                // Create a white background box (with padding)
                                $boxSize = $logoSize + 20;
                                $box = Image::canvas($boxSize, $boxSize, '#ffffff');

                                // Optionally, make the box rounded
                                $mask = Image::canvas($boxSize, $boxSize);
                                $box->rectangle(0, 0, $boxSize - 1, $boxSize - 1, function ($draw) {
                                    $draw->border(1, '#000');
                                    $draw->background('#dddddd');
                                });
                                $box->mask($mask, true);

                                // Insert the logo on the box, then box on the QR
                                $box->insert($logo, 'center');
                                $image->insert($box, 'center');
                            }


                            // 6️⃣ Convert back to base64 for browser download
                            $base64 = 'data:image/png;base64,' . base64_encode($image->encode('png'));
                            $fileName = 'qr-' . $record->id . '.png';

                            $livewire->dispatch('trigger-download', fileName: $fileName, data: $base64);

                            Notification::make()
                                ->title('QR Code generated')
                                ->success()
                                ->send();
                        }),

                    ...(auth()->user()->hasActiveSubscription() ? [
                        Tables\Actions\EditAction::make()
                            ->label('View')
                            ->icon('heroicon-o-eye')
                            ->color('gray')
                            ->modalWidth('lg'),
                    ] : []),
                ])
                    ->label('Actions')
                    ->icon('heroicon-o-ellipsis-horizontal-circle')
                    ->size(ActionSize::ExtraLarge)
            ])

            ->bulkActions(
                (auth()->user()->hasActiveSubscription()) ? [
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\DeleteBulkAction::make(),
                    ]),
                ] : []
            );
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BookingsRelationManager::class,
            RelationManagers\RequirementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'edit' => Pages\EditCustomer::route('/{record}'),
        ];
    }
}
