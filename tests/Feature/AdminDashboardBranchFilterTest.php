<?php

use App\Filament\Widgets\RevenueWidget;
use App\Filament\Widgets\StatsOverview;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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

