<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\CustomerRequirement;
use App\Models\RequirementTypes;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RequirementsRelationManager extends RelationManager
{
    protected static string $relationship = 'requirements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('path')
                    ->label('Upload File')
                    ->disk('s3')
                    ->directory('requirements')
                    ->visibility('private')
                    ->image()
                    ->required()
                    ->resize(50)
                    ->columnSpanFull(),

                    Select::make('requirement_type')
                        ->label('Requirement')
                        ->options(function () {
                            $customerId = $this->ownerRecord->id;
                            $companyEnabled = $this->ownerRecord->company->enabled_requirements ?? [];

                            if (! $customerId || empty($companyEnabled)) {
                                return RequirementTypes::query()
                                    ->whereIn('id', $companyEnabled)
                                    ->pluck('label', 'id') 
                                    ->toArray();
                            }

                            return RequirementTypes::query()
                                ->whereIn('id', $companyEnabled)   
                                ->pluck('label', 'id')             
                                ->toArray();
                        })
                    ->required()
                    ->columnSpanFull(),

                // Select::make('status')
                //     ->label('Status')
                //     ->options([
                //         'pending' => 'Pending',
                //         'approved' => 'Approved',
                //         'declined' => 'Declined',
                //     ])
                //     ->default('pending')
                //     ->required()
                //     ->columnSpanFull(),

                DatePicker::make('expiration')
                    ->label('Expiration')
                    ->withoutSeconds()
                    // ->native(false)
                    // ->required()
                    ->columnSpanFull()
                    
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('requirement_type')
            ->columns([
                Stack::make([
                    ImageColumn::make('path')
                        ->label('Uploaded File')
                        ->size(100)
                        ->getStateUsing(fn ($record) => $record->path 
                            ? Storage::disk('s3')->temporaryUrl(
                                    $record->path,
                                    now()->addMinutes(10)
                                )
                            : null
                    ),
                        // ->extraAttributes(['class' => 'w-24 h-24 ']),
                    TextColumn::make('requirementType.label')
                        ->label('Requirement Type')
                        ->badge(),

                    TextColumn::make('dates')
                        ->label('Dates')
                        ->html()
                        ->getStateUsing(function ($record) {
                            $uploaded = $record->date_uploaded
                                ? Carbon::parse($record->date_uploaded)->format('M d, Y')
                                : 'N/A';

                            $expiration = $record->expiration
                                ? Carbon::parse($record->expiration)->format('M d, Y')
                                : 'No Expiration';

                            return "
                                <div style='display: table; width: 100%;'>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; color: gray; width: 120px;'>Date Uploaded:</div>
                                        <div style='display: table-cell; padding-left: 5px;'>{$uploaded}</div>
                                    </div>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; color: gray; width: 120px;'>Expiration:</div>
                                        <div style='display: table-cell; padding-left: 5px;'>{$expiration}</div>
                                    </div>
                                </div>
                            ";
                        }),

                    // TextColumn::make('status')
                    //     ->label('Status')
                    //     ->badge()
                    //     ->colors([
                    //         'warning' => 'pending',
                    //         'success' => 'approved',
                    //         'danger'  => 'declined',
                    //     ])
                    //     ->formatStateUsing(fn ($state) => ucfirst($state)),
                ])
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add New')
                    ->modalWidth('md')
                    ->visible(function (RelationManager $livewire) {
                        // Get enabled requirements for this tenant
                        $enabled = Filament::getTenant()->enabled_requirements ?? [];

                        // Count how many unique requirement types the client already has
                        $existing = $livewire->ownerRecord->requirements()
                            ->whereIn('requirement_type', $enabled)
                            ->pluck('requirement_type')
                            ->unique()
                            ->count();

                        return $existing < count($enabled);
                    }),
            ])
            ->actions(
                auth()->user()->hasActiveSubscription()
                    ? [
                        Tables\Actions\ViewAction::make(),
                        Tables\Actions\EditAction::make(),
                        Action::make('download')
                            ->label('Download')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->action(function ($record): ?StreamedResponse {
                                $path = $record->path;

                                if (! $path || ! Storage::disk('s3')->exists($path)) {
                                    Notification::make()
                                        ->title('File not found')
                                        ->danger()
                                        ->send();
                                    return null;
                                }

                                // Stream the file directly from S3
                                return Storage::disk('s3')->download($path);
                            }),
                        Tables\Actions\DeleteAction::make(),
                    ]
                    : [
                        Tables\Actions\ViewAction::make(),
                        Action::make('download')
                            ->label('Download')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->action(function ($record): ?StreamedResponse {
                                $path = $record->path;

                                if (! $path || ! Storage::disk('s3')->exists($path)) {
                                    Notification::make()
                                        ->title('File not found')
                                        ->danger()
                                        ->send();
                                    return null;
                                }

                                // Stream the file directly from S3
                                return Storage::disk('s3')->download($path);
                            }),
                    ]
            )

            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

}
