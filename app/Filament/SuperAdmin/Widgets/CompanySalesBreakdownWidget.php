<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Filament\SuperAdmin\Support\SalesFilterState;
use App\Models\Company;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class CompanySalesBreakdownWidget extends TableWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $companyIds = SalesFilterState::companyIds($this->pageFilters);
        $periodStart = SalesFilterState::periodStart($this->pageFilters);

        return $table
            ->heading('Company Sales Breakdown')
            ->query(
                Company::query()
                    ->when($companyIds !== [], fn (Builder $query) => $query->whereIn('id', $companyIds))
                    ->withSum([
                        'bookingPayments as sales_total' => fn (Builder $query) => $query
                            ->where('payment_status', 'paid')
                            ->where('created_at', '>=', $periodStart)
                            ->whereHas('booking', fn (Builder $bookingQuery) => $bookingQuery->where('status', 'completed')),
                    ], 'amount')
                    ->withCount([
                        'bookingPayments as paid_transactions_count' => fn (Builder $query) => $query
                            ->where('payment_status', 'paid')
                            ->where('created_at', '>=', $periodStart)
                            ->whereHas('booking', fn (Builder $bookingQuery) => $bookingQuery->where('status', 'completed')),
                    ])
            )
            ->defaultSort('sales_total', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Company')
                    ->searchable(),
                TextColumn::make('sales_total')
                    ->label('Sales')
                    ->formatStateUsing(fn ($state): string => SalesFilterState::formatCurrency((float) ($state ?? 0)))
                    ->sortable(),
                TextColumn::make('paid_transactions_count')
                    ->label('Paid Transactions')
                    ->sortable(),
            ]);
    }
}
