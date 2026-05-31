<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Filament\Pages\ContractBuilder;
use Rmsramos\Activitylog\ActivitylogPlugin;

use App\Filament\Pages\CompanyProfile;
use App\Filament\Pages\ReferralDashboard;
use App\Filament\Pages\BookingInspectionPage;
use App\Filament\Widgets\FinancialGraph;
use App\Http\Middleware\ApplyTenantScopes;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Facades\Filament;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Widgets;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Auth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Storage;
use App\Filament\Widgets\FinancialSummaryStats;
use App\Filament\Widgets\UpcomingBookings;
use App\Http\Middleware\ApplyTenantThemeColors;
use Filament\Navigation\UserMenuItem;
use App\Filament\Pages\SubscriptionOverview;
use App\Filament\Pages\UserProfile;
use App\Filament\Pages\ViewInspectionPage;
use App\Filament\Widgets\BookingGraph;
use App\Filament\Widgets\BookingSources;
use App\Filament\Widgets\BookingStatsWidget;
use App\Filament\Widgets\CarAvailability;
use App\Filament\Widgets\FundTypes;
use App\Filament\Widgets\TopBookedCarsChart;
use App\Models\Company;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->tenant(Company::class,slugAttribute: 'slug')
            // ->tenantDomain('{tenant:slug}.localhost')
            ->tenantProfile(CompanyProfile::class)
            ->id('app')
            ->path('app')
            ->login()
            ->brandName('KEYFLEET')
            ->brandLogo(fn () => view('components.brand'))
            ->colors([
                'primary' => '#0455da',
                'secondary' => '#042562',
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'black' => '#1f1f1f',
                'green' => Color::Green,
            ])
            ->viteTheme(['resources/css/app.css','resources/js/app.js'])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                SubscriptionOverview::class,
                UserProfile::class,
                BookingInspectionPage::class,
                ViewInspectionPage::class
            ])
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                BookingStatsWidget::class,
                CarAvailability::class,
                FinancialSummaryStats::class,
                FundTypes::class,
                UpcomingBookings::class,
                FinancialGraph::class,
                BookingGraph::class,
                BookingSources::class,
                TopBookedCarsChart::class
            ])
            // ->spa()
            // ->spaUrlExceptions(fn (): array => [
            //     url('/app/*/contract-builder'),
            //     url('/app/*/fleet-utilization-report'),
            //     url('/app/*/booking-inspection/*/*')
            // ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            // ->profile()
            // ->passwordReset()
            ->passwordReset(RequestPasswordReset::class) 
            // ->databaseNotifications()
            // ->databaseNotificationsPolling('30s')
            ->tenantMiddleware([
                ApplyTenantScopes::class,
                ApplyTenantThemeColors::class,
            ], isPersistent: true)
            ->authGuard('web')
            ->plugins([
                // ActivitylogPlugin::make(),
            ])
            // ->renderHook(
            //     'panels::sidebar.footer',
            //     fn () => view('subscription-warning'),
            // )
            ->renderHook(
                'panels::sidebar.footer',
                fn () => view('install-pwa'),
            )
            ->renderHook(
                'panels::body.start',
                fn () => view('filament.components.loading-screen'),
            )
            ->renderHook(
                'panels::head.start',
                fn () => view('pwa-head'),
            )
            ->renderHook(
                'panels::body.end',
                fn () => view('pwa-scripts'),
            )
            ->renderHook(
                'panels::body.end',
                fn (): string => auth()->check() 
                    ? view('filament.components.mobile-nav')->render() 
                    : '',
            )
            // ->renderHook(
            //     'panels::auth.login.form.after',
            //     fn () => view('filament.auth.google-button'),
            // )
            
            // ->favicon(Storage::url('keyfleet-icon.ico'))
            ->userMenuItems([
                UserMenuItem::make()
                    ->label('Profile')
                     ->url(fn (): string => UserProfile::getUrl())
                    ->icon('heroicon-o-user-circle')
                    ->sort(0),
                UserMenuItem::make()
                    ->label('My Subscription')
                     ->url(fn (): string => SubscriptionOverview::getUrl())
                    ->icon('heroicon-o-credit-card')
                    ->sort(1),
                UserMenuItem::make()
                    ->label('My Referral Program')
                    ->url(fn (): string => ReferralDashboard::getUrl())
                    ->icon('heroicon-o-trophy')
                    ->sort(3),
            ])
            ->navigationGroups([
                'Transactions',      
                'Fleet Management',  
            ])
            // ->topNavigation()
            // ->sidebarFullyCollapsibleOnDesktop(false)
            ;
    }
}
