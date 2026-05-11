<?php

namespace App\Filament\Clusters\Booking\Pages;

use App\Filament\Clusters\Booking\BookingCluster;
use App\Filament\Widgets\CalendarWidget;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BookingCalendar extends Page
{
    protected string $view = 'filament.clusters.booking.pages.booking-calendar';

    // protected static ?string $cluster = BookingCluster::class;

    protected static ?string $title = 'Calendar';

    protected static ?string $navigationLabel = 'Calendar';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::CalendarDateRange;
    protected static UnitEnum|string|null $navigationGroup = 'Booking Management';

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
            CalendarWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
