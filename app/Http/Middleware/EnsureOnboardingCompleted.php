<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\BusinessSettings;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    public function __construct(private BusinessSettings $businessSettings)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('web')->check()) {
            return $next($request);
        }

        $tenant = Filament::getTenant();

        if (! $tenant instanceof Company) {
            $tenant = auth('web')->user()?->company;
        }

        if (! $tenant instanceof Company) {
            return $next($request);
        }

        $isOnboardingRoute = $request->routeIs('filament.admin.pages.onboarding');
        $isLogoutRoute = $request->routeIs('filament.admin.auth.logout');
        $isTenantSetupRoute = $request->routeIs('filament.admin.tenant.registration') || $request->routeIs('filament.admin.tenant.profile') || $request->routeIs('filament.admin.tenant.billing');

        $isComplete = $this->businessSettings->isOnboardingComplete($tenant);

        if (! $isComplete && ! $isOnboardingRoute && ! $isLogoutRoute && ! $isTenantSetupRoute) {
            return $this->redirectWithOptionalTenant('filament.admin.pages.onboarding', $tenant);
        }

        if ($isComplete && $isOnboardingRoute) {
            return $this->redirectWithOptionalTenant('filament.admin.pages.dashboard', $tenant);
        }

        return $next($request);
    }

    private function redirectWithOptionalTenant(string $routeName, Company $tenant): Response
    {
        try {
            return redirect()->route($routeName, filament_tenant_route_params($tenant));
        } catch (UrlGenerationException $e) {
            return redirect()->route($routeName);
        }
    }
}
