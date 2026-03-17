<?php

use App\Filament\Pages\BusinessSettingsPage;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Listing;
use App\Models\Therapist;
use App\Models\User;
use App\Services\BusinessSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $defaultBranch = Branch::create([
        'name' => 'Default Branch',
        'is_active' => true,
    ]);

    app(BusinessSettings::class)->saveSettings([
        'onboarding' => [
            'completed_at' => now()->toDateTimeString(),
        ],
        'branches' => [
            'default_branch_id' => $defaultBranch->id,
        ],
    ]);
});

test('business settings page saves updates', function () {
    $user = User::factory()->create();
    $branch = Branch::create([
        'name' => 'Second Branch',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)
        ->test(BusinessSettingsPage::class)
        ->set('data.business.name', 'Updated Name')
        ->set('data.business.type', 'clinic')
        ->set('data.business.timezone', 'Asia/Manila')
        ->set('data.business.currency', 'php')
        ->set('data.booking.has_listings', true)
        ->set('data.booking.requires_bed', false)
        ->set('data.booking.requires_follow_up', true)
        ->set('data.booking.slot_interval_minutes', 45)
        ->set('data.booking.day_start', '08:00')
        ->set('data.booking.day_end', '17:00')
        ->set('data.booking.expire_after_hours', 12)
        ->set('data.booking.grace_period_minutes', 20)
        ->set('data.labels.staff', 'Practitioner')
        ->set('data.labels.resource', 'Room')
        ->set('data.labels.service', 'Appointment')
        ->set('data.labels.booking', 'Visit')
        ->set('data.branches.default_branch_id', $branch->id)
        ->call('save');

    $settings = app(BusinessSettings::class)->getSettings();

    expect(data_get($settings, 'business.name'))->toBe('Updated Name');
    expect(data_get($settings, 'business.currency'))->toBe('PHP');
    expect((int) data_get($settings, 'branches.default_branch_id'))->toBe($branch->id);
});

test('business settings save backfills null branch assignments', function () {
    $user = User::factory()->create();
    $targetBranch = Branch::create([
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
        ->call('save');

    expect($therapist->fresh()->branch_id)->toBe($targetBranch->id);
    expect($bed->fresh()->branch_id)->toBe($targetBranch->id);
    expect(Booking::query()->find($bookingId)?->branch_id)->toBe($targetBranch->id);
});
