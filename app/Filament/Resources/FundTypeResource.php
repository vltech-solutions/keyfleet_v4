<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FundTypeResource\Pages;
use App\Models\FundType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;

class FundTypeResource extends Resource
{
    protected static ?string $model = FundType::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $label = 'Fund'; 

    protected static ?string $plural = 'Funds'; 
    protected static ?int $navigationSort = 3; 

    public static function getNavigationGroup(): ?string
    {
        return 'Transactions';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Fund Details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn ($record) => in_array($record?->name, ['Income', "Partner's Fund"])),
                        
                        TextInput::make('balance')
                            ->numeric()
                            ->label('Balance')
                            ->default(0)
                            ->readonly()
                            ->prefix('₱'),
                    ])->columns(2),

                Section::make('Bank Account Details')
                    ->description('Enter the bank account details for this fund type')
                    ->schema([
                        TextInput::make('account_name')
                            ->label('Account Name')
                            ->maxLength(255)
                            ->helperText('Full name of the account holder'),
                        
                        TextInput::make('account_number')
                            ->label('Account Number')
                            ->maxLength(50)
                            ->helperText('Bank account number'),
                    ])->columns(2),

                Section::make('QR Code')
                    ->description('Upload a QR code for easy payment')
                    ->schema([
                        FileUpload::make('qr_code')
                            ->label('QR Code Image')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('300')
                            ->imageResizeTargetHeight('300')
                            ->directory('funds/qr-codes')
                            ->visibility('public')
                            ->helperText('Upload a square QR code image (recommended size: 300x300px)')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                            ->maxSize(2048), // 2MB
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Fund Type')
                    ->badge()
                    ->searchable(),
                
                TextColumn::make('balance')
                    ->money('PHP')
                    ->color('success')
                    ->extraAttributes(['class' => 'font-bold']),
                
                TextColumn::make('account_name')
                    ->label('Account Name')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('Not set'),
                
                TextColumn::make('account_number')
                    ->label('Account Number')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('Not set'),
                
                ImageColumn::make('qr_code')
                    ->label('QR Code')
                    // ->circular()
                    ->toggleable()
                    ->size(40)
                    ,
                
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Last Update')
                    ->dateTime('M d, Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_bank_details')
                    ->label('Has Bank Details')
                    ->query(fn ($query) => $query->whereNotNull('account_number')->whereNotNull('account_name')),
                
                Tables\Filters\Filter::make('has_qr_code')
                    ->label('Has QR Code')
                    ->query(fn ($query) => $query->whereNotNull('qr_code')),
            ])
            ->actions(
                (auth()->user()->hasActiveSubscription()) ? [
                    Tables\Actions\EditAction::make()->color('gray')->modalWidth('xl'),
                ] : []
            )
            ->bulkActions([]);
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
            'index' => Pages\ListFundTypes::route('/'),
        ];
    }
}