<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Imports\ExpenseImport;
use App\Exports\ExpenseTemplateExport;
use App\Exports\ExpenseExport;
use App\Filament\Resources\ExpenseResource\Widgets\ExpenseOverview;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        $importAction = Action::make('importFromExcel')
            ->label('Import')
            ->button() 
            ->color('gray') 
            ->outlined()
            ->modalWidth('md')
            ->icon('heroicon-s-arrow-down-tray')
            ->form([
                FileUpload::make('file')
                    ->label('Excel File')
                    ->helperText('Note: Use dd/mm/YYYY format (e.g. 26/08/2023).')
                    ->required()
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/csv',
                    ])
                    ->storeFiles(false),
            ])
            ->action(function (array $data): void {
                $import = new ExpenseImport;
                Excel::import($import, $data['file']);

                if ($import->failures()->isNotEmpty()) {
                    foreach ($import->failures() as $failure) {
                        $row = $failure->row()+1;
                        Notification::make()
                            ->title("Row {$row} Error")
                            ->body(implode(', ', $failure->errors()))
                            ->danger()
                            ->duration(2500)
                            ->send();
                    }
                    return;
                }
                
                Notification::make()
                    ->title('Import Complete')
                    ->body('Expenses have been imported successfully.')
                    ->success()
                    ->send();
            });

        $actions = (auth()->user()->hasActiveSubscription()) ? 
            [
                CreateAction::make()
                    ->label('Add New')
                    ->modalWidth(MaxWidth::FourExtraLarge),
                // $importAction,
                // Action::make('downloadTemplate')
                //     ->label('Download Template')
                //     ->button() 
                //     ->color('gray') 
                //     ->outlined()
                //     ->tooltip('Download a blank template for importing expenses')
                //     ->icon('heroicon-s-document-arrow-down')
                //     ->action(fn () => Excel::download(new ExpenseTemplateExport, 'expense-template.xlsx')),
                Action::make('exportToExcel')
                    ->label('Export')
                    ->button()
                    ->color('gray')
                    ->outlined()
                    ->icon('heroicon-s-arrow-up-tray')
                    ->action(function ($livewire): BinaryFileResponse {
                        $query = $livewire->getFilteredTableQuery();

                        return Excel::download(new ExpenseExport($query), 'expenses.xlsx');
                    }),
            ] : [
                Action::make('exportToExcel')
                    ->label('Export')
                    ->button()
                    ->color('gray')
                    ->outlined()
                    ->icon('heroicon-s-arrow-up-tray')
                    ->action(function ($livewire): BinaryFileResponse {
                        $query = $livewire->getFilteredTableQuery();

                        return Excel::download(new ExpenseExport($query), 'expenses.xlsx');
                    }),
            ];

        return $actions;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ExpenseOverview::class
        ];
    }
}
