<?php

use App\Filament\Pages\Tenancy\CompanyBilling;
use App\Models\Company;
use App\Models\User;
use App\Services\BusinessSettings;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Carbon::setTestNow(Carbon::parse('2026-03-24 10:00:00'));
});

afterEach(function () {
    Filament::setTenant(null, true);
    Carbon::setTestNow();
});

function createTenantUser(Company $company, string $roleName = 'Admin'): User
{
    $role = Role::query()->firstOrCreate([
        'name' => $roleName,
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create([
        'company_id' => $company->id,
    ]);

    $user->assignRole($role);

    return $user;
}

test('company billing page allows admin tenant member and denies non-admin or outsider', function () {
    $company = Company::factory()->create(['plan_code' => 'starter']);
    $otherCompany = Company::factory()->create(['plan_code' => 'starter']);

    $admin = createTenantUser($company, 'Admin');
    $staff = createTenantUser($company, 'Staff');
    $outsiderAdmin = createTenantUser($otherCompany, 'Admin');

    $this->actingAs($admin)
        ->get(route('filament.admin.tenant.billing', filament_tenant_route_params($company)))
        ->assertOk()
        ->assertSee('Upgrade Plan');

    $this->actingAs($staff)
        ->get(route('filament.admin.tenant.billing', filament_tenant_route_params($company)))
        ->assertForbidden();

    $this->actingAs($outsiderAdmin)
        ->get(route('filament.admin.tenant.billing', filament_tenant_route_params($company)))
        ->assertNotFound();
});

test('company billing page renders reference critical elements and no placeholder avatar text', function () {
    $company = Company::factory()->create([
        'plan_code' => 'starter',
        'name' => 'Grand Sylhet Hotel',
        'avatar' => null,
    ]);
    $admin = createTenantUser($company, 'Admin');

    $this->actingAs($admin)
        ->get(route('filament.admin.tenant.billing', filament_tenant_route_params($company)))
        ->assertOk()
        ->assertSee('Upgrade Plan')
        ->assertSee('Monthly')
        ->assertSee('Annual')
        ->assertSee('Free')
        ->assertSee('Pro')
        ->assertSee('Premium')
        ->assertSee('Current Plan')
        ->assertSee('Upgrade to Pro')
        ->assertSee('Upgrade to Premium')
        ->assertSee('company-billing-avatar-fallback')
        ->assertSee('GS')
        ->assertDontSee('COMPANY AVATAR HERE');
});

test('company billing page shows avatar image when company has avatar url', function () {
    $company = Company::factory()->create([
        'plan_code' => 'starter',
        'name' => 'Acme Hotel',
        'avatar' => 'https://example.com/logo.png',
    ]);
    $admin = createTenantUser($company, 'Admin');

    $this->actingAs($admin)
        ->get(route('filament.admin.tenant.billing', filament_tenant_route_params($company)))
        ->assertOk()
        ->assertSee('https://example.com/logo.png')
        ->assertSee('Acme Hotel avatar')
        ->assertDontSee('COMPANY AVATAR HERE');
});

test('company billing maps growth and pro to current pro tier and enterprise to premium tier', function (string $planCode, string $expectedPlanHeading) {
    $company = Company::factory()->create(['plan_code' => $planCode]);
    $admin = createTenantUser($company, 'Admin');

    $this->actingAs($admin)
        ->get(route('filament.admin.tenant.billing', filament_tenant_route_params($company)))
        ->assertOk()
        ->assertSeeInOrder([$expectedPlanHeading, 'Current Plan']);
})->with([
    ['growth', 'Pro'],
    ['pro', 'Pro'],
    ['enterprise', 'Premium'],
]);

test('company billing monthly upgrade mutates subscription fields', function () {
    $company = Company::factory()->create([
        'plan_code' => 'starter',
        'subscription_status' => 'trialing',
        'trial_ends_at' => Carbon::parse('2026-04-10 09:30:00'),
        'current_period_ends_at' => Carbon::parse('2026-04-24 09:30:00'),
    ]);

    $admin = createTenantUser($company, 'Admin');

    $this->actingAs($admin);
    Filament::setTenant($company, true);

    Livewire::test(CompanyBilling::class)
        ->set('billingCycle', 'monthly')
        ->call('upgrade', 'pro');

    $company->refresh();

    expect($company->plan_code)->toBe('pro')
        ->and($company->subscription_status)->toBe('active')
        ->and($company->trial_ends_at)->toBeNull()
        ->and($company->current_period_ends_at?->toDateTimeString())->toBe(Carbon::now()->addMonth()->toDateTimeString());
});

test('company billing annual upgrade to premium sets enterprise plan and annual renewal', function () {
    $company = Company::factory()->create([
        'plan_code' => 'pro',
        'subscription_status' => 'past_due',
        'trial_ends_at' => null,
        'current_period_ends_at' => Carbon::parse('2026-04-01 10:00:00'),
    ]);

    $admin = createTenantUser($company, 'Admin');

    $this->actingAs($admin);
    Filament::setTenant($company, true);

    Livewire::test(CompanyBilling::class)
        ->set('billingCycle', 'annual')
        ->call('upgrade', 'premium');

    $company->refresh();

    expect($company->plan_code)->toBe('enterprise')
        ->and($company->subscription_status)->toBe('active')
        ->and($company->current_period_ends_at?->toDateTimeString())->toBe(Carbon::now()->addYear()->toDateTimeString());
});

test('company billing downgrade action is non mutating', function () {
    $company = Company::factory()->create([
        'plan_code' => 'enterprise',
        'subscription_status' => 'past_due',
        'trial_ends_at' => Carbon::parse('2026-04-01 09:00:00'),
        'current_period_ends_at' => Carbon::parse('2026-05-01 09:00:00'),
    ]);

    $admin = createTenantUser($company, 'Admin');

    $this->actingAs($admin);
    Filament::setTenant($company, true);

    Livewire::test(CompanyBilling::class)
        ->set('billingCycle', 'monthly')
        ->call('upgrade', 'pro');

    $company->refresh();

    expect($company->plan_code)->toBe('enterprise')
        ->and($company->subscription_status)->toBe('past_due')
        ->and($company->trial_ends_at?->toDateTimeString())->toBe('2026-04-01 09:00:00')
        ->and($company->current_period_ends_at?->toDateTimeString())->toBe('2026-05-01 09:00:00');
});

test('company billing prices use business settings currency', function () {
    $company = Company::factory()->create(['plan_code' => 'starter']);
    $admin = createTenantUser($company, 'Admin');

    app(BusinessSettings::class)->saveSettings([
        'business' => [
            'currency' => 'USD',
        ],
    ], $company);

    $this->actingAs($admin);
    Filament::setTenant($company, true);

    Livewire::test(CompanyBilling::class)
        ->assertSee('USD 0.00')
        ->assertSee('USD 799.00')
        ->assertSee('USD 2,399.00');
});
