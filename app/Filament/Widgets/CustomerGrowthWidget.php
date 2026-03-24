<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class CustomerGrowthWidget extends ChartWidget
{
    use HasWidgetShield;
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 1;

    protected ?string $heading = 'Customer Growth';

    protected ?string $description = null;

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $endMonth = now()->startOfMonth();
        $startMonth = $endMonth->copy()->subMonths(11)->startOfMonth();

        $countsByMonth = $this->baseQuery()
            ->whereBetween('created_at', [$startMonth, $endMonth->copy()->endOfMonth()])
            ->get(['created_at'])
            ->groupBy(fn (Customer $customer): string => Carbon::parse($customer->created_at)->format('Y-m'))
            ->map(fn ($group): int => $group->count())
            ->all();

        $labels = [];
        $points = [];
        $cursor = $startMonth->copy();

        while ($cursor->lte($endMonth)) {
            $monthKey = $cursor->format('Y-m');
            $labels[] = $cursor->format('M Y');
            $points[] = $countsByMonth[$monthKey] ?? 0;
            $cursor->addMonth();
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Customers',
                    'data' => $points,
                    'fill' => true,  
                    'pointRadius' => 3,
                    'pointHoverRadius' => 4,
                    'tension' => 0.35,
                    'borderWidth' => 2,
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
                    'display' => true,
                    'position' => 'bottom',
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
                    'grid' => [
                        'color' => 'rgba(148, 163, 184, 0.25)',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return Builder<\App\Models\Customer>
     */
    protected function baseQuery(): Builder
    {
        $companyId = Filament::getTenant()?->getKey() ?? auth('web')->user()?->company_id;

        return Customer::query()
            ->when($companyId, fn (Builder $query) => $query->where('company_id', $companyId));
    }
}
