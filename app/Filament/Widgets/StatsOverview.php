<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsOverview extends StatsOverviewWidget
{
    function formatNumberShort($number)
    {
        if ($number >= 1000000) {
            return number_format($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return number_format($number / 1000, 1) . 'K';
        } else {
            return number_format($number, 2);
        }
    }

    protected function getStats(): array
    {  
        $completedBooking = Booking::query()->completed()->count();  

        $bookingChart = $this->generateChartData(
            Booking::query()
                ->completed()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray()
        );

        $customerChart = $this->generateChartData(
            Customer::query()
                ->whereHas('bookings', fn($q) => $q->confirmed())
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray()
        );

        return [

            Stat::make('Total Bookings', Booking::query()->count())
                // ->chart($bookingChart)
                ->description($completedBooking . ' completed')
                ->descriptionIcon('heroicon-o-calendar-days'),
                // ->color('primary'),


            Stat::make('Today’s Bookings', $this->todayBookings())
                ->description('All bookings scheduled today'),

            Stat::make('Pending Payments', $this->pendingPayments())
                ->description('Awaiting payment'),
                

            Stat::make('Customers', Customer::query()->count())
                // ->chart($customerChart)
                ->description(Customer::whereHas('bookings', fn($q) => $q->confirmed())->count() . ' has active booking')
                ->descriptionIcon('heroicon-o-users')
                // ->color('primary'),
                
        ];
    }


    protected function todayBookings(): int
    {
        return Booking::whereDate('start_time', Carbon::today())->count();
    }

    protected function pendingPayments(): int
    {
        return Booking::where('payment_status', 'pending')->count();
    }

    private function generateChartData(array $rawData, int $days = 7): array
    {
        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $chartData[] = isset($rawData[$date]) ? (float) $rawData[$date] : 0;
        }
        return $chartData;
    }
}
