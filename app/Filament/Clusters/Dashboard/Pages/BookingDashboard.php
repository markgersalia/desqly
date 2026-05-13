<?php

namespace App\Filament\Clusters\Dashboard\Pages;

use App\Filament\Clusters\Dashboard\DashboardCluster;
use App\Filament\Resources\Bookings\Widgets\BookingStats;
use App\Filament\Widgets\CalendarWidget;
use App\Models\Branch;
use App\Models\Company;
use App\Services\BusinessSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class BookingDashboard extends BaseDashboard
{


    use HasFiltersForm;

    protected string $view = 'filament.clusters.dashboard.pages.booking-dashboard';
    // protected static ?string $slug = 'dashboard';
    // protected static ?string $cluster = DashboardCluster::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;

    protected static UnitEnum|string|null $navigationGroup = 'Reports & Analytics';
    protected static ?int $navigationSort = -19;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon(Heroicon::ArrowPath)
                ->action(fn() => $this->dispatch('refresh-page')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BookingStats::class,
            CalendarWidget::class,
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        $tenant = Filament::getTenant();
        $company = $tenant instanceof Company ? $tenant : null;

        if (! app(BusinessSettings::class)->usesBranches($company)) {
            return $schema->components([]);
        }

        return $schema
            ->components([
                Select::make('branch_id')
                    ->label('Branch')
                    ->searchable()
                    ->preload()
                    ->placeholder('All branches')
                    ->options(function (): array {
                        $tenant = Filament::getTenant();

                        $query = Branch::query()->orderBy('name');

                        if ($tenant instanceof Company) {
                            $query->where('company_id', $tenant->getKey());
                        }

                        return $query->pluck('name', 'id')->toArray();
                    }),
            ]);
    }
}
