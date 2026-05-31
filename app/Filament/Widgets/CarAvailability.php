<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Widgets\Widget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;
use App\Models\Car;
use App\Models\CarType;


class CarAvailability extends Widget implements HasForms
{
    use InteractsWithForms;
    protected static string $view = 'filament.widgets.car-availability';
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 2,
        'lg' => 2,
    ];
    protected static ?int $sort = 0;
    public $carType;
    public $dateTime;
    public $availableCars = [];
    public $showAvailableCarsModal = false;
    public $carTypeName;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('carType')
                    ->label('Car Type')
                    ->options(
                        ['' => 'All'] + CarType::query()
                        ->whereNotNull('car_type')
                        ->pluck('car_type', 'id')
                        ->toArray()
                    )
                    // ->searchable()
                    ->required(),
                DateTimePicker::make('dateTime')
                    ->native(true)
                    ->label('Date & Time')
                    ->seconds(false)
                    ->format('M d Y h:i A')
                    ->required()
            ]);
    }

    public function checkAvailability(): void
    {
        $this->validate([
            'carType' => 'nullable|exists:car_types,id', // allow null for "All"
            'dateTime' => 'required|date',
        ]);

        $query = Car::query();

        if ($this->carType) {
            $query->where('car_type_id', $this->carType);
            $this->carTypeName = CarType::find($this->carType)?->car_type ?? 'Cars';
        } else {
            $this->carTypeName = 'Cars';
        }

        $query->orderBy('car_type_id', 'asc');
        $cars = $query->get()
            ->filter(fn ($car) => Car::isAvailableAt($car->id, $this->dateTime));

        if ($cars->isEmpty()) {
            Notification::make()
                ->title('No cars available for the selected type and time.')
                ->danger()
                ->send();
            return;
        }

        $this->availableCars = $cars;
        $this->showAvailableCarsModal = true;
    }


}
