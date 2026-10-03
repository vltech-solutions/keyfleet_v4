<?php

namespace App\Filament\Agent\Resources;

use App\Filament\Agent\Resources\CommissionResource\Pages;
use App\Models\AgentCommission;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommissionResource extends Resource
{
    protected static ?string $model = AgentCommission::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('agent_id', auth()->user()->agentProfile->id);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('company.name')->searchable(),
            Tables\Columns\TextColumn::make('subscription.id')->label('Base Payment')->formatStateUsing(fn ($state) => 'SUB-'.$state),
            Tables\Columns\TextColumn::make('commissionable_amount')->money('PHP'),
            Tables\Columns\TextColumn::make('commission_rate')->suffix('%'),
            Tables\Columns\TextColumn::make('commission_amount')->money('PHP'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('earned_at')->dateTime(),
            Tables\Columns\TextColumn::make('payable_at')->dateTime(),
            Tables\Columns\TextColumn::make('paid_at')->dateTime()->placeholder('—'),
        ])->filters([
            SelectFilter::make('status')->options(array_combine(['pending', 'payable', 'paid', 'reversed'], ['Pending', 'Payable', 'Paid', 'Reversed'])),
            SelectFilter::make('company_id')->relationship('company', 'name')->label('Company'),
            Filter::make('earned_at')->form([DatePicker::make('from'), DatePicker::make('until')])->query(fn (Builder $query, array $data) => $query
                ->when($data['from'], fn ($q, $date) => $q->whereDate('earned_at', '>=', $date))
                ->when($data['until'], fn ($q, $date) => $q->whereDate('earned_at', '<=', $date))),
        ])->actions([Tables\Actions\ViewAction::make()])->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListCommissions::route('/'), 'view' => Pages\ViewCommission::route('/{record}')];
    }
}
