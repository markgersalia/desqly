<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Filament\SuperAdmin\Widgets\CompanySalesBreakdownWidget;
use App\Filament\SuperAdmin\Widgets\SalesOverviewWidget;
use App\Filament\SuperAdmin\Widgets\SalesTrendChartWidget;
use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_ids')
                    ->label('Companies')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn (): array => Company::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->default(fn (): array => Company::query()->pluck('id')->all()),
                Select::make('period')
                    ->label('Period')
                    ->options([
                        '7d' => 'Last 7 days',
                        '30d' => 'Last 30 days',
                        '90d' => 'Last 90 days',
                    ])
                    ->default('30d')
                    ->required(),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            SalesOverviewWidget::class,
            SalesTrendChartWidget::class,
            CompanySalesBreakdownWidget::class,
        ];
    }
}

