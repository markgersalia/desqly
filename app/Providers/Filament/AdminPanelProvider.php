<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureOnboardingCompleted;
use App\Filament\Widgets\CalendarWidget;
use App\Filament\Widgets\RevenueWidget;
use App\Filament\Widgets\Sales;
use App\Filament\Widgets\StatsOverview;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filafly\Themes\Brisk\BriskTheme;
use Filament\Enums\DatabaseNotificationsPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin') 
            ->colors([
                'primary'=>Color::Blue
            ])
            // ->breadCrumbs(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Dashboard::class,
            ])
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
                StatsOverview::class,
                RevenueWidget::class,
                CalendarWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class, 
                SubstituteBindings::class,
                EnsureOnboardingCompleted::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ]) 
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->plugins([
                FilamentShieldPlugin::make(), // ✅ register plugin
                // BriskTheme::make()
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->subNavigationPosition(SubNavigationPosition::End)
            ->unsavedChangesAlerts()  
            ->brandName(config('app.name'))
            ->profile()
            ->spa()
            ->font('Poppins')
            ->path('admin')
            ->login()
            ->topbar(true)
            ->authGuard('web')
            ->globalSearch(false)
            // ->topNavigation(true)
            
            ->databaseNotifications()
            ->databaseNotifications(position: DatabaseNotificationsPosition::Sidebar)
            // ->brandLogo(asset('images/dark-logo.png'))
            // ->brandLogoHeight('50px')
            // ->favicon(asset('images/logo.jpg'))  

            ;
    }
}
