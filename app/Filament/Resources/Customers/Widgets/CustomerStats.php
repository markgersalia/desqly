<?php

namespace App\Filament\Resources\Customers\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class CustomerStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Customers', Customer::count())
                ->description('All registered customers')
                ->color('primary'),

            Stat::make('New This Month', $this->newCustomersThisMonth())
                ->description('Joined this month')
                ->color('success'),

            Stat::make('Customers with Bookings', $this->customersWithBookings())
                ->description('With at least 1 booking')
                ->color('info'),

            Stat::make('Returning Customers', $this->returningCustomers())
                ->description('2+ bookings')
                ->color('warning'),

            Stat::make('Inactive Customers', $this->inactiveCustomers())
                ->description('No bookings yet')
                ->color('gray'),
        ];
    }

    protected function newCustomersThisMonth(): int
    {
        return Customer::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
    }

    protected function customersWithBookings(): int
    {
        return Customer::has('bookings')->count();
    }

    protected function returningCustomers(): int
    {
        return Customer::has('bookings', '>=', 2)->count();
    }

    protected function inactiveCustomers(): int
    {
        return Customer::doesntHave('bookings')->count();
    }
}
