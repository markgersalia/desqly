<?php

namespace App\Filament\Widgets;

use App\Models\BookingPayment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class RevenueWidget extends StatsOverviewWidget
{
    use HasWidgetShield;
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;

    public function getColumns(): int | array
    {
        return 2;
    }

    public function formatNumberShort($number)
    {
        if ($number >= 1000000) {
            return number_format($number / 1000000, 1) . 'M';
        }

        if ($number >= 1000) {
            return number_format($number / 1000, 1) . 'K';
        }

        return number_format($number, 2);
    }

    protected function getStats(): array
    {
        $branchId = $this->getBranchId();

        $revenue = (clone $this->baseRevenueQuery($branchId))->sum('amount');

        $revenueThisMonth = (clone $this->baseRevenueQuery($branchId))
            ->whereHas('booking', function (Builder $query) use ($branchId) {
                $query
                    ->completed()
                    ->whereBetween('start_time', [now()->startOfMonth(), now()->endOfMonth()])
                    ->when($branchId, fn (Builder $branchQuery) => $branchQuery->where('branch_id', $branchId));
            })
            ->sum('amount');

        $overallRevenueFormatted = 'PHP ' . $this->formatNumberShort($revenue);
        $revenueThisMonthFormatted = 'PHP ' . $this->formatNumberShort($revenueThisMonth);

        $revenueChart = $this->generateChartData(
            (clone $this->baseRevenueQuery($branchId))
                ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray(),
        );

        $revenueChartThisMonth = $this->generateChartData(
            (clone $this->baseRevenueQuery($branchId))
                ->whereHas('booking', function (Builder $query) use ($branchId) {
                    $query
                        ->completed()
                        ->whereBetween('start_time', [now()->startOfMonth(), now()->endOfMonth()])
                        ->when($branchId, fn (Builder $branchQuery) => $branchQuery->where('branch_id', $branchId));
                })
                ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->toArray(),
        );

        return [
            Stat::make('All Time Revenue', $overallRevenueFormatted)
                ->chart($revenueChart)
                ->description('All-time confirmed payments')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),

            Stat::make('Revenue This Month', $revenueThisMonthFormatted)
                ->chart($revenueChartThisMonth)
                ->description('Payments this month')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('primary'),
        ];
    }

    /**
     * @return Builder<\App\Models\BookingPayment>
     */
    protected function baseRevenueQuery(?int $branchId = null): Builder
    {
        return BookingPayment::query()
            ->paid()
            ->whereHas('booking', function (Builder $query) use ($branchId) {
                $query
                    ->completed()
                    ->when($branchId, fn (Builder $branchQuery) => $branchQuery->where('branch_id', $branchId));
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
