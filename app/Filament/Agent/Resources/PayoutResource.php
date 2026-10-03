<?php

namespace App\Filament\Agent\Resources;

use App\Filament\Agent\Resources\PayoutResource\Pages;
use App\Models\AgentPayout;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PayoutResource extends Resource
{
    protected static ?string $model = AgentPayout::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('agent_id', auth()->user()->agentProfile->id);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reference_number')->searchable(),
            Tables\Columns\TextColumn::make('period_start')->date(),
            Tables\Columns\TextColumn::make('period_end')->date(),
            Tables\Columns\TextColumn::make('amount')->money('PHP'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('paid_at')->dateTime()->placeholder('—'),
        ])->actions([Tables\Actions\ViewAction::make()])->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\TextEntry::make('reference_number'),
            Infolists\Components\TextEntry::make('amount')->money('PHP'),
            Infolists\Components\TextEntry::make('status')->badge(),
            Infolists\Components\RepeatableEntry::make('commissions')->schema([
                Infolists\Components\TextEntry::make('company.name'),
                Infolists\Components\TextEntry::make('commission_amount')->money('PHP'),
                Infolists\Components\TextEntry::make('earned_at')->dateTime(),
            ])->columns(3)->columnSpanFull(),
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPayouts::route('/'), 'view' => Pages\ViewPayout::route('/{record}')];
    }
}
