<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\FundType;
use Filament\Tables\Actions\Action;

class FundTypes extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Funds';

    public function getHeading(): string
    {
        $total = FundType::where('balance', '>', 0)->whereNot('name',"Partner's Fund")->sum('balance');
        return 'Fund Types — Total: ₱' . number_format($total, 2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FundType::query()->where('balance', '>', 0)
                ->whereNot('name',"Partner's Fund")
            )
            ->headerActions([
                Action::make('total-balance')
                    ->label('Total Funds: ₱' . number_format(FundType::where('balance', '>', 0)->whereNot('name',"Partner's Fund")->sum('balance'), 2))
                    ->disabled() 
                    ->color('gray'), 
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Fund')
                    ->badge(),
                TextColumn::make('balance')
                    ->money('PHP'),
            ]);
    }
}
