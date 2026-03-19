<?php

namespace App\Filament\SuperAdmin\Support;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

class SalesFilterState
{
    /**
     * @param  array<string, mixed> | null  $filters
     * @return array<int>
     */
    public static function companyIds(?array $filters): array
    {
        $companyIds = data_get($filters, 'company_ids');

        if (! is_array($companyIds) || $companyIds === []) {
            return [];
        }

        return collect($companyIds)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed> | null  $filters
     */
    public static function periodKey(?array $filters): string
    {
        $period = (string) data_get($filters, 'period', '30d');

        return in_array($period, ['7d', '30d', '90d'], true) ? $period : '30d';
    }

    /**
     * @param  array<string, mixed> | null  $filters
     */
    public static function periodDays(?array $filters): int
    {
        return match (self::periodKey($filters)) {
            '7d' => 7,
            '90d' => 90,
            default => 30,
        };
    }

    /**
     * @param  array<string, mixed> | null  $filters
     */
    public static function periodStart(?array $filters): \Carbon\Carbon
    {
        return now()->subDays(self::periodDays($filters))->startOfDay();
    }

    /**
     * @param  array<string, mixed> | null  $filters
     */
    public static function periodLabel(?array $filters): string
    {
        return 'Last ' . self::periodDays($filters) . ' days';
    }

    /**
     * @param  array<string, mixed> | null  $filters
     */
    public static function selectedCompanyCount(?array $filters): int
    {
        return self::companyQuery($filters)->count();
    }

    /**
     * @param  array<string, mixed> | null  $filters
     * @return Builder<Company>
     */
    public static function companyQuery(?array $filters): Builder
    {
        $companyIds = self::companyIds($filters);
        $query = Company::query();

        if ($companyIds !== []) {
            $query->whereIn('id', $companyIds);
        }

        return $query;
    }

    /**
     * @param  Builder<\App\Models\BookingPayment>  $query
     * @param  array<string, mixed> | null  $filters
     * @return Builder<\App\Models\BookingPayment>
     */
    public static function applySalesScope(Builder $query, ?array $filters, bool $applyPeriod = true): Builder
    {
        $companyIds = self::companyIds($filters);

        $query
            ->where('payment_status', 'paid')
            ->whereHas('booking', fn (Builder $bookingQuery) => $bookingQuery->where('status', 'completed'));

        if ($applyPeriod) {
            $query->where('created_at', '>=', self::periodStart($filters));
        }

        if ($companyIds !== []) {
            $query->whereIn('company_id', $companyIds);
        }

        return $query;
    }

    public static function formatCurrency(float $amount): string
    {
        return 'PHP ' . number_format($amount, 2);
    }
}
