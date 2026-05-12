<?php

namespace App\Filament\Clusters\Dashboard\Pages;

use App\Filament\Clusters\Dashboard\DashboardCluster;
use App\Filament\Resources\Customers\Widgets\CustomerStats;
use App\Filament\Widgets\CalendarWidget;
use App\Filament\Widgets\CustomerGrowthWidget;
use App\Filament\Widgets\RevenueTrendChartWidget;
use App\Filament\Widgets\RevenueWidget;
use Filament\Pages\Page;

class SalesDashboard extends Page
{
    protected string $view = 'filament.clusters.dashboard.pages.sales-dashboard';

    protected static ?string $cluster = DashboardCluster::class;

    protected function getHeaderWidgets(): array
    {
        return [
                CustomerStats::class,
                RevenueWidget::class,
                RevenueTrendChartWidget::class,
                CustomerGrowthWidget::class, 
        ];
    }
    
}
