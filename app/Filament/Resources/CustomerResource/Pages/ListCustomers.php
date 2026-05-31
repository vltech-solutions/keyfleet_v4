<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions; // optional: for ActionGroup, etc.
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Filament\Resources\CustomerResource\Widgets\CustomerStatsOverview;


class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        $actions = (auth()->user()->hasActiveSubscription()) ? 
            [
                CreateAction::make()
                    ->label('Add New')
                    ->modalWidth('md'),

                Action::make('exportToExcel')
                    ->label('Export')
                    ->button() 
                    ->color('gray') 
                    ->outlined()
                    ->icon('heroicon-s-arrow-up-tray')
                    ->action(function (): BinaryFileResponse {
                        return Excel::download(new \App\Exports\CustomerExport, 'customers.xlsx');
                    }),
            ] : [
                Action::make('exportToExcel')
                    ->label('Export')
                    ->button() 
                    ->color('gray') 
                    ->outlined()
                    ->icon('heroicon-s-arrow-up-tray')
                    ->action(function (): BinaryFileResponse {
                        return Excel::download(new \App\Exports\CustomerExport, 'customers.xlsx');
                    }),
            ];

        return $actions;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerStatsOverview::class,
        ];
    }
}
