<?php

namespace App\Filament\Clusters\Dashboard\Pages;

use App\Filament\Clusters\Dashboard\DashboardCluster;
use App\Filament\Resources\Bookings\Widgets\BookingStats;
use App\Filament\Widgets\CalendarWidget;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class BookingDashboard extends Page
{
    protected string $view = 'filament.clusters.dashboard.pages.booking-dashboard';
    protected static ?string $slug = 'dashboard';
    protected static ?string $cluster = DashboardCluster::class;

    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon(Heroicon::ArrowPath)
                ->action(fn () => $this->dispatch('refresh-page')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BookingStats::class,
            CalendarWidget::class,
        ];
    }

}
