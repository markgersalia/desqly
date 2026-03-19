<?php

use App\Filament\SuperAdmin\Resources\Companies\Pages\CreateCompany;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\BusinessSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::query()->firstOrCreate(['name' => 'SystemOwner', 'guard_name' => 'web']);
    Role::query()->firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
});

test('creating a company in super admin applies core settings overrides for company mode', function () {
    $owner = User::factory()->create(['company_id' => null]);
    $owner->assignRole('SystemOwner');

    Filament::setCurrentPanel('super-admin');

    Livewire::actingAs($owner)
        ->test(CreateCompany::class)
        ->set('data.name', 'Wellness HQ')
        ->set('data.slug', 'wellness-hq')
        ->set('data.plan_code', 'starter')
        ->set('data.subscription_status', 'active')
        ->set('data.is_active', true)
        ->set('data.business.entity_type', 'company')
        ->set('data.business.timezone', 'Asia/Tokyo')
        ->set('data.business.currency', 'usd')
        ->set('data.booking.has_listings', false)
        ->set('data.booking.requires_staff', false)
        ->set('data.booking.requires_bed', true)
        ->set('data.booking.requires_follow_up', false)
        ->set('data.booking.mode', 'time_slot')
        ->set('data.booking.slot_interval_minutes', 20)
        ->set('data.booking.day_start', '06:00')
        ->set('data.booking.day_end', '20:00')
        ->set('data.booking.expire_after_hours', 10)
        ->set('data.booking.grace_period_minutes', 5)
        ->set('data.admin_name', 'Wellness Admin')
        ->set('data.admin_email', 'wellness.admin@example.com')
        ->set('data.admin_password', 'secret123')
        ->call('create')
        ->assertHasNoErrors();

    $company = Company::query()->where('slug', 'wellness-hq')->first();

    expect($company)->not->toBeNull();

    $admin = User::query()->where('email', 'wellness.admin@example.com')->first();

    expect($admin)->not->toBeNull();
    expect((int) $admin->company_id)->toBe((int) $company->id);
    expect($admin->hasRole('Admin'))->toBeTrue();

    $settingsService = app(BusinessSettings::class);
    $settings = $settingsService->getSettings($company);

    expect(data_get($settings, 'business.name'))->toBe('Wellness HQ');
    expect(data_get($settings, 'business.entity_type'))->toBe('company');
    expect(data_get($settings, 'business.type'))->toBe('generic');
    expect(data_get($settings, 'business.timezone'))->toBe('Asia/Tokyo');
    expect(data_get($settings, 'business.currency'))->toBe('USD');
    expect(data_get($settings, 'booking.has_listings'))->toBeFalse();
    expect(data_get($settings, 'booking.requires_staff'))->toBeTrue();
    expect(data_get($settings, 'booking.requires_bed'))->toBeTrue();
    expect(data_get($settings, 'booking.requires_follow_up'))->toBeFalse();
    expect(data_get($settings, 'booking.mode'))->toBe('time_slot');
    expect((int) data_get($settings, 'booking.slot_interval_minutes'))->toBe(20);
    expect(data_get($settings, 'booking.day_start'))->toBe('06:00');
    expect(data_get($settings, 'booking.day_end'))->toBe('20:00');
    expect((int) data_get($settings, 'booking.expire_after_hours'))->toBe(10);
    expect((int) data_get($settings, 'booking.grace_period_minutes'))->toBe(5);
    expect($settingsService->isOnboardingComplete($company))->toBeTrue();

    $branch = Branch::query()
        ->where('company_id', $company->getKey())
        ->where('name', 'Main Branch')
        ->first();

    expect($branch)->not->toBeNull();
    expect((int) data_get($settings, 'branches.default_branch_id'))->toBe((int) $branch->id);
});

test('creating a company in super admin applies core settings overrides for individual mode', function () {
    $owner = User::factory()->create(['company_id' => null]);
    $owner->assignRole('SystemOwner');

    Filament::setCurrentPanel('super-admin');

    Livewire::actingAs($owner)
        ->test(CreateCompany::class)
        ->set('data.name', 'Solo Care')
        ->set('data.slug', 'solo-care')
        ->set('data.plan_code', 'starter')
        ->set('data.subscription_status', 'active')
        ->set('data.is_active', true)
        ->set('data.business.entity_type', 'individual')
        ->set('data.business.timezone', 'UTC')
        ->set('data.business.currency', 'eur')
        ->set('data.booking.has_listings', true)
        ->set('data.booking.requires_staff', true)
        ->set('data.booking.requires_bed', false)
        ->set('data.booking.requires_follow_up', true)
        ->set('data.booking.mode', 'whole_day')
        ->set('data.booking.slot_interval_minutes', 60)
        ->set('data.booking.day_start', '09:00')
        ->set('data.booking.day_end', '17:00')
        ->set('data.booking.expire_after_hours', 6)
        ->set('data.booking.grace_period_minutes', 10)
        ->set('data.admin_name', 'Solo Admin')
        ->set('data.admin_email', 'solo.admin@example.com')
        ->set('data.admin_password', 'secret123')
        ->call('create')
        ->assertHasNoErrors();

    $company = Company::query()->where('slug', 'solo-care')->first();

    expect($company)->not->toBeNull();

    $settingsService = app(BusinessSettings::class);
    $settings = $settingsService->getSettings($company);

    expect(data_get($settings, 'business.entity_type'))->toBe('individual');
    expect(data_get($settings, 'business.timezone'))->toBe('UTC');
    expect(data_get($settings, 'business.currency'))->toBe('EUR');
    expect(data_get($settings, 'booking.has_listings'))->toBeTrue();
    expect(data_get($settings, 'booking.requires_staff'))->toBeFalse();
    expect(data_get($settings, 'booking.mode'))->toBe('whole_day');
    expect((int) data_get($settings, 'booking.expire_after_hours'))->toBe(6);
    expect((int) data_get($settings, 'booking.grace_period_minutes'))->toBe(10);
    expect(data_get($settings, 'branches.default_branch_id'))->toBeNull();
    expect($settingsService->isOnboardingComplete($company))->toBeTrue();
    expect(Branch::query()->where('company_id', $company->getKey())->count())->toBe(0);
});

