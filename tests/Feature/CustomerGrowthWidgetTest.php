<?php

use App\Filament\Widgets\CalendarWidget;
use App\Filament\Widgets\CustomerGrowthWidget;
use App\Filament\Widgets\RevenueTrendChartWidget;
use App\Models\Company;
use App\Models\Customer;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-03-18 10:00:00'));
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

afterEach(function () {
    Carbon::setTestNow();
    Filament::setTenant(null, true);
});

/**
 * @return mixed
 */
function callCustomerGrowthProtected(object $object, string $method, array $arguments = [])
{
    $reflection = new ReflectionMethod($object, $method);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs($object, $arguments);
}

test('admin dashboard registers customer growth between revenue trend and calendar widgets', function () {
    $widgets = array_values(Filament::getPanel('admin')->getWidgets());

    $trendIndex = array_search(RevenueTrendChartWidget::class, $widgets, true);
    $customerGrowthIndex = array_search(CustomerGrowthWidget::class, $widgets, true);
    $calendarIndex = array_search(CalendarWidget::class, $widgets, true);

    expect($trendIndex)->not()->toBeFalse();
    expect($customerGrowthIndex)->not()->toBeFalse();
    expect($calendarIndex)->not()->toBeFalse();
    expect($customerGrowthIndex)->toBe($trendIndex + 1);
    expect($customerGrowthIndex)->toBe($calendarIndex - 1);
});

test('customer growth shows monthly new customers for last 12 months and ignores branch filter', function () {
    $tenantCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();

    Filament::setTenant($tenantCompany, true);

    $startMonth = now()->startOfMonth()->subMonths(11);

    // Tenant customers counted in growth chart.
    Customer::factory()->create([
        'company_id' => $tenantCompany->id,
        'created_at' => $startMonth->copy()->addDays(1),
        'updated_at' => $startMonth->copy()->addDays(1),
    ]);

    Customer::factory()->create([
        'company_id' => $tenantCompany->id,
        'created_at' => $startMonth->copy()->addMonths(4)->addDays(2),
        'updated_at' => $startMonth->copy()->addMonths(4)->addDays(2),
    ]);

    Customer::factory()->create([
        'company_id' => $tenantCompany->id,
        'created_at' => $startMonth->copy()->addMonths(4)->addDays(11),
        'updated_at' => $startMonth->copy()->addMonths(4)->addDays(11),
    ]);

    Customer::factory()->create([
        'company_id' => $tenantCompany->id,
        'created_at' => now()->copy()->subDays(3),
        'updated_at' => now()->copy()->subDays(3),
    ]);

    // Other company customer should never be included.
    Customer::factory()->create([
        'company_id' => $otherCompany->id,
        'created_at' => $startMonth->copy()->addMonths(4)->addDays(5),
        'updated_at' => $startMonth->copy()->addMonths(4)->addDays(5),
    ]);

    $widget = app(CustomerGrowthWidget::class);
    $widget->pageFilters = ['branch_id' => '999'];

    $dataWithBranch = callCustomerGrowthProtected($widget, 'getData');

    $widgetNoBranch = app(CustomerGrowthWidget::class);
    $widgetNoBranch->pageFilters = null;

    $dataWithoutBranch = callCustomerGrowthProtected($widgetNoBranch, 'getData');

    expect($dataWithBranch)->toBe($dataWithoutBranch);

    $labels = $dataWithBranch['labels'];
    $points = $dataWithBranch['datasets'][0]['data'];

    expect(count($labels))->toBe(12);
    expect(count($points))->toBe(12);

    $expectedLabels = [];
    $expectedPoints = array_fill(0, 12, 0);

    for ($i = 0; $i < 12; $i++) {
        $month = $startMonth->copy()->addMonths($i);
        $expectedLabels[] = $month->format('M Y');
    }

    // Start month = 1, month + 4 = 2, current month = 1.
    $expectedPoints[0] = 1;
    $expectedPoints[4] = 2;
    $expectedPoints[11] = 1;

    expect($labels)->toBe($expectedLabels);
    expect($points)->toBe($expectedPoints);

    Livewire::test(CustomerGrowthWidget::class)->assertSee('Customer Growth');
});
