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
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Actions\Action;
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

                    Tables\Actions\ActionGroup::make(
                        [
                            Tables\Actions\EditAction::make()->color('gray'),
                            Action::make('generate_token')
                                ->label('Generate Link')
                                ->icon('heroicon-o-link')
                                ->action(function ($record) {
                                    $token = $record->generateAccessToken();
                                    $link = route('partner.report', $token);
                                    
                                    Notification::make()
                                        ->title('Report link generated!')
                                        ->body('Share this link with the partner: ' . $link)
                                        ->success()
                                        ->send();
                                }),
                            
                            Action::make('copy_link')
                                ->label('Copy Link')
                                ->icon('heroicon-o-clipboard')
                                ->color('success')
                                ->visible(function ($record) {
                                    return $record->access_token !== null;
                                })
                                ->action(function ($record) {
                                    $link = route('partner.report', $record->access_token);
                                    
                                    Notification::make()
                                        ->title('Copy this Link for your Partner')
                                        ->body($link)
                                        ->success()
                                        ->send();
                                })
                                ->extraAttributes([
                                    'onclick' => "navigator.clipboard.writeText('" . route('partner.report', 'TOKEN_PLACEHOLDER') . "'.replace('TOKEN_PLACEHOLDER', this.closest('tr').dataset.recordId))",
                                ]),
                                
                            Action::make('regenerate_token')
                                ->label('Regenerate Link')
                                ->icon('heroicon-o-arrow-path')
                                ->color('warning')
                                ->requiresConfirmation()
                                ->modalHeading('Regenerate Report Link')
                                ->modalDescription('This will invalidate the current link and generate a new one. The partner will need to use the new link.')
                                ->modalSubmitActionLabel('Yes, regenerate')
                                ->action(function ($record) {
                                    $token = $record->generateAccessToken();
                                    $link = route('partner.report', $token);
                                    
                                    Notification::make()
                                        ->title('Link regenerated!')
                                        ->body('New link: ' . $link)
                                        ->success()
                                        ->send();
                                }),
                                
                            Action::make('revoke_token')
                            ->label('Revoke Access')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->modalHeading('Revoke Access')
                            ->modalDescription('This will revoke access to the report immediately. The partner will no longer be able to view the report.')
                            ->modalSubmitActionLabel('Yes, revoke')
                            ->visible(function ($record) {
                                return $record->access_token !== null;
                            })
                            ->action(function ($record) {
                                $record->access_token = null;
                                $record->token_expires_at = null;
                                $record->save();
                                
                                Notification::make()
                                    ->title('Access revoked!')
                                    ->body('The partner can no longer access the report.')
                                    ->success()
                                    ->send();
                            }),
                        
                    
                        ]    
                    )
                        ->icon('heroicon-o-ellipsis-horizontal-circle')
                        ->size(ActionSize::ExtraLarge)

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
