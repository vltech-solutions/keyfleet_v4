<?php

namespace App\Filament\Admin\Pages;

use App\Services\AdminAnalyticsService;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Computed;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -100;

    protected static string $view = 'filament.admin.pages.dashboard';

    public string $period = 'this_month';

    public ?string $customStart = null;

    public ?string $customEnd = null;

    public function mount(): void
    {
        $this->customStart = now()->startOfMonth()->toDateString();
        $this->customEnd = now()->endOfMonth()->toDateString();
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    #[Computed]
    public function analytics(): array
    {
        return app(AdminAnalyticsService::class)->dashboard(
            $this->period,
            $this->customStart,
            $this->customEnd,
        );
    }

    public function reportUrl(string $report = 'revenue', array $extra = []): string
    {
        return AdminBusinessReport::getUrl(array_merge([
            'reportType' => $report,
            'period' => $this->period,
            'customStart' => $this->customStart,
            'customEnd' => $this->customEnd,
        ], $extra));
    }
}
