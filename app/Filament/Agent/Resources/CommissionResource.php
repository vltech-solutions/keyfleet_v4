<?php

namespace App\Filament\Agent\Resources;

use App\Filament\Agent\Resources\CommissionResource\Pages;
use App\Models\AgentCommission;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists;
use Filament\Infolists\Infolist;
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
            Tables\Columns\TextColumn::make('subscription.id')->label('Payment Reference')->formatStateUsing(fn ($state) => 'SUB-'.$state),
            Tables\Columns\TextColumn::make('actual_base_subscription_amount')->label('Base Subscription')->money('PHP'),
            Tables\Columns\TextColumn::make('commissionable_amount')->label('Eligible Base')->money('PHP'),
            Tables\Columns\TextColumn::make('commission_rate')->suffix('%'),
            Tables\Columns\TextColumn::make('commission_amount')->money('PHP'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('earned_at')->dateTime(),
            Tables\Columns\TextColumn::make('payable_at')->dateTime(),
            Tables\Columns\TextColumn::make('paid_at')->dateTime()->placeholder('—'),
        ])->filters([
            SelectFilter::make('status')->options(array_combine(
                ['pending', 'payable', 'paid', 'partially_reversed', 'reversed'],
                ['On Hold', 'Payable', 'Paid', 'Partially Reversed', 'Reversed'],
            )),
            SelectFilter::make('company_id')->relationship('company', 'name')->label('Company'),
            Filter::make('earned_at')->form([DatePicker::make('from'), DatePicker::make('until')])->query(fn (Builder $query, array $data) => $query
                ->when($data['from'], fn ($q, $date) => $q->whereDate('earned_at', '>=', $date))
                ->when($data['until'], fn ($q, $date) => $q->whereDate('earned_at', '<=', $date))),
        ])->actions([Tables\Actions\ViewAction::make()])->bulkActions([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Subscription Payment')->schema([
                Infolists\Components\TextEntry::make('company.name')->label('Referred Business'),
                Infolists\Components\TextEntry::make('subscription_id')->label('Payment Reference')->formatStateUsing(fn ($state) => 'SUB-'.$state),
                Infolists\Components\TextEntry::make('subscription.plan.name')->label('Subscription'),
                Infolists\Components\TextEntry::make('earned_at')->label('Payment Date')->dateTime(),
                Infolists\Components\TextEntry::make('actual_base_subscription_amount')->label('Base Subscription Amount')->money('PHP'),
                Infolists\Components\TextEntry::make('payment_coverage_start')->label('Coverage Start')->date(),
                Infolists\Components\TextEntry::make('payment_coverage_end')->label('Coverage End')->date(),
            ])->columns(2),
            Infolists\Components\Section::make('Commission Calculation')->schema([
                Infolists\Components\TextEntry::make('eligible_coverage_start')->label('Eligible Coverage Start')->date(),
                Infolists\Components\TextEntry::make('eligible_coverage_end')->label('Eligible Coverage End')->date(),
                Infolists\Components\TextEntry::make('eligible_coverage_days')->label('Eligible / Total Days')
                    ->formatStateUsing(fn ($state, AgentCommission $record) => $state.' / '.$record->total_coverage_days),
                Infolists\Components\TextEntry::make('commissionable_amount')->label('Eligible Commission Base')->money('PHP'),
                Infolists\Components\TextEntry::make('commission_rate')->label('Commission Rate')->suffix('%'),
                Infolists\Components\TextEntry::make('commission_amount')->label('Original Commission')->money('PHP'),
                Infolists\Components\TextEntry::make('reversed_amount')->label('Reversed Commission')->money('PHP'),
                Infolists\Components\TextEntry::make('net_commission')->label('Net Commission')
                    ->state(fn (AgentCommission $record) => $record->netCommissionAmount())->money('PHP'),
            ])->columns(2),
            Infolists\Components\Section::make('Status')->schema([
                Infolists\Components\TextEntry::make('status')->badge(),
                Infolists\Components\TextEntry::make('payable_at')->label('Hold Until / Payable At')->dateTime(),
                Infolists\Components\TextEntry::make('paid_at')->dateTime()->placeholder('—'),
                Infolists\Components\TextEntry::make('reversed_at')->dateTime()->placeholder('—'),
                Infolists\Components\TextEntry::make('reversal_reason')->columnSpanFull()->placeholder('—'),
            ])->columns(2),
        ]);
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
