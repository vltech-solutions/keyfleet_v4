<?php

namespace App\Filament\Admin\Resources\CompanyResource\Pages;

use App\Filament\Admin\Resources\CompanyResource;
use App\Models\Company;
use Filament\Forms\Components\Builder;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ViewCompany extends ViewRecord implements HasTable
{
    use Tables\Concerns\InteractsWithTable;
    protected static string $resource = CompanyResource::class;

    protected static string $view = 'filament.pages.view-company';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Edit Company'),
        ];
    }

    // public function getRecord(): Company
    // {
    //     return Company::with([
    //         'subscription.plan',
    //     ])
    //     ->withCount(['cars', 'bookings'])
    //     ->findOrFail($this->record->id);
    // }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return $this->record
                    ->subscriptions()
                    ->with(['plan', 'planPrice'])
                    ->orderByDesc('starts_at')
                    ->getQuery(); 
            })
            ->columns([
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('planPrice.billing_cycle')
                    ->label('Billing Cycle')
                    ->sortable(),

                Tables\Columns\TextColumn::make('planPrice.price')
                    ->label('Price')
                    ->money('PHP'),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Starts At')
                    ->date(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Ends At')
                    ->date(),

                // Tables\Columns\IconColumn::make('auto_renew')
                //     ->boolean()
                //     ->label('Auto Renew'),
            ])
            ->defaultSort('starts_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    public function getTitle(): string
    {
        return 'View Company';
        // return $this->record->name;
    }
}
