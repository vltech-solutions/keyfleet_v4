<?php

namespace App\Livewire;

use Omnia\LivewireCalendar\LivewireCalendar;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Models\Booking;
use App\Filament\Resources\BookingResource;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Cache;

class BookingCalendar extends LivewireCalendar
{
    public ?int $carId = null;

    public $monthGrid;

    public function mount(
        $initialYear = null,
        $initialMonth = null,
        $weekStartsAt = null,
        $calendarView = null,
        $dayView = null,
        $eventView = null,
        $dayOfWeekView = null,
        $dragAndDropClasses = null,
        $beforeCalendarView = null,
        $afterCalendarView = null,
        $pollMillis = null,
        $pollAction = null,
        $dragAndDropEnabled = true,
        $dayClickEnabled = true,
        $eventClickEnabled = true,
        $extras = []
    ) {
        parent::mount(
            $initialYear,
            $initialMonth,
            $weekStartsAt,
            $calendarView,
            $dayView,
            $eventView,
            $dayOfWeekView,
            $dragAndDropClasses,
            $beforeCalendarView,
            $afterCalendarView,
            $pollMillis,
            $pollAction,
            $dragAndDropEnabled,
            $dayClickEnabled,
            $eventClickEnabled,
            $extras
        );

        // Initialize your monthGrid if needed
        $this->monthGrid = $this->generateMonthGrid($this->startsAt);
    }


    protected function generateMonthGrid($startsAt)
    {
        $startOfMonth = $startsAt->copy()->startOfMonth();
        $endOfMonth = $startsAt->copy()->endOfMonth();
        
        $grid = collect();
        $week = [];

        // Force start of week to Sunday (0) and end to Saturday (6)
        $firstDayOfGrid = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $lastDayOfGrid = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        for ($date = $firstDayOfGrid; $date->lte($lastDayOfGrid); $date->addDay()) {
            $week[] = $date->copy();
            
            // Kapag Saturday na, itong week na ito ay tapos na.
            if ($date->isSaturday()) {
                $grid->push($week);
                $week = [];
            }
        }

        return $grid;
    }


    public function events(): Collection
    {
        $cacheKey = "events_for_car_" . Filament::getTenant()->id;

        $cached = Cache::get($cacheKey);

        if ($cached) {
            $collection = collect($cached);

            if ($this->carId) {
                $collection = $collection->where('car_id', $this->carId);
            }

            return $collection;
        }

        $query = Booking::query()->where('status','approved');
        if ($this->carId) {
            $query->where('car_id', $this->carId);
        }

        $fresh = $query->with('car')->get()->flatMap(function (Booking $model) {
            if ($model->car?->deleted_at !== null) {
                return collect(); 
            }

            $start = Carbon::parse($model->start_datetime)->startOfDay();
            $end = Carbon::parse($model->end_datetime)->startOfDay();
            $dates = collect();

            while ($start->lte($end)) {
                $dates->push([
                    'id' => $model->id,
                    'image' => $model->car->image,
                    'car_name' => $model->car->name,
                    'renter_name' => $model->renter_name,
                    'car_id' => $model->car_id,
                    'date' => $start->copy(),
                ]);
                $start->addDay();
            }

            return $dates;
        });

        Cache::put($cacheKey, $fresh, now()->addMinutes(10));

        return $fresh;
    }

    public function goToNextMonth()
    {
        $this->startsAt = $this->startsAt->copy()->addMonth()->startOfMonth();
        $this->monthGrid = $this->generateMonthGrid($this->startsAt);
    }

    public function goToPreviousMonth()
    {
        $this->startsAt = $this->startsAt->copy()->subMonth()->startOfMonth();
        $this->monthGrid = $this->generateMonthGrid($this->startsAt);
    }

    // public function goToNextMonth()
    // {
    //     $this->startsAt = $this->startsAt->copy()->addMonth()->startOfMonth();

    //     $startOfMonth = $this->startsAt->copy()->startOfMonth();
    //     $endOfMonth = $this->startsAt->copy()->endOfMonth();
    //     $grid = collect();
    //     $week = [];

    //     for ($date = $startOfMonth->startOfWeek(); $date->lte($endOfMonth->endOfWeek()); $date->addDay()) {
    //         $week[] = $date->copy();
    //         if ($date->isSunday()) {
    //             $grid->push($week);
    //             $week = [];
    //         }
    //     }

    //     $this->monthGrid = $grid;

    // }

    // public function goToPreviousMonth()
    // {
    //     $this->startsAt = $this->startsAt->copy()->subMonth()->startOfMonth();

    //      $startOfMonth = $this->startsAt->copy()->startOfMonth();
    //     $endOfMonth = $this->startsAt->copy()->endOfMonth();
    //     $grid = collect();
    //     $week = [];

    //     for ($date = $startOfMonth->startOfWeek(); $date->lte($endOfMonth->endOfWeek()); $date->addDay()) {
    //         $week[] = $date->copy();
    //         if ($date->isSunday()) {
    //             $grid->push($week);
    //             $week = [];
    //         }
    //     }

    //     $this->monthGrid = $grid;
    // }

    public function onEventClick($booking)
    {
        return $this->redirect(BookingResource::getUrl('edit', ['record' => $booking]));
    }

}
