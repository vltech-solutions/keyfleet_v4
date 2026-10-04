<?php

namespace App\Livewire;

use App\Filament\Resources\BookingResource;
use App\Models\Car;
use App\Models\Source;
use App\Services\CalendarDataService;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Omnia\LivewireCalendar\LivewireCalendar;

class BookingCalendar extends LivewireCalendar
{
    public ?int $carId = null;

    public ?int $sourceId = null;

    public string $status = '';

    public string $viewMode = 'visual';

    public string $calendarScope = 'month';

    public ?string $selectedDate = null;

    public ?int $selectedBookingId = null;

    public bool $showDayPanel = false;

    public string $calendarFeedUrl = '';

    public array $carOptions = [];

    public array $sourceOptions = [];

    public array $statusOptions = [];

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
            false,
            true,
            true,
            $extras
        );

        $this->calendarView = 'livewire.enhanced-booking-calendar';

        $company = Filament::getTenant();

        abort_unless(
            $company &&
            (int) $company->id === (int) auth()->user()?->company_id,
            403
        );

        $this->viewMode = session('calendar.view_mode', 'visual');

        $this->carOptions = Car::query()
            ->where('company_id', $company->id)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'brand', 'model', 'plate_number'])
            ->map(fn (Car $car): array => [
                'value' => (string) $car->id,
                'label' => $car->name,
                'description' => collect([
                    trim(($car->brand ?? '').' '.($car->model ?? '')),
                    $car->plate_number,
                ])->filter()->implode(' - '),
            ])
            ->values()
            ->all();

        $this->sourceOptions = Source::query()
            ->where('company_id', $company->id)
            ->orderBy('source')
            ->pluck('source', 'id')
            ->all();

        $this->statusOptions = app(CalendarDataService::class)
            ->statuses($company)
            ->all();
    }

    public function events(): Collection
    {
        $company = Filament::getTenant();

        abort_unless($company, 403);

        $from = Carbon::instance(
            $this->gridStartsAt->copy()
        );

        $to = Carbon::instance(
            $this->gridEndsAt->copy()
        );

        return app(CalendarDataService::class)->events(
            $company,
            $from,
            $to,
            [
                'car_id' => $this->carId,
                'source_id' => $this->sourceId,
                'status' => $this->status,
            ],
        );
    }

    public function setViewMode(string $mode): void
    {
        abort_unless(
            in_array($mode, ['visual', 'operations'], true),
            422
        );

        $this->viewMode = $mode;

        session([
            'calendar.view_mode' => $mode,
        ]);
    }

    public function setCalendarScope(string $scope): void
    {
        abort_unless(
            in_array($scope, ['month', 'week', 'day'], true),
            422
        );

        $this->calendarScope = $scope;

        $this->selectedDate ??= now(
            config('app.timezone')
        )->toDateString();

        if ($scope !== 'month') {
            $this->syncCalendarMonth(Carbon::parse($this->selectedDate, config('app.timezone')));
        }

        $this->showDayPanel = $scope === 'day';
    }

    public function clearFilters(): void
    {
        $this->reset(
            'carId',
            'sourceId',
            'status'
        );
    }

    public function goToPreviousPeriod(): void
    {
        $this->moveCalendarPeriod(-1);
    }

    public function goToNextPeriod(): void
    {
        $this->moveCalendarPeriod(1);
    }

    public function goToToday(): void
    {
        parent::goToCurrentMonth();

        $this->selectedDate = now(
            config('app.timezone')
        )->toDateString();
        $this->selectedBookingId = null;
        $this->showDayPanel = $this->calendarScope === 'day';
    }

    private function moveCalendarPeriod(int $direction): void
    {
        if ($this->calendarScope === 'month') {
            if ($direction < 0) {
                $this->goToPreviousMonth();
            } else {
                $this->goToNextMonth();
            }

            return;
        }

        $date = Carbon::parse(
            $this->selectedDate ?: $this->startsAt->toDateString(),
            config('app.timezone')
        );

        if ($this->calendarScope === 'week') {
            $date->addDays(7 * $direction);
        } else {
            $date->addDays($direction);
        }

        $this->selectedDate = $date->toDateString();
        $this->selectedBookingId = null;
        $this->showDayPanel = $this->calendarScope === 'day';

        $this->syncCalendarMonth($date);
    }

    private function syncCalendarMonth(Carbon $date): void
    {
        if ($this->startsAt->format('Y-m') === $date->format('Y-m')) {
            return;
        }

        $this->startsAt = $date->copy()->startOfMonth()->startOfDay();
        $this->endsAt = $date->copy()->endOfMonth()->startOfDay();

        $this->calculateGridStartsEnds();
    }

    public function goToNextMonth()
    {
        parent::goToNextMonth();

        $this->selectedDate = $this->startsAt->toDateString();
    }

    public function goToPreviousMonth()
    {
        parent::goToPreviousMonth();

        $this->selectedDate = $this->startsAt->toDateString();
    }

    public function goToCurrentMonth()
    {
        parent::goToCurrentMonth();

        $this->selectedDate = now(
            config('app.timezone')
        )->toDateString();
    }

    public function onDayClick($year, $month, $day)
    {
        $this->selectedDate = Carbon::create(
            $year,
            $month,
            $day,
            0,
            0,
            0,
            config('app.timezone')
        )->toDateString();

        $this->selectedBookingId = null;
        $this->showDayPanel = true;
    }

    public function onEventClick($eventId, ?string $date = null)
    {
        $this->selectedBookingId = (int) $eventId;

        $this->selectedDate = $date ?: $this->selectedDate;

        $this->showDayPanel = true;
    }

    public function closeDayPanel(): void
    {
        $this->showDayPanel = false;
        $this->selectedBookingId = null;
    }

    public function generateCalendarFeed(): void
    {
        $user = auth()->user();

        abort_unless(
            $user?->hasPermission('calendar.view'),
            403
        );

        $token = Str::random(64);

        $user->forceFill([
            'calendar_feed_token_hash' => hash('sha256', $token),
            'calendar_feed_token_generated_at' => now(),
        ])->saveQuietly();

        $this->calendarFeedUrl = route(
            'calendar.feed',
            [
                'user' => $user->id,
                'token' => $token,
            ]
        );
    }

    public function revokeCalendarFeed(): void
    {
        $user = auth()->user();

        abort_unless($user, 403);

        $user->forceFill([
            'calendar_feed_token_hash' => null,
            'calendar_feed_token_generated_at' => null,
        ])->saveQuietly();

        $this->calendarFeedUrl = '';
    }

    public function render()
    {
        $company = Filament::getTenant();

        abort_unless($company, 403);

        return parent::render()->with([
            'company' => $company,
            'bookingEditBaseUrl' => BookingResource::getUrl('index'),
            'newBookingUrl' => BookingResource::getUrl('create'),
        ]);
    }
}
