<?php

namespace App\Filament\Agent\Resources;

use App\Filament\Agent\Resources\ReferralResource\Pages;
use App\Models\CompanyReferral;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReferralResource extends Resource
{
    protected static ?string $model = CompanyReferral::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'My Referrals';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('agent_id', auth()->user()->agentProfile->id);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('referred.name')->label('Business')->searchable(),
            Tables\Columns\TextColumn::make('referred.subscription.plan.name')->label('Current Base Plan')->placeholder('—'),
            Tables\Columns\TextColumn::make('referred.subscription.ends_at')->label('Subscription Until')->date()->placeholder('—'),
            Tables\Columns\TextColumn::make('referred_at')->dateTime(),
            Tables\Columns\TextColumn::make('qualified_at')->label('First Paid Date')->dateTime()->placeholder('—'),
            Tables\Columns\TextColumn::make('commission_ends_at')->label('Eligibility Until')->dateTime()->placeholder('—'),
            Tables\Columns\TextColumn::make('commissions_sum_commission_amount')->sum('commissions', 'commission_amount')->label('Commission Earned')->money('PHP'),
            Tables\Columns\TextColumn::make('is_converted')->label('Status')->formatStateUsing(fn ($state) => $state ? 'Qualified' : 'Referred')->badge(),
        ])->actions([Tables\Actions\ViewAction::make()])->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListReferrals::route('/'), 'view' => Pages\ViewReferral::route('/{record}')];
    }
}
