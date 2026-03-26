<?php

use App\Filament\Pages\BusinessSettingsPage;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Listing;
use App\Models\Therapist;
use App\Models\User;
use App\Services\BusinessSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::query()->firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::query()->firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);

    $this->company = Company::factory()->create([
        'name' => 'Tenant Company',
        'slug' => 'tenant-company',
    ]);

    $defaultBranch = Branch::create([
        'company_id' => $this->company->id,
        'name' => 'Default Branch',
        'is_active' => true,
    ]);

    app(BusinessSettings::class)->saveSettings([
        'onboarding' => [
            'completed_at' => now()->toDateTimeString(),
        ],
        'business' => [
            'name' => 'Tenant Company',
            'type' => 'spa',
            'entity_type' => 'company',
            'timezone' => 'Asia/Manila',
            'currency' => 'PHP',
        ],
        'booking' => [
            'has_listings' => true,
            'requires_staff' => true,
            'requires_bed' => true,
            'requires_follow_up' => true,
            'mode' => 'time_slot',
            'slot_interval_minutes' => 30,
            'day_start' => '08:00',
            'day_end' => '18:00',
            'expire_after_hours' => 24,
            'grace_period_minutes' => 15,
        ],
        'branches' => [
            'default_branch_id' => $defaultBranch->id,
        ],
    ], $this->company);
});

test('admin can access business settings while staff is forbidden', function () {
    $admin = User::factory()->create(['company_id' => $this->company->id]);
    $admin->assignRole('Admin');

    $staff = User::factory()->create(['company_id' => $this->company->id]);
    $staff->assignRole('Staff');

    $this->actingAs($admin)
        ->get(route('filament.admin.pages.business-settings', filament_tenant_route_params($this->company)))
        ->assertOk();

    $this->actingAs($staff)
        ->get(route('filament.admin.pages.business-settings', filament_tenant_route_params($this->company)))
        ->assertForbidden();
});

test('business settings page renders aligned wizard steps', function () {
    $admin = User::factory()->create(['company_id' => $this->company->id]);
    $admin->assignRole('Admin');

    $this->actingAs($admin)
        ->get(route('filament.admin.pages.business-settings', filament_tenant_route_params($this->company)))
        ->assertOk()
        ->assertSee('Business Profile')
        ->assertSee('Booking Rules')
        ->assertSee('Labels')
        ->assertSee('Branch Setup');
});

test('business settings page persists full wizard payload', function () {
    $user = User::factory()->create(['company_id' => $this->company->id]);
    $user->assignRole('Admin');

    $branch = Branch::create([
        'company_id' => $this->company->id,
        'name' => 'Second Branch',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)
        ->test(BusinessSettingsPage::class)
        ->set('data.business.name', 'Renamed Wellness')
        ->set('data.business.type', 'clinic')
        ->set('data.business.entity_type', 'company')
        ->set('data.business.timezone', 'UTC')
        ->set('data.business.currency', 'usd')
        ->set('data.booking.has_listings', false)
        ->set('data.booking.requires_bed', false)
        ->set('data.booking.requires_follow_up', false)
        ->set('data.booking.mode', 'whole_day')
        ->set('data.booking.slot_interval_minutes', 45)
        ->set('data.booking.day_start', '07:00')
        ->set('data.booking.day_end', '19:00')
        ->set('data.booking.expire_after_hours', 12)
        ->set('data.booking.grace_period_minutes', 20)
        ->set('data.labels.staff', 'Practitioner')
        ->set('data.labels.resource', 'Room')
        ->set('data.labels.service', 'Appointment')
        ->set('data.labels.booking', 'Visit')
        ->set('data.branches.default_branch_id', $branch->id)
        ->call('save')
        ->assertHasNoErrors();

    $settings = app(BusinessSettings::class)->getSettings($this->company);

    expect(data_get($settings, 'business.name'))->toBe('Renamed Wellness');
    expect(data_get($settings, 'business.type'))->toBe('clinic');
    expect(data_get($settings, 'business.entity_type'))->toBe('company');
    expect(data_get($settings, 'business.timezone'))->toBe('UTC');
    expect(data_get($settings, 'business.currency'))->toBe('USD');

    expect(data_get($settings, 'booking.has_listings'))->toBeFalse();
    expect(data_get($settings, 'booking.requires_staff'))->toBeTrue();
    expect(data_get($settings, 'booking.requires_bed'))->toBeFalse();
    expect(data_get($settings, 'booking.requires_follow_up'))->toBeFalse();
    expect(data_get($settings, 'booking.mode'))->toBe('whole_day');
    expect((int) data_get($settings, 'booking.slot_interval_minutes'))->toBe(45);
    expect(data_get($settings, 'booking.day_start'))->toBe('07:00');
    expect(data_get($settings, 'booking.day_end'))->toBe('19:00');
    expect((int) data_get($settings, 'booking.expire_after_hours'))->toBe(12);
    expect((int) data_get($settings, 'booking.grace_period_minutes'))->toBe(20);

    expect(data_get($settings, 'labels.staff'))->toBe('Practitioner');
    expect(data_get($settings, 'labels.resource'))->toBe('Room');
    expect(data_get($settings, 'labels.service'))->toBe('Appointment');
    expect(data_get($settings, 'labels.booking'))->toBe('Visit');
    expect((int) data_get($settings, 'branches.default_branch_id'))->toBe($branch->id);
});

test('business settings page persists core changes and normalizes mode-dependent values', function () {
    $user = User::factory()->create(['company_id' => $this->company->id]);
    $user->assignRole('Admin');

    $branch = Branch::create([
        'company_id' => $this->company->id,
        'name' => 'Unused Branch',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)
        ->test(BusinessSettingsPage::class)
        ->set('data.business.entity_type', 'individual')
        ->set('data.business.timezone', 'Asia/Tokyo')
        ->set('data.business.currency', 'eur')
        ->set('data.booking.has_listings', true)
        ->set('data.booking.requires_staff', true)
        ->set('data.booking.requires_bed', false)
        ->set('data.booking.requires_follow_up', true)
        ->set('data.booking.mode', 'time_slot')
        ->set('data.booking.slot_interval_minutes', 25)
        ->set('data.booking.day_start', '10:00')
        ->set('data.booking.day_end', '16:00')
        ->set('data.booking.expire_after_hours', 8)
        ->set('data.booking.grace_period_minutes', 5)
        ->set('data.branches.default_branch_id', $branch->id)
        ->call('save')
        ->assertHasNoErrors();

    $settings = app(BusinessSettings::class)->getSettings($this->company);

    expect(data_get($settings, 'business.entity_type'))->toBe('individual');
    expect(data_get($settings, 'business.timezone'))->toBe('Asia/Tokyo');
    expect(data_get($settings, 'business.currency'))->toBe('EUR');

    expect(data_get($settings, 'booking.requires_staff'))->toBeFalse();
    expect((int) data_get($settings, 'booking.slot_interval_minutes'))->toBe(25);
    expect((int) data_get($settings, 'booking.expire_after_hours'))->toBe(8);
    expect((int) data_get($settings, 'booking.grace_period_minutes'))->toBe(5);

    expect(data_get($settings, 'branches.default_branch_id'))->toBeNull();
});

test('business settings save backfills null branch assignments', function () {
    $user = User::factory()->create(['company_id' => $this->company->id]);
    $user->assignRole('Admin');

    $targetBranch = Branch::create([
        'company_id' => $this->company->id,
        'name' => 'Target Branch',
        'is_active' => true,
    ]);

    $customer = Customer::factory()->create(['is_vip' => false]);
    $listing = Listing::factory()->create();

    $therapist = Therapist::create([
        'name' => 'Therapist One',
        'image' => 'avatar.png',
        'bio' => 'Bio',
        'email' => null,
        'phone' => null,
        'is_active' => true,
        'branch_id' => null,
    ]);

    $bed = Bed::create([
        'name' => 'Bed 1',
        'description' => null,
        'is_available' => true,
        'branch_id' => null,
    ]);

    $bookingId = DB::table('bookings')->insertGetId([
        'company_id' => $this->company->id,
        'customer_id' => $customer->id,
        'listing_id' => $listing->id,
        'therapist_id' => $therapist->id,
        'bed_id' => $bed->id,
        'booking_number' => 'BK-90001',
        'title' => 'Test Booking',
        'type' => 'service',
        'price' => 200,
        'start_time' => now()->addHour(),
        'end_time' => now()->addHours(2),
        'status' => 'pending',
        'payment_status' => 'pending',
        'branch_id' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(BusinessSettingsPage::class)
        ->set('data.branches.default_branch_id', $targetBranch->id)
        ->call('save')
        ->assertHasNoErrors();

    expect($therapist->fresh()->branch_id)->toBe($targetBranch->id);
    expect($bed->fresh()->branch_id)->toBe($targetBranch->id);
    expect(Booking::query()->find($bookingId)?->branch_id)->toBe($targetBranch->id);
});
