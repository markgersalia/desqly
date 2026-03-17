<?php

namespace App\Filament\Resources\Bookings\Widgets;

use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BookingStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make("Today's Bookings", $this->todayBookings())
                ->description('All bookings scheduled today'),

            Stat::make('Upcoming Bookings', $this->upcomingBookings())
                ->description('Confirmed & pending'),

            Stat::make('Pending Payments', $this->pendingPayments())
                ->description('Awaiting payment'),
        ];
    }

    protected function todayBookings(): int
    {
        return Booking::whereDate('start_time', Carbon::today())->count();
    }

    protected function upcomingBookings(): int
    {
        return Booking::where('start_time', '>', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
    }

    protected function pendingPayments(): int
    {
        return Booking::where('payment_status', 'pending')->count();
    }
}
