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
