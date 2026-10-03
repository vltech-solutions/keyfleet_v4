<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AgentPayoutResource\Pages;
use App\Models\Agent;
use App\Models\AgentCommission;
use App\Models\AgentPayout;
use App\Services\AgentCommissionService;
use App\Services\AgentPayoutService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AgentPayoutResource extends Resource
{
    protected static ?string $model = AgentPayout::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationGroup = 'Agent Program';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('agent_id')
                ->options(fn () => Agent::with('user')->get()->pluck('user.name', 'id'))
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(fn (Set $set) => $set('commission_ids', []))
                ->required(),
            Forms\Components\Select::make('commission_ids')
                ->label('Payable Commissions')
                ->multiple()
                ->options(fn (Get $get): array => self::payableCommissionOptions($get('agent_id')))
                ->searchable()
                ->preload()
                ->disabled(fn (Get $get): bool => blank($get('agent_id')))
                ->helperText('Select an agent first. Commissions appear after their configured holding period and cannot already belong to a payout.')
                ->required(),
            Forms\Components\TextInput::make('reference_number')->helperText('Leave blank to generate automatically.'),
            Forms\Components\DatePicker::make('period_start'),
            Forms\Components\DatePicker::make('period_end')->afterOrEqual('period_start'),
            Forms\Components\Textarea::make('notes'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('reference_number')->searchable(),
            Tables\Columns\TextColumn::make('agent.user.name')->label('Agent')->searchable(),
            Tables\Columns\TextColumn::make('amount')->money('PHP'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('period_start')->date(),
            Tables\Columns\TextColumn::make('period_end')->date(),
            Tables\Columns\TextColumn::make('paid_at')->dateTime()->placeholder('—'),
        ])->actions([
            Tables\Actions\Action::make('markPaid')->label('Mark paid')->requiresConfirmation()
                ->action(function (AgentPayout $record): void {
                    app(AgentPayoutService::class)->markPaid($record);
                    activity()->performedOn($record)->causedBy(auth()->user())->log('Agent payout marked paid');
                })->visible(fn (AgentPayout $record) => in_array($record->status, [AgentPayout::STATUS_DRAFT, AgentPayout::STATUS_PROCESSING], true)),
            Tables\Actions\ViewAction::make(),
        ])->bulkActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAgentPayouts::route('/'), 'create' => Pages\CreateAgentPayout::route('/create'), 'view' => Pages\ViewAgentPayout::route('/{record}')];
    }

    /** @return array<int, string> */
    private static function payableCommissionOptions(mixed $agentId): array
    {
        if (blank($agentId)) {
            return [];
        }

        // The scheduler remains the primary transition mechanism. This idempotent
        // reconciliation prevents a due commission being hidden if a worker is late.
        app(AgentCommissionService::class)->promotePayable();

        return AgentCommission::query()
            ->with('company')
            ->where('agent_id', $agentId)
            ->where('status', AgentCommission::STATUS_PAYABLE)
            ->whereDoesntHave('payoutItem')
            ->orderBy('earned_at')
            ->get()
            ->mapWithKeys(fn (AgentCommission $commission): array => [
                $commission->id => $commission->company->name.' — SUB-'.$commission->subscription_id.' — ₱'.number_format((float) $commission->commission_amount, 2),
            ])
            ->all();
    }
}
