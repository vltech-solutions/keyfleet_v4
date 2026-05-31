<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FundTypeResource\Pages;
use App\Filament\Resources\FundTypeResource\RelationManagers;
use App\Models\FundType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                TextInput::make('name')
                    ->columnSpan('full')
                    ->required()
                    ->disabled(fn ($record) => in_array($record?->name, ['Income', "Partner's Fund"])),
                TextInput::make('balance')
                    ->numeric()
                    ->label('Balance')
                    ->default(0)
                    // ->disabled(fn ($record) => in_array($record?->name, ["Partner's Fund"]))
                    ->readonly()
                    ->columnSpan('full')
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
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Last Update')
                    ->dateTime('M d, Y h:i A')
            ])
            ->filters([
                //
            ])
             ->actions(
                (auth()->user()->hasActiveSubscription()) ? [
                    Tables\Actions\EditAction::make()->color('gray')->modalWidth('md'),
                    // Tables\Actions\DeleteAction::make()->color('gray'),
                ] : []
            )
            ->bulkActions(
                // (auth()->user()->hasActiveSubscription()) ? [
                //     Tables\Actions\BulkActionGroup::make([
                //         Tables\Actions\DeleteBulkAction::make()
                //             ->before(function ($records, $action) {
                //                 $protected = ['Income', "Partner's Fund"];
                                
                //                 $blocked = $records->filter(fn ($record) => in_array($record->name, $protected));

                //                 if ($blocked->isNotEmpty()) {
                //                     Notification::make()
                //                         ->title('Action Not Allowed')
                //                         ->body("The following fund types cannot be deleted: " . $blocked->pluck('name')->join(', '))
                //                         ->danger()
                //                         ->send();

                //                     $action->cancel(); // Prevents delete and the default "Deleted" notification
                //                 }
                //             })
                //     ]),
                // ] : []
                []
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
            'index' => Pages\ListFundTypes::route('/'),
            // 'create' => Pages\CreateFundType::route('/create'),
            // 'edit' => Pages\EditFundType::route('/{record}/edit'),
        ];
    }
}
