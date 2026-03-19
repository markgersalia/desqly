<?php

use App\Filament\SuperAdmin\Resources\Companies\Pages\EditCompany;
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
});

test('super admin edit preloads core settings from tenant settings', function () {
    $owner = User::factory()->create(['company_id' => null]);
    $owner->assignRole('SystemOwner');

    $company = Company::factory()->create(['name' => 'Alpha Co', 'slug' => 'alpha-co']);

    app(BusinessSettings::class)->saveSettings([
        'business' => [
            'name' => 'Alpha Co',
            'entity_type' => 'individual',
            'timezone' => 'UTC',
            'currency' => 'EUR',
        ],
        'booking' => [
            'mode' => 'whole_day',
            'has_listings' => false,
            'requires_bed' => false,
            'requires_follow_up' => true,
            'slot_interval_minutes' => 25,
            'day_start' => '07:00',
            'day_end' => '19:00',
            'expire_after_hours' => 8,
            'grace_period_minutes' => 12,
        ],
    ], $company);

    Filament::setCurrentPanel('super-admin');

    Livewire::actingAs($owner)
        ->test(EditCompany::class, ['record' => $company->getRouteKey()])
        ->assertSet('data.business.entity_type', 'individual')
        ->assertSet('data.business.timezone', 'UTC')
        ->assertSet('data.business.currency', 'EUR')
        ->assertSet('data.booking.has_listings', false)
        ->assertSet('data.booking.mode', 'whole_day')
        ->assertSet('data.booking.slot_interval_minutes', 25)
        ->assertSet('data.booking.expire_after_hours', 8);
});

test('super admin edit persists core settings and enforces requires staff from entity mode', function () {
    $owner = User::factory()->create(['company_id' => null]);
    $owner->assignRole('SystemOwner');

    $company = Company::factory()->create(['name' => 'Beta Co', 'slug' => 'beta-co']);

    $branch = Branch::create([
        'company_id' => $company->id,
        'name' => 'Main Branch',
        'is_active' => true,
    ]);

    app(BusinessSettings::class)->saveSettings([
        'onboarding' => [
            'completed_at' => now()->toDateTimeString(),
        ],
        'business' => [
            'name' => 'Beta Co',
            'entity_type' => 'individual',
            'timezone' => 'UTC',
            'currency' => 'EUR',
        ],
        'booking' => [
            'mode' => 'time_slot',
            'has_listings' => true,
            'requires_staff' => false,
            'requires_bed' => false,
            'requires_follow_up' => false,
            'slot_interval_minutes' => 60,
            'day_start' => '09:00',
            'day_end' => '17:00',
            'expire_after_hours' => 24,
            'grace_period_minutes' => 30,
        ],
        'branches' => [
            'default_branch_id' => $branch->id,
        ],
    ], $company);

    Filament::setCurrentPanel('super-admin');

    Livewire::actingAs($owner)
        ->test(EditCompany::class, ['record' => $company->getRouteKey()])
        ->set('data.name', 'Beta Co Updated')
        ->set('data.business.entity_type', 'company')
        ->set('data.business.timezone', 'Asia/Manila')
        ->set('data.business.currency', 'php')
        ->set('data.booking.has_listings', false)
        ->set('data.booking.requires_staff', false)
        ->set('data.booking.requires_bed', true)
        ->set('data.booking.requires_follow_up', true)
        ->set('data.booking.mode', 'whole_day')
        ->set('data.booking.slot_interval_minutes', 30)
        ->set('data.booking.day_start', '08:00')
        ->set('data.booking.day_end', '18:00')
        ->set('data.booking.expire_after_hours', 12)
        ->set('data.booking.grace_period_minutes', 20)
        ->call('save')
        ->assertHasNoErrors();

    $company->refresh();

    expect($company->name)->toBe('Beta Co Updated');

    $settings = app(BusinessSettings::class)->getSettings($company);

    expect(data_get($settings, 'business.name'))->toBe('Beta Co Updated');
    expect(data_get($settings, 'business.entity_type'))->toBe('company');
    expect(data_get($settings, 'business.timezone'))->toBe('Asia/Manila');
    expect(data_get($settings, 'business.currency'))->toBe('PHP');
    expect(data_get($settings, 'booking.has_listings'))->toBeFalse();
    expect(data_get($settings, 'booking.requires_staff'))->toBeTrue();
    expect(data_get($settings, 'booking.requires_bed'))->toBeTrue();
    expect(data_get($settings, 'booking.requires_follow_up'))->toBeTrue();
    expect(data_get($settings, 'booking.mode'))->toBe('whole_day');
    expect((int) data_get($settings, 'booking.slot_interval_minutes'))->toBe(30);
    expect(data_get($settings, 'booking.day_start'))->toBe('08:00');
    expect(data_get($settings, 'booking.day_end'))->toBe('18:00');
    expect((int) data_get($settings, 'booking.expire_after_hours'))->toBe(12);
    expect((int) data_get($settings, 'booking.grace_period_minutes'))->toBe(20);

    app(BusinessSettings::class)->applyRuntimeConfig($company);

    expect(config('business.entity_type'))->toBe('company');
    expect(config('booking.requires_staff'))->toBeTrue();
    expect(config('booking.slot_interval_minutes'))->toBe(30);
});

