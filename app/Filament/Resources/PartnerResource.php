<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Filament\Resources\PartnerResource\RelationManagers;
use App\Models\Partner;
use App\Models\Partners;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class PartnerResource extends Resource
{
    protected static ?string $model = Partners::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    public static function getNavigationGroup(): ?string
    {
        return 'Fleet Management';
    }

    public static function getNavigationGroupSort(): ?int
    {
        return 2;
    }

    public static function getNavigationSort(): ?int
    {
        return 0; 
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->email()
                        ->nullable()
                        ->maxLength(255),

                    TextInput::make('contact_number')
                        ->label('Contact Number')
                        ->numeric()
                        ->placeholder('09123456789')
                        ->nullable()
                        ->maxLength(13),

                    TextInput::make('address')
                        ->label('Address')
                        ->nullable()
                        ->maxLength(255),
                ]),

                Fieldset::make('Commission Settings')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('commission_base')
                                ->options([
                                    'rent_only' => 'Base Rent Only',
                                    'total_due' => 'Total Booking Amount',
                                ])
                                ->default('rent_only')
                                ->required()
                                ->label('Commission Based On'),

                            Select::make('commission_type')
                                ->options([
                                    'percentage' => 'Percentage',
                                    'fixed' => 'Fixed Amount',
                                ])
                                ->required()
                                ->default('percentage')
                                ->label('Commission Type'),

                            TextInput::make('commission_value')
                                ->required()
                                ->numeric()
                                ->label('Commission Value'),
                        ]),
                    ])
                    ->label('Commission Settings (applies per booking)')
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('contact_number')->searchable(),
                BadgeColumn::make('commission_type')
                    ->colors([
                        'success' => 'percentage',
                        'warning' => 'fixed',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                TextColumn::make('commission_value')
                    ->label('Commission')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->commission_type === 'percentage' ? $state . '%' : '₱' . number_format($state, 2)
                    ),

                BadgeColumn::make('commission_base')
                    ->label('Based On')
                    ->colors([
                        'primary' => 'rent_only',
                        'info' => 'total_due',
                    ])
                    ->formatStateUsing(fn ($state) =>
                        $state === 'total_due' ? 'Total Due' : 'Rent Only'
                    ),

                // TextColumn::make('contact_number')->label('Contact'),
            ])
            ->filters([
                //
            ])
            ->actions(
                // Tables\Actions\EditAction::make()->color('gray'),
                (auth()->user()->hasActiveSubscription()) ? [
                    Tables\Actions\EditAction::make()->color('gray'),
                    // Tables\Actions\DeleteAction::make()->color('gray'),
                ] : []
            )
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
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
            'index' => Pages\ListPartners::route('/'),
            // 'create' => Pages\CreatePartner::route('/create'),
            // 'edit' => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
