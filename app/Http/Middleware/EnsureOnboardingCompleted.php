<?php

namespace App\Http\Middleware;

use App\Services\BusinessSettings;
use Closure;
use Illuminate\Http\Request;
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

        $isOnboardingRoute = $request->routeIs('filament.admin.pages.onboarding');
        $isLogoutRoute = $request->routeIs('filament.admin.auth.logout');
        $isComplete = $this->businessSettings->isOnboardingComplete();

        if (! $isComplete && ! $isOnboardingRoute && ! $isLogoutRoute) {
            return redirect()->route('filament.admin.pages.onboarding');
        }

        if ($isComplete && $isOnboardingRoute) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        return $next($request);
    }
}
