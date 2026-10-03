<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AgentReferralResource\Pages;
use App\Models\Agent;
use App\Models\CompanyReferral;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AgentReferralResource extends Resource
{
    protected static ?string $model = CompanyReferral::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Agent Program';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('program_type', CompanyReferral::PROGRAM_AGENT);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('agent.user.name')->label('Agent')->searchable(),
            Tables\Columns\TextColumn::make('referred.name')->label('Referred Company')->searchable(),
            Tables\Columns\TextColumn::make('referral_code'),
            Tables\Columns\TextColumn::make('referred_at')->dateTime(),
            Tables\Columns\TextColumn::make('qualified_at')->dateTime()->placeholder('—'),
            Tables\Columns\TextColumn::make('commission_ends_at')->dateTime()->placeholder('—'),
        ])->actions([
            Tables\Actions\Action::make('reassign')->icon('heroicon-o-arrow-path')->form([
                Forms\Components\Select::make('agent_id')->options(Agent::with('user')->get()->pluck('user.name', 'id'))->searchable()->required(),
                Forms\Components\Textarea::make('reason')->required()->minLength(5),
            ])->action(function (CompanyReferral $record, array $data): void {
                DB::transaction(function () use ($record, $data): void {
                    $agent = Agent::findOrFail($data['agent_id']);
                    $oldAgent = $record->agent_id;
                    $record->update(['agent_id' => $agent->id, 'agent_program_id' => $agent->agent_program_id, 'referrer_user_id' => $agent->user_id]);
                    activity()->performedOn($record)->causedBy(auth()->user())->withProperties(['old_agent_id' => $oldAgent, 'new_agent_id' => $agent->id, 'reason' => $data['reason']])->log('Agent referral reassigned');
                });
            })->visible(fn (CompanyReferral $record) => ! $record->commissions()->exists()),
            Tables\Actions\ViewAction::make(),
        ])->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAgentReferrals::route('/'), 'view' => Pages\ViewAgentReferral::route('/{record}')];
    }
}
