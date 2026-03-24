<?php

namespace App\Filament\Widgets;

use App\Models\BookingPayment;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class RevenueTrendChartWidget extends ChartWidget
{
    use HasWidgetShield;
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 1;

    public ?string $filter = '30d';

    protected ?string $heading = 'Revenue Trend';

    protected ?string $description = 'Daily paid revenue for completed bookings';

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        return [
            '7d' => 'Last 7 days',
            '30d' => 'Last 30 days',
            '90d' => 'Last 90 days',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $periodKey = $this->resolvePeriodKey();
        $periodDays = $this->periodDays($periodKey);
        $branchId = $this->getBranchId();

        $end = now()->startOfDay();
        $start = (clone $end)->subDays($periodDays - 1)->startOfDay();

        $salesByDate = BookingPayment::query()
            ->paid()
            ->where('created_at', '>=', $start)
            ->whereHas('booking', function (Builder $query) use ($branchId) {
                $query
                    ->completed()
                    ->when($branchId, fn (Builder $branchQuery) => $branchQuery->where('branch_id', $branchId));
            })
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $labels = [];
        $points = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dateKey = $cursor->toDateString();
            $labels[] = $cursor->format('M d');
            $points[] = (float) ($salesByDate[$dateKey] ?? 0);
            $cursor->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $points,
                    'fill' => true,
                    // 'borderColor' => '#2dd4a0',
                    // 'backgroundColor' => 'rgba(45, 212, 160, 0.18)',
                    'borderWidth' => 2,
                    'tension' => 0.35,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function resolvePeriodKey(): string
    {
        $period = (string) ($this->filter ?? '30d');

        return in_array($period, ['7d', '30d', '90d'], true) ? $period : '30d';
    }

    protected function periodDays(string $period): int
    {
        return match ($period) {
            '7d' => 7,
            '90d' => 90,
            default => 30,
        };
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
