<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Filament\SuperAdmin\Support\SalesFilterState;
use App\Models\BookingPayment;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SalesTrendChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Sales Trend';

    protected ?string $description = 'Daily paid sales for completed bookings';

    protected function getData(): array
    {
        $salesByDate = SalesFilterState::applySalesScope(
            BookingPayment::query(),
            $this->pageFilters,
        )
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $start = SalesFilterState::periodStart($this->pageFilters)->copy()->startOfDay();
        $end = now()->startOfDay();

        $labels = [];
        $points = [];

        while ($start->lte($end)) {
            $dateKey = $start->toDateString();
            $labels[] = $start->format('M d');
            $points[] = (float) ($salesByDate[$dateKey] ?? 0);
            $start->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $points,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
