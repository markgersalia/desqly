<?php

namespace App\Providers\Filament;

use App\Filament\Billing\ManualCompanyBillingProvider;
use App\Filament\Pages\Tenancy\EditCompanyProfile;
use App\Filament\Pages\Tenancy\RegisterCompany;
use App\Filament\Widgets\CalendarWidget;
use App\Filament\Widgets\RevenueWidget;
use App\Filament\Widgets\StatsOverview;
use App\Http\Middleware\ApplyTenantBusinessSettings;
use App\Http\Middleware\EnsureOnboardingCompleted;
use App\Http\Middleware\EnsureTenantWriteAccess;
use App\Models\Company;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
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
                'primary' => Color::Purple,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
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
            ->tenantMiddleware([
                ApplyTenantBusinessSettings::class,
                EnsureTenantWriteAccess::class,
            ], isPersistent: true)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->plugins([
                FilamentShieldPlugin::make(),
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
            ->tenant(Company::class, slugAttribute: 'slug')
            ->tenantRoutePrefix('company')
            ->tenantRegistration(RegisterCompany::class)
            ->tenantProfile(EditCompanyProfile::class)
            ->tenantBillingProvider(new ManualCompanyBillingProvider())
            ->searchableTenantMenu(false)
            ->login()
            ->topbar(true)
            ->authGuard('web')
            ->globalSearch(false)
            ->databaseNotifications()
            ->databaseNotifications(position: DatabaseNotificationsPosition::Sidebar);
    }
}
