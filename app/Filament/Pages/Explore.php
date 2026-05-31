<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Explore extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.explore';
    
    // Itago sa sidebar
    protected static bool $shouldRegisterNavigation = false;
}