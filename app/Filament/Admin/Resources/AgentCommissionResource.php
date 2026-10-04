<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AgentCommissionResource\Pages;
use App\Models\AgentCommission;
use App\Services\AgentCommissionService;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AgentCommissionResource extends Resource
{
    protected static ?string $model = AgentCommission::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Agent Program';

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('agent.user.name')->label('Agent')->searchable(),
            Tables\Columns\TextColumn::make('company.name')->searchable(),
            Tables\Columns\TextColumn::make('subscription_id')->label('Payment'),
            Tables\Columns\TextColumn::make('commissionable_amount')->money('PHP'),
            Tables\Columns\TextColumn::make('commission_rate')->suffix('%'),
            Tables\Columns\TextColumn::make('commission_amount')->money('PHP'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('earned_at')->dateTime(),
            Tables\Columns\TextColumn::make('payable_at')->dateTime(),
        ])->actions([
            Tables\Actions\Action::make('reverse')->color('danger')->requiresConfirmation()->form([
                Forms\Components\Textarea::make('reason')->required()->minLength(5),
            ])->action(function (AgentCommission $record, array $data): void {
                app(AgentCommissionService::class)->reverse($record, $data['reason']);
                activity()->performedOn($record)->causedBy(auth()->user())->withProperties(['reason' => $data['reason']])->log('Agent commission reversed');
            })->visible(fn (AgentCommission $record) => in_array($record->status, [AgentCommission::STATUS_PENDING, AgentCommission::STATUS_PAYABLE, AgentCommission::STATUS_PARTIALLY_REVERSED], true)),
            Tables\Actions\ViewAction::make(),
        ])->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAgentCommissions::route('/'), 'view' => Pages\ViewAgentCommission::route('/{record}')];
    }
}
