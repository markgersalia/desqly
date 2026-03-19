<?php

use App\Models\Bed;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Listing;
use App\Models\Therapist;
use App\Services\BusinessSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('defaults to company entity mode and branches enabled', function () {
    $service = app(BusinessSettings::class);

    expect(data_get($service->defaults(), 'business.entity_type'))->toBe('company');
    expect(data_get($service->defaults(), 'booking.requires_staff'))->toBeTrue();
    expect(data_get($service->defaults(), 'booking.mode'))->toBe('time_slot');
    expect($service->usesBranches())->toBeTrue();
    expect($service->requiresStaffAssignment())->toBeTrue();
});

test('individual entity mode disables branch usage', function () {
    $service = app(BusinessSettings::class);
    $branch = Branch::create([
        'name' => 'Main Branch',
        'address' => 'Test Address',
        'is_active' => true,
    ]);

    $service->saveSettings([
        'business' => [
            'entity_type' => 'individual',
        ],
        'branches' => [
            'default_branch_id' => $branch->id,
        ],
    ]);

    expect($service->usesBranches())->toBeFalse();
    expect($service->getDefaultBranchId())->toBeNull();
    expect($service->requiresStaffAssignment())->toBeFalse();
});

test('staff requirement is normalized by entity type', function () {
    $service = app(BusinessSettings::class);

    $service->saveSettings([
        'business' => [
            'entity_type' => 'individual',
        ],
        'booking' => [
            'requires_staff' => true,
        ],
    ]);

    expect(data_get($service->getSettings(), 'booking.requires_staff'))->toBeFalse();

    $service->saveSettings([
        'business' => [
            'entity_type' => 'company',
        ],
        'booking' => [
            'requires_staff' => false,
        ],
    ]);

    expect(data_get($service->getSettings(), 'booking.requires_staff'))->toBeTrue();
});

test('persists onboarding settings and marks completion', function () {
    $service = app(BusinessSettings::class);
    $branch = Branch::create([
        'name' => 'Main Branch',
        'address' => 'Test Address',
        'is_active' => true,
    ]);

    $service->saveSettings([
        'onboarding' => [
            'completed_at' => now()->toDateTimeString(),
        ],
        'business' => [
            'name' => 'Health Studio',
            'entity_type' => 'company',
            'type' => 'clinic',
            'timezone' => 'Asia/Manila',
            'currency' => 'PHP',
        ],
        'branches' => [
            'default_branch_id' => $branch->id,
        ],
    ]);

    expect($service->isOnboardingComplete())->toBeTrue();
    expect($service->getDefaultBranchId())->toBe($branch->id);
    expect(data_get($service->getSettings(), 'business.type'))->toBe('clinic');
});

test('backfills null branch assignments to default branch', function () {
    $service = app(BusinessSettings::class);
    $branch = Branch::create([
        'name' => 'Main Branch',
        'address' => 'Test Address',
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

    $booking = Booking::create([
        'customer_id' => $customer->id,
        'listing_id' => $listing->id,
        'therapist_id' => $therapist->id,
        'bed_id' => $bed->id,
        'booking_number' => 'BK-00001',
        'title' => 'Test Booking',
        'type' => 'service',
        'price' => 100,
        'start_time' => now()->addHour(),
        'end_time' => now()->addHours(2),
        'status' => 'pending',
        'payment_status' => 'pending',
        'branch_id' => null,
    ]);

    $service->backfillBranchAssignments($branch->id);

    expect($therapist->fresh()->branch_id)->toBe($branch->id);
    expect($bed->fresh()->branch_id)->toBe($branch->id);
    expect($booking->fresh()->branch_id)->toBe($branch->id);
});


test('booking mode is normalized to supported values', function () {
    $service = app(BusinessSettings::class);

    $service->saveSettings([
        'booking' => [
            'mode' => 'invalid_mode',
        ],
    ]);

    expect(data_get($service->getSettings(), 'booking.mode'))->toBe('time_slot');
});

test('whole day mode normalizes booking start and end to day window', function () {
    $service = app(BusinessSettings::class);

    $service->saveSettings([
        'booking' => [
            'mode' => 'whole_day',
            'day_start' => '08:00',
            'day_end' => '18:00',
        ],
    ]);

    $service->applyRuntimeConfig();

    $customer = Customer::factory()->create(['is_vip' => false]);
    $listing = Listing::factory()->create();

    $booking = Booking::create([
        'customer_id' => $customer->id,
        'listing_id' => $listing->id,
        'booking_number' => 'BK-00011',
        'title' => 'Whole Day Booking',
        'type' => 'service',
        'price' => 150,
        'start_time' => '2026-03-20 11:30:00',
        'end_time' => '2026-03-20 12:30:00',
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    expect(Carbon\Carbon::parse($booking->fresh()->start_time)->format('Y-m-d H:i:s'))->toBe('2026-03-20 08:00:00');
    expect(Carbon\Carbon::parse($booking->fresh()->end_time)->format('Y-m-d H:i:s'))->toBe('2026-03-20 18:00:00');
});
