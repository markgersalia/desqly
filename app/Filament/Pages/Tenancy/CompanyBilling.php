<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Company;
use App\Services\BusinessSettings;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class CompanyBilling extends Page
{
    public const CANONICAL_SUBSCRIPTION_STATUSES = ['trialing', 'active', 'past_due', 'unpaid', 'canceled'];

    protected static bool $isDiscovered = false;

    protected string $view = 'filament.pages.tenancy.company-billing';

    public string $billingCycle = 'annual';

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public static function canAccess(): bool
    {
        $user = auth('web')->user();
        $tenant = Filament::getTenant();

        if (! $user || ! $tenant instanceof Company) {
            return false;
        }

        if ((int) $user->company_id !== (int) $tenant->getKey()) {
            return false;
        }

        if (! method_exists($user, 'hasRole')) {
            return false;
        }

        return $user->hasRole('Admin');
    }

    public function getTitle(): string | Htmlable
    {
        return '';
    }

    public function selectCycle(string $cycle): void
    {
        $this->billingCycle = in_array($cycle, ['monthly', 'annual'], true) ? $cycle : 'annual';
    }

    public function upgrade(string $targetTier): void
    {
        $company = $this->getTenantCompany();

        if (! $company || ! static::canAccess()) {
            abort(403);
        }

        $plans = $this->getPlans();

        if (! Arr::has($plans, $targetTier)) {
            Notification::make()
                ->title('Invalid plan selected.')
                ->danger()
                ->send();

            return;
        }

        $currentTier = $this->mapPlanCodeToTier($company->plan_code);
        $currentRank = $this->tierRank($currentTier);
        $targetRank = $this->tierRank($targetTier);

        if ($targetRank <= $currentRank) {
            Notification::make()
                ->title('Downgrades are handled by support.')
                ->body('Please contact support if you need to change to a lower plan.')
                ->warning()
                ->send();

            return;
        }

        $selectedCycle = in_array($this->billingCycle, ['monthly', 'annual'], true) ? $this->billingCycle : 'annual';
        $planCode = (string) data_get($plans, "{$targetTier}.plan_code");

        $currentPeriodEndsAt = $selectedCycle === 'monthly'
            ? Carbon::now()->addMonth()
            : Carbon::now()->addYear();

        $company->forceFill([
            'plan_code' => $planCode,
            'subscription_status' => 'active',
            'trial_ends_at' => null,
            'current_period_ends_at' => $currentPeriodEndsAt,
        ])->save();

        Notification::make()
            ->title('Plan updated successfully.')
            ->success()
            ->send();
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

    protected function getViewData(): array
    {
        $company = $this->getTenantCompany();
        $plans = $this->getPlans();
        $currentTier = $this->mapPlanCodeToTier($company?->plan_code);
        $currency = $this->resolveCurrency($company);
        $cycle = in_array($this->billingCycle, ['monthly', 'annual'], true) ? $this->billingCycle : 'annual';
        $companyName = trim((string) ($company?->name ?? 'Company')); 
        $status = $this->normalizeStatus($company?->subscription_status);
        $nextRenewAt = $company?->current_period_ends_at ?? $company?->trial_ends_at;
        $nextRenewLabel = $nextRenewAt?->format('M d, Y h:i A') ?? 'N/A';

        $cards = [];

        foreach ($plans as $tier => $plan) {
            $isCurrent = $tier === $currentTier;
            $targetRank = $this->tierRank($tier);
            $currentRank = $this->tierRank($currentTier);

            $actionLabel = 'Contact support';
            $actionState = 'contact';

            if ($isCurrent) {
                $actionLabel = 'Current Plan';
                $actionState = 'current';
            } elseif ($targetRank > $currentRank) {
                $actionLabel = "Upgrade to {$plan['name']}";
                $actionState = 'upgrade';
            }

            $cards[] = [
                ...$plan,
                'tier' => $tier,
                'is_current' => $isCurrent,
                'action_label' => $actionLabel,
                'action_state' => $actionState,
                'price_label' => $this->formatCurrency($currency, (float) $plan['prices'][$cycle]),
                'billing_label' => $cycle === 'monthly'
                    ? 'Per month, billed monthly'
                    : 'Per month, billed annually',
            ];
        }

        return [
            'billingCycle' => $cycle,
            'cards' => $cards,
            'currency' => $currency,
            'currentPlanName' => data_get($plans, "{$currentTier}.name", 'Free'),
            'companyAvatarUrl' => $this->resolveCompanyAvatarUrl($company),
            'companyName' => $companyName,
            'companyInitials' => $this->resolveCompanyInitials($companyName),
            
            'status' => $status,
            'planCode'=>$company->plan_code,
            'statusLabel' => $this->statusLabel($status),
            'statusColor' => $this->statusColor($status),
            'nextRenewLabel' => $nextRenewLabel,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function getPlans(): array
    {
        return [
            'free' => [
                'name' => 'Free',
                'plan_code' => 'starter',
                'description' => 'Basic tools to manage bookings and guests, perfect for small businesses.',
                'prices' => [
                    'monthly' => 0,
                    'annual' => 0,
                ],
                'highlighted' => false,
                'features' => [
                    'Access to all basic tools',
                    'Access to 0-10 rooms',
                    'Basic booking analytics',
                    'Basic accounting and HR system',
                    'Basic marketing tool',
                ],
            ],
            'pro' => [
                'name' => 'Pro',
                'plan_code' => 'pro',
                'description' => 'Advanced features for growing businesses with better analytics and control.',
                'prices' => [
                    'monthly' => 999,
                    'annual' => 799,
                ],
                'highlighted' => true,
                'badge' => 'Most Popular',
                'features' => [
                    'Access to 0-30 rooms',
                    'Access to point of sale',
                    'Channel manager connectivity',
                    'Custom analytics and reporting',
                    '24/7 live chat support',
                ],
            ],
            'premium' => [
                'name' => 'Premium',
                'plan_code' => 'enterprise',
                'description' => 'Comprehensive tools for enterprises, with advanced controls and support.',
                'prices' => [
                    'monthly' => 2999,
                    'annual' => 2399,
                ],
                'highlighted' => false,
                'features' => [
                    'Access to unlimited rooms',
                    'Point of sale and channel manager',
                    'Unlimited custom analytics',
                    'Unlimited customization options',
                    'Dedicated support manager',
                ],
            ],
        ];
    }

    protected function mapPlanCodeToTier(?string $planCode): string
    {
        return match ((string) $planCode) {
            'starter' => 'free',
            'growth', 'pro' => 'pro',
            'enterprise' => 'premium',
            default => 'free',
        };
    }

    protected function tierRank(?string $tier): int
    {
        return match ((string) $tier) {
            'free' => 1,
            'pro' => 2,
            'premium' => 3,
            default => 1,
        };
    }

    protected function getTenantCompany(): ?Company
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Company ? $tenant : null;
    }

    protected function resolveCurrency(?Company $company): string
    {
        $settings = app(BusinessSettings::class)->getSettings($company);
        $currency = strtoupper((string) data_get($settings, 'business.currency', 'PHP'));

        return $currency !== '' ? $currency : 'PHP';
    }

    protected function formatCurrency(string $currency, float $amount): string
    {
        return sprintf('%s %s', $currency, number_format($amount, 2));
    }

    protected function resolveCompanyAvatarUrl(?Company $company): ?string
    {
        if (! $company || blank($company->avatar)) {
            return null;
        }

        try {
            $avatarUrl = filament()->getTenantAvatarUrl($company);

            if (filled($avatarUrl)) {
                return $avatarUrl;
            }
        } catch (Throwable $exception) {
        }

        $fallback = $company->getFilamentAvatarUrl();

        return filled($fallback) ? $fallback : null;
    }

    protected function resolveCompanyInitials(string $companyName): string
    {
        $parts = Str::of($companyName)
            ->replaceMatches('/[^A-Za-z0-9 ]+/', ' ')
            ->squish()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => strtoupper(Str::substr($part, 0, 1)));

        $initials = $parts->implode('');

        return $initials !== '' ? $initials : 'CO';
    }
}


