<?php

namespace App\Filament\Widgets;

use App\Models\BookingPayment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueWidget extends StatsOverviewWidget
{
    use HasWidgetShield;

    public function getColumns(): int | array
    {
        return 2;
    }

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
        $revenue = BookingPayment::query()
            ->paid()
            ->whereHas('booking', fn($q) => $q->completed())
            ->sum('amount');

        $revenueThisMonth = BookingPayment::query()
            ->whereHas('booking', function ($q) {
                $q->completed()
                    ->whereBetween('start_time', [now()->startOfMonth(), now()->endOfMonth()]);
            })
            ->paid()
            ->sum('amount');

        $overAllRevenue = "₱".$this->formatNumberShort($revenue);
        $revenueThisMonth = "₱".$this->formatNumberShort($revenueThisMonth);

        $revenueChart = $this->generateChartData(
            BookingPayment::query()
                ->paid()
                ->whereHas('booking', fn($q) => $q->completed())
                ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray()
        );


        $revenueChartThisMonth = $this->generateChartData(
            BookingPayment::query()
                ->paid()
                ->whereHas('booking', function ($q) {
                    $q->completed()
                        ->whereBetween('start_time', [now()->startOfMonth(), now()->endOfMonth()]);
                })
                ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray()
        );

        return [
            Stat::make('All Time Revenue', $overAllRevenue)
                ->chart($revenueChart)
                ->description('All-time confirmed payments')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('primary'),

            Stat::make('Revenue This Month', $revenueThisMonth)
                ->chart($revenueChartThisMonth)
                ->description('Payments this month')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('primary'),

        ];
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
