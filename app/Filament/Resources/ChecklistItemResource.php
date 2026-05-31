<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChecklistItemResource\Pages;
use App\Filament\Resources\ChecklistItemResource\RelationManagers;
use App\Models\ChecklistItem;
use App\Models\ChecklistGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\ActionSize;

class ChecklistItemResource extends Resource
{
    protected static ?string $model = ChecklistItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Inspection Checklist';

    protected static ?string $pluralLabel = 'Inspection Checklist';
    
    protected static ?string $modelLabel = 'Inspection Checklist';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationGroup(): ?string
    {
        return 'Fleet Management';
    }

    public static function getNavigationSort(): ?int
    {
        return 4; 
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('group_id')
                    ->label('Group')
                    ->options(ChecklistGroup::orderBy('order')->pluck('name', 'id'))
                    ->required()
                    ->columnSpan('full'),
                TextInput::make('item')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('group.name')
                    ->label('Group')
                    ->sortable()
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions(
                (auth()->user()->hasActiveSubscription()) ? [
                    Tables\Actions\EditAction::make()->color('gray'),
                    Tables\Actions\DeleteAction::make()->color('gray'),
                ] : []
            )
            ->actions([
                Tables\Actions\ActionGroup::make([
                    ...(
                        auth()->user()->hasActiveSubscription()
                            ? [
                                Tables\Actions\EditAction::make()->color('gray')
                                    ->modalWidth(MaxWidth::Medium),
                                Tables\Actions\DeleteAction::make()->color('gray'),
                            ]
                            : []
                    ),
                ])
                    ->label('Actions')
                    ->icon('heroicon-o-ellipsis-horizontal-circle')
                    ->size(ActionSize::ExtraLarge),
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
            'index' => Pages\ListChecklistItems::route('/'),
            // 'create' => Pages\CreateChecklistItem::route('/create'),
            // 'edit' => Pages\EditChecklistItem::route('/{record}/edit'),
        ];
    }
}
