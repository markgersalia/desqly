<?php

use App\Filament\Widgets\CompanySubscriptionStatusWidget;
use App\Filament\Widgets\RevenueWidget;
use App\Filament\Widgets\StatsOverview;
use App\Models\Company;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-03-24 10:00:00'));
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

afterEach(function () {
    Filament::setTenant(null, true);
    Carbon::setTestNow();
});

/**
 * @return mixed
 */
function callSubscriptionProtectedMethod(object $object, string $method, array $arguments = [])
{
    $reflection = new ReflectionMethod($object, $method);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs($object, $arguments);
}

test('dashboard registers subscription widget after stats overview and before revenue widget', function () {
    $widgets = array_values(Filament::getPanel('admin')->getWidgets());

    $statsOverviewIndex = array_search(StatsOverview::class, $widgets, true);
    $subscriptionIndex = array_search(CompanySubscriptionStatusWidget::class, $widgets, true);
    $revenueWidgetIndex = array_search(RevenueWidget::class, $widgets, true);

    expect($statsOverviewIndex)->not()->toBeFalse();
    expect($subscriptionIndex)->not()->toBeFalse();
    expect($revenueWidgetIndex)->not()->toBeFalse();
    expect($subscriptionIndex)->toBe($statsOverviewIndex + 1);
    expect($subscriptionIndex)->toBe($revenueWidgetIndex - 1);
});

test('subscription widget maps status labels and renewal date precedence correctly', function () {
    $company = Company::factory()->create([
        'subscription_status' => 'active',
        'current_period_ends_at' => Carbon::parse('2026-05-01 09:00:00'),
        'trial_ends_at' => Carbon::parse('2026-04-01 09:00:00'),
    ]);

    Filament::setTenant($company, true);

    $widget = app(CompanySubscriptionStatusWidget::class);
    $viewData = callSubscriptionProtectedMethod($widget, 'getViewData');

    expect($viewData['status'])->toBe('active');
    expect($viewData['statusLabel'])->toBe('Active');
    expect($viewData['nextRenewLabel'])->toBe('May 01, 2026 09:00 AM');
});

test('subscription widget falls back to trial end date when period end is missing', function () {
    $company = Company::factory()->create([
        'subscription_status' => 'trialing',
        'current_period_ends_at' => null,
        'trial_ends_at' => Carbon::parse('2026-04-09 11:30:00'),
    ]);

    Filament::setTenant($company, true);

    $widget = app(CompanySubscriptionStatusWidget::class);
    $viewData = callSubscriptionProtectedMethod($widget, 'getViewData');

    expect($viewData['status'])->toBe('trialing');
    expect($viewData['statusLabel'])->toBe('Trialing');
    expect($viewData['nextRenewLabel'])->toBe('Apr 09, 2026 11:30 AM');
});

test('subscription widget shows N/A when both renew dates are missing', function () {
    $company = Company::factory()->create([
        'subscription_status' => 'past_due',
        'current_period_ends_at' => null,
        'trial_ends_at' => null,
    ]);

    Filament::setTenant($company, true);

    $widget = app(CompanySubscriptionStatusWidget::class);
    $viewData = callSubscriptionProtectedMethod($widget, 'getViewData');

    expect($viewData['statusLabel'])->toBe('Past Due');
    expect($viewData['nextRenewLabel'])->toBe('N/A');
});

test('subscription widget keeps canonical fallback for unknown statuses and renders upgrade link', function () {
    $company = Company::factory()->create([
        'subscription_status' => 'invalid_status',
        'current_period_ends_at' => Carbon::parse('2026-04-30 15:15:00'),
        'trial_ends_at' => null,
    ]);

    Filament::setTenant($company, true);

    $widget = app(CompanySubscriptionStatusWidget::class);
    $viewData = callSubscriptionProtectedMethod($widget, 'getViewData');

    expect($viewData['status'])->toBe('unpaid');
    expect($viewData['statusLabel'])->toBe('Unpaid');
    expect($viewData['upgradeUrl'])->not()->toBeNull();

    Livewire::test(CompanySubscriptionStatusWidget::class)
        ->assertSee('Subscription')
        ->assertSee('Next renew date')
        ->assertSee('Upgrade plan');
});
