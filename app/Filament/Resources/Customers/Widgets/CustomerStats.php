<?php

namespace App\Filament\Resources\Customers\Widgets;

use App\Models\Customer;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CustomerStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Customers', $this->baseQuery()->count())
                ->description('All registered customers'),
                // ->color('primary'),

            Stat::make('New This Month', $this->newCustomersThisMonth())
                ->description('Joined this month')
                ->descriptionIcon(Heroicon::PresentationChartLine)
                ->color('success'),

            Stat::make('Customers with Bookings', $this->customersWithBookings())
                ->description('With at least 1 booking')
                ->descriptionIcon(Heroicon::HandThumbUp)
                ->color('primary'),
 
            Stat::make('Inactive Customers', $this->inactiveCustomers())
                ->description('No bookings yet')
                ->descriptionIcon(Heroicon::ExclamationTriangle)
                ->color('danger'),
        ];
    }

    protected function newCustomersThisMonth(): int
    {
        return $this->baseQuery()
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
    }

    protected function customersWithBookings(): int
    {
        return $this->baseQuery()->has('bookings')->count();
    }

    protected function returningCustomers(): int
    {
        return $this->baseQuery()->has('bookings', '>=', 2)->count();
    }

    protected function inactiveCustomers(): int
    {
        return $this->baseQuery()->doesntHave('bookings')->count();
    }

    protected function baseQuery(): Builder
    {
        $tenantId = Filament::getTenant()?->getKey();

        return Customer::query()
            ->when($tenantId, fn (Builder $query) => $query->where('company_id', $tenantId));
    }
}
