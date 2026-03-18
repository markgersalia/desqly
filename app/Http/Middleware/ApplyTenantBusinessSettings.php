<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\BusinessSettings;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTenantBusinessSettings
{
    public function __construct(private readonly BusinessSettings $businessSettings)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        if ($tenant instanceof Company) {
            $this->businessSettings->applyRuntimeConfig($tenant);
        }

        return $next($request);
    }
}
