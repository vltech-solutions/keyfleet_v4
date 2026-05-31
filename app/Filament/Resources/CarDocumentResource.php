<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CarDocumentResource\Pages;
use App\Filament\Resources\CarDocumentResource\RelationManagers;
use App\Models\CarDocument;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CarDocumentResource extends Resource
{
    protected static ?string $model = CarDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 4;
    
    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationGroup(): ?string
    {
        return 'Transactions';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make()
                ->columns(1)
                ->schema([
                    Select::make('car_id')
                        ->relationship('car', 'name')
                        ->required(),

                    TextInput::make('document_type')
                        ->label('Document Type')
                        ->required()
                        ->maxLength(255),

                    DatePicker::make('expiration_date')
                        ->required(),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
                TextColumn::make('car.name')
                    ->label('Car'),
                TextColumn::make('document_type')
                    ->label('Type')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                TextColumn::make('expiration_date')
                    ->label('Expires at')
                    ->date()
                    ->color(fn ($state) => now()->diffInDays($state, false) <= 30 ? 'danger' : 'default'),
                
                TextColumn::make('warning')
                    ->label('Warning')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $daysLeft = now()->diffInDays($record->expiration_date, false);

                        return match (true) {
                            $daysLeft < 0 => 'Expired',
                            $daysLeft <= 1 => 'Tomorrow',
                            $daysLeft <= 7 => '1 Week Left',
                            $daysLeft <= 31 => '1 Month Left',
                            default => 'OK',
                        };
                    })
                    ->color(function ($state) {
                        return match ($state) {
                            'Expired', 'Tomorrow' => 'danger',
                            '1 Week Left', '1 Month Left' => 'warning',
                            'OK' => 'success',
                            default => 'gray',
                        };
                    }),
            ])
            ->filters([
                 SelectFilter::make('car.name')
                    ->relationship('car', 'name'),
            ])
            ->actions(
                (auth()->user()->hasActiveSubscription()) ? [
                    Tables\Actions\EditAction::make()->color('gray')->modalWidth('md'),
                    Tables\Actions\DeleteAction::make()->color('gray')->modalWidth('md'),
                ] : []
            )
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarDocuments::route('/'),
            // 'create' => Pages\CreateCarDocument::route('/create'),
            // 'edit' => Pages\EditCarDocument::route('/{record}/edit'),
        ];
    }
}
