<?php

namespace App\Providers\Filament;

use App\Filament\Billing\ManualCompanyBillingProvider;
use App\Filament\Clusters\Dashboard\Pages\BookingDashboard as PagesBookingDashboard;
use App\Filament\Pages\BookingDashboard;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\SalesDashboard;
use App\Filament\Pages\Tenancy\EditCompanyProfile;
use App\Filament\Pages\Tenancy\RegisterCompany;
use App\Filament\Widgets\CalendarJsWidget;
use App\Filament\Widgets\CalendarWidget;
use App\Filament\Widgets\CompanySubscriptionStatusWidget;
use App\Filament\Widgets\CustomerGrowthWidget;
use App\Filament\Widgets\RevenueWidget;
use App\Filament\Widgets\RevenueTrendChartWidget;
use App\Filament\Widgets\StatsOverview;
use App\Http\Middleware\ApplyTenantBusinessSettings;
use App\Http\Middleware\EnsureOnboardingCompleted;
use App\Http\Middleware\EnsureTenantWriteAccess;
use App\Models\Company;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Caresome\FilamentAuthDesigner\AuthDesignerPlugin;
use Caresome\FilamentAuthDesigner\Data\AuthPageConfig;
use Caresome\FilamentAuthDesigner\Enums\MediaPosition;
use Filament\Enums\DatabaseNotificationsPosition;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
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
                'primary' => Color::hex('#2563FF'),
                'success' => Color::hex('#14B8A6'),
                'warning' => Color::hex('#F59E0B'),
                'danger' => Color::hex('#EF4444'),
                'gray' => Color::Slate,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                PagesBookingDashboard::class,
            ])
            ->widgets([
                // CompanySubscriptionStatusWidget::class,
                StatsOverview::class,
                RevenueWidget::class,
                // RevenueTrendChartWidget::class,
                // CustomerGrowthWidget::class,
                // CalendarJsWidget::class,
                CalendarWidget::class
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                // EnsureOnboardingCompleted::class,
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

                AuthDesignerPlugin::make()
                    ->defaults(
                        fn($config) => $config
                            ->media(asset('images/auth-bg.jpg'))
                            ->mediaPosition(MediaPosition::Left)
                            // ->blur(10)
                    )
                    ->login() 
                    ->passwordReset()
                    ->emailVerification()
                    ->themeToggle()
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->subNavigationPosition(SubNavigationPosition::End)
            ->unsavedChangesAlerts()
            // ->brandName(config('app.name'))
            ->profile()
            ->spa()

            // ->font('Manrope')
            ->font('Plus Jakarta Sans')
            ->path('admin')
            ->tenant(Company::class)
            ->tenantRoutePrefix('dashboard')
            // ->tenantRegistration(RegisterCompany::class)
            ->tenantProfile(EditCompanyProfile::class)
            // ->tenantBillingProvider(new ManualCompanyBillingProvider())
            // ->searchableTenantMenu(false)
            // ->requiresTenantSubscription()
            // ->tenantBillingRouteSlug('billing')
            // ->login()
            ->topbar(false)
            ->authGuard('web')
            ->maxContentWidth('full')
            // ->sidebarWidth('280px')
            ->globalSearch(true)
            ->brandLogo(asset('images/logo.png'))
            ->darkModeBrandLogo(asset('images/logo-dark.png'))
            // ->brandLogo(fn () => view('filament.admin.logo'))
            // ->darkModeBrandLogo(fn () => view('filament.admin.logo'))
            ->brandLogoHeight('3.5rem')
            ->databaseNotifications()


            ->databaseTransactions()
            // ->maxContentWidth(Width::Full)
            ->breadcrumbs(true)
        ->homeUrl(fn () => PagesBookingDashboard::getUrl(tenant: auth()->user()->currentTeam));
            // ->databaseNotifications(position: DatabaseNotificationsPosition::Sidebar);
        ;
    }
}
