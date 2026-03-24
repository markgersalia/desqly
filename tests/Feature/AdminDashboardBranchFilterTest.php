<?php

use App\Filament\Widgets\RevenueWidget;
use App\Filament\Widgets\RevenueTrendChartWidget;
use App\Filament\Widgets\StatsOverview;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-03-18 10:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

/**
 * @return mixed
 */
function callProtectedMethod(object $object, string $method, array $arguments = [])
{
    $reflection = new ReflectionMethod($object, $method);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs($object, $arguments);
}

/**
 * @param  array<int, \Filament\Widgets\StatsOverviewWidget\Stat>  $stats
 * @return array<string, mixed>
 */
function mapStatsByLabel(array $stats): array
{
    return collect($stats)
        ->mapWithKeys(fn ($stat): array => [(string) $stat->getLabel() => $stat->getValue()])
        ->all();
}

function amountFromFormattedValue(string $value): float
{
    return (float) preg_replace('/[^0-9.]/', '', $value);
}

test('admin dashboard registers revenue trend chart after revenue cards', function () {
    $widgets = array_values(Filament::getPanel('admin')->getWidgets());

    $revenueWidgetIndex = array_search(RevenueWidget::class, $widgets, true);
    $trendWidgetIndex = array_search(RevenueTrendChartWidget::class, $widgets, true);

    expect($revenueWidgetIndex)->not()->toBeFalse();
    expect($trendWidgetIndex)->not()->toBeFalse();
    expect($trendWidgetIndex)->toBe($revenueWidgetIndex + 1);
});

test('branch filter applies to both revenue and booking stats widgets', function () {
    $company = Company::factory()->create();
    $owner = User::factory()->create(['company_id' => $company->id]);

    $branchOne = Branch::query()->create([
        'company_id' => $company->id,
        'name' => 'Branch One',
        'address' => 'Address One',
        'is_active' => true,
    ]);

    $branchTwo = Branch::query()->create([
        'company_id' => $company->id,
        'name' => 'Branch Two',
        'address' => 'Address Two',
        'is_active' => true,
    ]);

    $completedBranchOne = Booking::factory()->state([
        'company_id' => $company->id,
        'branch_id' => $branchOne->id,
        'status' => 'completed',
        'payment_status' => 'paid',
        'start_time' => now()->subDay(),
    ])->create();

    $pendingBranchOne = Booking::factory()->state([
        'company_id' => $company->id,
        'branch_id' => $branchOne->id,
        'status' => 'pending',
        'payment_status' => 'pending',
        'start_time' => now(),
    ])->create();

    $completedBranchTwo = Booking::factory()->state([
        'company_id' => $company->id,
        'branch_id' => $branchTwo->id,
        'status' => 'completed',
        'payment_status' => 'paid',
        'start_time' => now()->subDays(2),
    ])->create();

    $pendingBranchTwo = Booking::factory()->state([
        'company_id' => $company->id,
        'branch_id' => $branchTwo->id,
        'status' => 'pending',
        'payment_status' => 'pending',
        'start_time' => now(),
    ])->create();

    DB::table('booking_payments')->insert([
        [
            'company_id' => $company->id,
            'booking_id' => $completedBranchOne->id,
            'processed_by_id' => $owner->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'payment_reference' => 'REF-1',
            'amount' => 100,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ],
        [
            'company_id' => $company->id,
            'booking_id' => $completedBranchTwo->id,
            'processed_by_id' => $owner->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'payment_reference' => 'REF-2',
            'amount' => 250,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ],
        // Should be excluded: booking is not completed.
        [
            'company_id' => $company->id,
            'booking_id' => $pendingBranchTwo->id,
            'processed_by_id' => $owner->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'payment_reference' => 'REF-3',
            'amount' => 999,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        // Should be excluded: payment is not paid.
        [
            'company_id' => $company->id,
            'booking_id' => $completedBranchOne->id,
            'processed_by_id' => $owner->id,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'payment_reference' => 'REF-4',
            'amount' => 888,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $revenueWidget = app(RevenueWidget::class);
    $revenueWidget->pageFilters = ['branch_id' => (string) $branchOne->id];

    $revenueStats = mapStatsByLabel(callProtectedMethod($revenueWidget, 'getStats'));

    expect(amountFromFormattedValue((string) $revenueStats['All Time Revenue']))->toEqualWithDelta(100.0, 0.001);
    expect(amountFromFormattedValue((string) $revenueStats['Revenue This Month']))->toEqualWithDelta(100.0, 0.001);

    $bookingStatsWidget = app(StatsOverview::class);
    $bookingStatsWidget->pageFilters = ['branch_id' => (string) $branchOne->id];

    $bookingStats = mapStatsByLabel(callProtectedMethod($bookingStatsWidget, 'getStats'));

    expect((int) $bookingStats['Total Bookings'])->toBe(2);
    expect((int) $bookingStats["Today's Bookings"])->toBe(1);
    expect((int) $bookingStats['Pending Payments'])->toBe(1);

    $bookingStatsWidget->pageFilters = ['branch_id' => (string) $branchTwo->id];

    $branchTwoBookingStats = mapStatsByLabel(callProtectedMethod($bookingStatsWidget, 'getStats'));

    expect((int) $branchTwoBookingStats['Total Bookings'])->toBe(2);
    expect((int) $branchTwoBookingStats['Pending Payments'])->toBe(1);
});

test('revenue trend chart respects branch filter exclusion rules and period filters', function () {
    $company = Company::factory()->create();
    $owner = User::factory()->create(['company_id' => $company->id]);

    $branchOne = Branch::query()->create([
        'company_id' => $company->id,
        'name' => 'Branch One',
        'address' => 'Address One',
        'is_active' => true,
    ]);

    $branchTwo = Branch::query()->create([
        'company_id' => $company->id,
        'name' => 'Branch Two',
        'address' => 'Address Two',
        'is_active' => true,
    ]);

    $completedBranchOneRecent = Booking::factory()->state([
        'company_id' => $company->id,
        'branch_id' => $branchOne->id,
        'status' => 'completed',
        'payment_status' => 'paid',
        'start_time' => now()->subDay(),
    ])->create();

    $completedBranchOneOld = Booking::factory()->state([
        'company_id' => $company->id,
        'branch_id' => $branchOne->id,
        'status' => 'completed',
        'payment_status' => 'paid',
        'start_time' => now()->subDays(40),
    ])->create();

    $completedBranchTwoRecent = Booking::factory()->state([
        'company_id' => $company->id,
        'branch_id' => $branchTwo->id,
        'status' => 'completed',
        'payment_status' => 'paid',
        'start_time' => now()->subDays(3),
    ])->create();

    $pendingBranchOne = Booking::factory()->state([
        'company_id' => $company->id,
        'branch_id' => $branchOne->id,
        'status' => 'pending',
        'payment_status' => 'pending',
        'start_time' => now()->subDays(2),
    ])->create();

    DB::table('booking_payments')->insert([
        // Included for branch one: inside 7d and 30d.
        [
            'company_id' => $company->id,
            'booking_id' => $completedBranchOneRecent->id,
            'processed_by_id' => $owner->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'payment_reference' => 'TREND-REF-1',
            'amount' => 120,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ],
        // Included only for 90d.
        [
            'company_id' => $company->id,
            'booking_id' => $completedBranchOneOld->id,
            'processed_by_id' => $owner->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'payment_reference' => 'TREND-REF-2',
            'amount' => 80,
            'created_at' => now()->subDays(40),
            'updated_at' => now()->subDays(40),
        ],
        // Excluded by branch filter.
        [
            'company_id' => $company->id,
            'booking_id' => $completedBranchTwoRecent->id,
            'processed_by_id' => $owner->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'payment_reference' => 'TREND-REF-3',
            'amount' => 300,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ],
        // Excluded because booking is not completed.
        [
            'company_id' => $company->id,
            'booking_id' => $pendingBranchOne->id,
            'processed_by_id' => $owner->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'payment_reference' => 'TREND-REF-4',
            'amount' => 999,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ],
        // Excluded because payment is not paid.
        [
            'company_id' => $company->id,
            'booking_id' => $completedBranchOneRecent->id,
            'processed_by_id' => $owner->id,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'payment_reference' => 'TREND-REF-5',
            'amount' => 777,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ],
    ]);

    $widget = app(RevenueTrendChartWidget::class);
    $widget->pageFilters = ['branch_id' => (string) $branchOne->id];

    $widget->filter = '7d';
    $sevenDayData = callProtectedMethod($widget, 'getData');
    $sevenDayPoints = $sevenDayData['datasets'][0]['data'];

    expect(count($sevenDayData['labels']))->toBe(7);
    expect(count($sevenDayPoints))->toBe(7);
    expect(array_sum($sevenDayPoints))->toEqualWithDelta(120.0, 0.001);

    $widget->filter = '30d';
    $thirtyDayData = callProtectedMethod($widget, 'getData');
    $thirtyDayPoints = $thirtyDayData['datasets'][0]['data'];

    expect(count($thirtyDayData['labels']))->toBe(30);
    expect(count($thirtyDayPoints))->toBe(30);
    expect(array_sum($thirtyDayPoints))->toEqualWithDelta(120.0, 0.001);

    $widget->filter = '90d';
    $ninetyDayData = callProtectedMethod($widget, 'getData');
    $ninetyDayPoints = $ninetyDayData['datasets'][0]['data'];

    expect(count($ninetyDayData['labels']))->toBe(90);
    expect(count($ninetyDayPoints))->toBe(90);
    expect(array_sum($ninetyDayPoints))->toEqualWithDelta(200.0, 0.001);

    Livewire::test(RevenueTrendChartWidget::class, [
        'pageFilters' => ['branch_id' => (string) $branchOne->id],
        'filter' => '30d',
    ])
        ->assertSee('Revenue Trend')
        ->assertSee('Daily paid revenue for completed bookings');
});
