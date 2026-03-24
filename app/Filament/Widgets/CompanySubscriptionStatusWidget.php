<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Throwable;

class CompanySubscriptionStatusWidget extends Widget
{
    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.company-subscription-status-widget';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $tenant = Filament::getTenant();
        $company = $tenant instanceof Company ? $tenant : null;

        $status = $this->normalizeStatus($company?->subscription_status);
        $nextRenewAt = $company?->current_period_ends_at ?? $company?->trial_ends_at;
        $nextRenewLabel = $nextRenewAt?->format('M d, Y h:i A') ?? 'N/A';

        $upgradeUrl = null;

        if ($company) {
            try {
                $upgradeUrl = Filament::getTenantBillingUrl(tenant: $company);
            } catch (Throwable $exception) {
                $upgradeUrl = null;
            }
        }

        return [
            'status' => $status,
            'planCode'=>$company->plan_code,
            'statusLabel' => $this->statusLabel($status),
            'statusColor' => $this->statusColor($status),
            'nextRenewLabel' => $nextRenewLabel,
            'upgradeUrl' => $upgradeUrl,
        ];
    }

    protected function normalizeStatus(?string $status): string
    {
        $status = (string) $status;

        return in_array($status, ['trialing', 'active', 'past_due', 'unpaid', 'canceled'], true)
            ? $status
            : 'unpaid';
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'trialing' => 'Trialing',
            'active' => 'Active',
            'past_due' => 'Past Due',
            'canceled' => 'Canceled',
            default => 'Unpaid',
        };
    }

    protected function statusColor(string $status): string
    {
        return match ($status) {
            'trialing' => 'info',
            'active' => 'success',
            'past_due' => 'warning',
            default => 'danger',
        };
    }
}
