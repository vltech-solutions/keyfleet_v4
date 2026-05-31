<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Filament\Widgets\BookingStatsWidget;
use App\Models\Reservation;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Imports\BookingsImport;
use App\Exports\BookingTemplateExport;
use App\Exports\BookingExport;
use Filament\Notifications\Notification;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

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
                    ->helperText('Note: Use dd/mm/YYYY HH:mm format (e.g. 26/08/2023 13:00).')
                    ->required()
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/csv',
                    ])
                    ->storeFiles(false),
            ])
            ->action(function (array $data): void {
                $import = new BookingsImport;
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
                    ->body('Bookings have been imported successfully.')
                    ->success()
                    ->send();
            });
            
        $actions = (auth()->user()->hasActiveSubscription()) ? 
            [
                CreateAction::make()
                    ->label('Add New'),
                // $importAction,
//                 Action::make('downloadTemplate')
//                     ->label('Download Template')
//                     ->button() 
//                     ->color('gray') 
//                     ->outlined()
//                     ->tooltip('Download a blank template for importing bookings')
//                     ->icon('heroicon-s-document-arrow-down')
//                     ->action(fn () => Excel::download(new BookingTemplateExport, 'booking-template.xlsx')),
                 Action::make('exportToExcel')
                     ->label('Export')
                     ->button()
                     ->color('gray')
                     ->outlined()
                     ->icon('heroicon-s-arrow-up-tray')
                     ->action(function ($livewire): BinaryFileResponse {
                        //  Get the currently filtered query from the table
                         $query = $livewire->getFilteredTableQuery();

                        //  Pass it to your export class
                         return Excel::download(new BookingExport($query), 'bookings.xlsx');
                     }),

                
            ] : [
                Action::make('exportToExcel')
                    ->label('Export')
                    ->button()
                    ->color('gray')
                    ->outlined()
                    ->icon('heroicon-s-arrow-up-tray')
                    ->action(function ($livewire): BinaryFileResponse {
                        // Get the currently filtered query from the table
                        $query = $livewire->getFilteredTableQuery();

                        // Pass it to your export class
                        return Excel::download(new BookingExport($query), 'bookings.xlsx');
                    }),
            ];

        $company = Filament::getTenant();
        if($company->hasNonBasicPaidSubscription() || $company->hasActiveFreeSubscription()){
            $actions[] = Action::make('Reservations')
                    ->label('Reservations')
                    ->button()
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('success')
                    ->outlined()
                    ->tooltip('Reservations made using online booking form.')
                    ->badge(fn () => ($count = Reservation::where('status', 'pending')->count()) > 0 ? $count : null)
                    ->url(route('filament.app.resources.reservations.index', [
                        'tenant' => Filament::getTenant()?->slug, 
                    ]));
        }

        return $actions;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // BookingStatsWidget::class,

        ];
    }
}
