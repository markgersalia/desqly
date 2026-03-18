<?php

namespace App\Filament\Billing;

use App\Filament\Pages\Tenancy\CompanyBilling;
use App\Http\Middleware\RedirectIfTenantNotSubscribed;
use Filament\Billing\Providers\Contracts\BillingProvider;
use Illuminate\Support\Facades\Route;

class ManualCompanyBillingProvider implements BillingProvider
{
    public function getRouteAction(): string | \Closure | array
    {
        return CompanyBilling::class;
    }

    public function getSubscribedMiddleware(): string
    {
        return RedirectIfTenantNotSubscribed::class;
    }
}
