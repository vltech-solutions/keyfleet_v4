<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AuthorizesTenantPage;

use Filament\Pages\Page;

class Calendar extends Page
{
    use AuthorizesTenantPage;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.pages.calendar';
}
