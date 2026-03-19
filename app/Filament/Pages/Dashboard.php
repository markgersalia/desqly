<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\Company;
use App\Services\BusinessSettings;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static bool $isDiscovered = false;

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
