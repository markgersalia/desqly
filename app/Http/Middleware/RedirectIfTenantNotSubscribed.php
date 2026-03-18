<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfTenantNotSubscribed
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Company || $tenant->isSubscribed()) {
            return $next($request);
        }

        if ($request->routeIs('filament.admin.tenant.billing')) {
            return $next($request);
        }

        $billingUrl = Filament::getTenantBillingUrl(tenant: $tenant);

        if ($billingUrl) {
            return redirect()->to($billingUrl);
        }

        return $next($request);
    }
}
