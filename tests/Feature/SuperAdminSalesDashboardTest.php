<?php

use App\Filament\SuperAdmin\Widgets\CompanySalesBreakdownWidget;
use App\Filament\SuperAdmin\Widgets\SalesOverviewWidget;
use App\Filament\SuperAdmin\Widgets\SalesTrendChartWidget;
use App\Models\Booking;
use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-03-18 10:00:00'));

    Role::query()->firstOrCreate(['name' => 'SystemOwner', 'guard_name' => 'web']);
    Role::query()->firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
});

afterEach(function () {
    Carbon::setTestNow();
});

function makeSystemOwner(): User
{
    $owner = User::factory()->create(['company_id' => null]);
    $owner->assignRole('SystemOwner');

    return $owner;
}

/**
 * @return array{company1: Company, company2: Company, company3: Company}
 */
function seedSalesDataset(User $owner): array
{
    $company1 = Company::factory()->create([
        'name' => 'Company 1',
        'slug' => 'company-1',
        'subscription_status' => 'active',
        'is_active' => true,
    ]);

    $company2 = Company::factory()->create([
        'name' => 'Company 2',
        'slug' => 'company-2',
        'subscription_status' => 'trialing',
        'is_active' => true,
    ]);

    $company3 = Company::factory()->create([
        'name' => 'Company 3',
        'slug' => 'company-3',
        'subscription_status' => 'past_due',
        'is_active' => true,
    ]);

    $createBooking = function (Company $company, string $status): Booking {
        return Booking::factory()->state([
            'company_id' => $company->id,
            'status' => $status,
        ])->create();
    };

    $insertPayment = function (Company $company, Booking $booking, float $amount, string $paymentStatus, Carbon $createdAt) use ($owner): void {
        DB::table('booking_payments')->insert([
            'company_id' => $company->id,
            'booking_id' => $booking->id,
            'processed_by_id' => $owner->id,
            'payment_method' => 'cash',
            'payment_status' => $paymentStatus,
            'payment_reference' => 'REF-' . $booking->id,
            'amount' => $amount,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    };

    $insertPayment($company1, $createBooking($company1, 'completed'), 100, 'paid', now()->subDays(5));
    $insertPayment($company1, $createBooking($company1, 'completed'), 40, 'paid', now()->subDays(17));
    $insertPayment($company2, $createBooking($company2, 'completed'), 200, 'paid', now()->subDays(2));
    $insertPayment($company3, $createBooking($company3, 'completed'), 300, 'paid', now()->subDays(45));

    // Exclusions required by the sales rules.
    $insertPayment($company1, $createBooking($company1, 'completed'), 999, 'pending', now()->subDays(3));
    $insertPayment($company2, $createBooking($company2, 'pending'), 888, 'paid', now()->subDays(3));

    return compact('company1', 'company2', 'company3');
}

/**
 * @return mixed
 */
function callProtected(object $object, string $method, array $arguments = [])
{
    $reflectionMethod = new ReflectionMethod($object, $method);
    $reflectionMethod->setAccessible(true);

    return $reflectionMethod->invokeArgs($object, $arguments);
}

/**
 * @return array<string, string>
 */
function getSalesOverviewStats(array $filters): array
{
    $widget = app(SalesOverviewWidget::class);
    $widget->pageFilters = $filters;

    return collect(callProtected($widget, 'getStats'))
        ->mapWithKeys(fn (Stat $stat): array => [$stat->getLabel() => (string) $stat->getValue()])
        ->all();
}

test('sales overview and breakdown respect company filters and sales rules', function () {
    $owner = makeSystemOwner();
    ['company1' => $company1, 'company2' => $company2, 'company3' => $company3] = seedSalesDataset($owner);

    Livewire::actingAs($owner)
        ->test(SalesOverviewWidget::class, [
            'pageFilters' => [
                'period' => '30d',
            ],
        ])
        ->assertSee('Active Subscriptions')
        ->assertSee('At-Risk Subscriptions')
        ->assertSee('Total Sales')
        ->assertSee('Paid Transactions')
        ->assertSee('Avg Sales / Company')
        ->assertDontSee('This Month Sales')
        ->assertDontSee('Selected Companies')
        ->assertSee('PHP 340.00');

    $companyOneStats = getSalesOverviewStats([
        'company_ids' => [(string) $company1->id],
        'period' => '30d',
    ]);

    expect($companyOneStats['Active Subscriptions'])->toBe('1')
        ->and($companyOneStats['At-Risk Subscriptions'])->toBe('0')
        ->and($companyOneStats['Total Sales'])->toBe('PHP 140.00')
        ->and($companyOneStats['Paid Transactions'])->toBe('2')
        ->and($companyOneStats['Avg Sales / Company'])->toBe('PHP 140.00');

    $companyOneTwoStats = getSalesOverviewStats([
        'company_ids' => [(string) $company1->id, (string) $company2->id],
        'period' => '30d',
    ]);

    expect($companyOneTwoStats['Active Subscriptions'])->toBe('2')
        ->and($companyOneTwoStats['At-Risk Subscriptions'])->toBe('0')
        ->and($companyOneTwoStats['Total Sales'])->toBe('PHP 340.00')
        ->and($companyOneTwoStats['Paid Transactions'])->toBe('3')
        ->and($companyOneTwoStats['Avg Sales / Company'])->toBe('PHP 170.00');

    Livewire::actingAs($owner)
        ->test(CompanySalesBreakdownWidget::class, [
            'pageFilters' => [
                'company_ids' => [(string) $company1->id],
                'period' => '30d',
            ],
        ])
        ->assertSee('Company 1')
        ->assertDontSee('Company 2')
        ->assertDontSee('Company 3');

    Livewire::actingAs($owner)
        ->test(CompanySalesBreakdownWidget::class, [
            'pageFilters' => [
                'company_ids' => [(string) $company1->id, (string) $company2->id],
                'period' => '30d',
            ],
        ])
        ->assertSee('Company 1')
        ->assertSee('Company 2')
        ->assertDontSee('Company 3');
});

test('subscription cards respect company filters and ignore period changes', function () {
    $owner = makeSystemOwner();
    ['company1' => $company1, 'company2' => $company2, 'company3' => $company3] = seedSalesDataset($owner);

    $thirtyDayStats = getSalesOverviewStats([
        'company_ids' => [(string) $company1->id, (string) $company2->id, (string) $company3->id],
        'period' => '30d',
    ]);

    $sevenDayStats = getSalesOverviewStats([
        'company_ids' => [(string) $company1->id, (string) $company2->id, (string) $company3->id],
        'period' => '7d',
    ]);

    expect($thirtyDayStats['Active Subscriptions'])->toBe('2')
        ->and($thirtyDayStats['At-Risk Subscriptions'])->toBe('1')
        ->and($thirtyDayStats['Total Sales'])->toBe('PHP 340.00')
        ->and($thirtyDayStats['Paid Transactions'])->toBe('3')
        ->and($thirtyDayStats['Avg Sales / Company'])->toBe('PHP 113.33');

    expect($sevenDayStats['Active Subscriptions'])->toBe('2')
        ->and($sevenDayStats['At-Risk Subscriptions'])->toBe('1')
        ->and($sevenDayStats['Total Sales'])->toBe('PHP 300.00')
        ->and($sevenDayStats['Paid Transactions'])->toBe('2')
        ->and($sevenDayStats['Avg Sales / Company'])->toBe('PHP 100.00');
});

test('sales trend data respects selected company and period', function () {
    $owner = makeSystemOwner();
    ['company1' => $company1] = seedSalesDataset($owner);

    $widget = app(SalesTrendChartWidget::class);
    $widget->pageFilters = [
        'company_ids' => [(string) $company1->id],
        'period' => '30d',
    ];

    $singleCompanyData = callProtected($widget, 'getData');

    expect(array_sum($singleCompanyData['datasets'][0]['data']))->toEqualWithDelta(140.0, 0.001);

    $widget->pageFilters = [
        'period' => '90d',
    ];

    $allCompanies90Days = callProtected($widget, 'getData');

    expect(array_sum($allCompanies90Days['datasets'][0]['data']))->toEqualWithDelta(640.0, 0.001);
});
