<?php

namespace App\Filament\Agent\Widgets;

use App\Models\AgentCommission;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentCommissions extends TableWidget
{
    protected static ?string $heading = 'Recent Commission Activity';

    public function table(Table $table): Table
    {
        return $table->query(fn (): Builder => AgentCommission::query()->where('agent_id', auth()->user()->agentProfile->id)->latest('earned_at')->limit(10))
            ->columns([
                TextColumn::make('company.name'),
                TextColumn::make('commission_amount')->money('PHP'),
                TextColumn::make('status')->badge(),
                TextColumn::make('earned_at')->dateTime(),
            ])->paginated(false);
    }
}
