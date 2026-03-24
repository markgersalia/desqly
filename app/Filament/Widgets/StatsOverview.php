<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Customer;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $branchId = $this->getBranchId();

        $bookingsQuery = $this->bookingsQuery($branchId);
        $totalBookings = (clone $bookingsQuery)->count();
        $completedBookings = (clone $bookingsQuery)->completed()->count();
        $todayBookings = (clone $bookingsQuery)->whereDate('start_time', Carbon::today())->count();
        $pendingPayments = (clone $bookingsQuery)->where('payment_status', 'pending')->count();

        $customersQuery = $this->customersQuery($branchId);
        $customersCount = (clone $customersQuery)->count();
        $customersWithActiveBooking = (clone $customersQuery)
            ->whereHas('bookings', function (Builder $query) use ($branchId) {
                $query
                    ->confirmed()
                    ->when($branchId, fn (Builder $branchQuery) => $branchQuery->where('branch_id', $branchId));
            })
            ->count();

        return [
            Stat::make('Total Bookings', $totalBookings)
                ->description($completedBookings . ' completed')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Today\'s Bookings', $todayBookings)
                ->description('All bookings scheduled today')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('primary'),

            Stat::make('Pending Payments', $pendingPayments)
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make('Customers', $customersCount)
                ->description($customersWithActiveBooking . ' has active booking')
                ->descriptionIcon('heroicon-o-users'),
        ];
    }

    /**
     * @return Builder<\App\Models\Booking>
     */
    protected function bookingsQuery(?int $branchId = null): Builder
    {
        return Booking::query()
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId));
    }

    /**
     * @return Builder<\App\Models\Customer>
     */
    protected function customersQuery(?int $branchId = null): Builder
    {
        return Customer::query()
            ->when($branchId, function (Builder $query) use ($branchId) {
                $query->whereHas('bookings', fn (Builder $bookingQuery) => $bookingQuery->where('branch_id', $branchId));
            });
    }

    protected function getBranchId(): ?int
    {
        $branchId = data_get($this->pageFilters, 'branch_id');

        if (blank($branchId)) {
            return null;
        }

        $branchId = (int) $branchId;

        return $branchId > 0 ? $branchId : null;
    }
}
