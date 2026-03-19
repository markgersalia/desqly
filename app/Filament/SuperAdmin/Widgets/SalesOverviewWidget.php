<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Filament\SuperAdmin\Support\SalesFilterState;
use App\Models\BookingPayment;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverviewWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $periodLabel = SalesFilterState::periodLabel($this->pageFilters);

        $periodSalesQuery = SalesFilterState::applySalesScope(
            BookingPayment::query(),
            $this->pageFilters,
        );

        $totalSales = (float) (clone $periodSalesQuery)->sum('amount');
        $paidTransactions = (int) (clone $periodSalesQuery)->count();

        $selectedCompanies = SalesFilterState::selectedCompanyCount($this->pageFilters);
        $averageSalesPerCompany = $selectedCompanies > 0
            ? $totalSales / $selectedCompanies
            : 0.0;

        $companyScopeQuery = SalesFilterState::companyQuery($this->pageFilters);

        $activeSubscriptions = (clone $companyScopeQuery)
            ->where('is_active', true)
            ->whereIn('subscription_status', ['active', 'trialing'])
            ->count();

        $atRiskSubscriptions = (clone $companyScopeQuery)
            ->where('is_active', true)
            ->whereIn('subscription_status', ['past_due', 'unpaid'])
            ->count();

        return [
            Stat::make('Active Subscriptions', number_format($activeSubscriptions))
                ->description('Active or trialing companies'),
            Stat::make('At-Risk Subscriptions', number_format($atRiskSubscriptions))
                ->description('Past due or unpaid companies'),
            Stat::make('Total Sales', SalesFilterState::formatCurrency($totalSales))
                ->description($periodLabel),
            Stat::make('Paid Transactions', number_format($paidTransactions))
                ->description($periodLabel),
            Stat::make('Avg Sales / Company', SalesFilterState::formatCurrency($averageSalesPerCompany))
                ->description($periodLabel),
        ];
    }
}
