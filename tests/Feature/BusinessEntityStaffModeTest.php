<?php

use App\Filament\Clusters\Therapist\Resources\TherapistLeaves\TherapistLeaveResource;
use App\Filament\Clusters\Therapist\TherapistCluster;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Filament\Resources\Therapists\TherapistResource;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Listing;
use App\Services\BusinessSettings;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * @return mixed
 */
function findSchemaComponentByName(array $components, string $name)
{
    foreach ($components as $component) {
        if (method_exists($component, 'getName') && $component->getName() === $name) {
            return $component;
        }

        $children = [];

        if (method_exists($component, 'getDefaultChildComponents')) {
            try {
                $children = $component->getDefaultChildComponents();
            } catch (\Throwable $e) {
                $children = [];
            }
        }

        if (empty($children) && method_exists($component, 'getChildComponents')) {
            try {
                $children = $component->getChildComponents();
            } catch (\Throwable $e) {
                $children = [];
            }
        }

        if (empty($children) && method_exists($component, 'getComponents')) {
            try {
                $children = $component->getComponents();
            } catch (\Throwable $e) {
                $children = [];
            }
        }

        if (! is_array($children) || empty($children)) {
            continue;
        }

        $found = findSchemaComponentByName($children, $name);

        if ($found) {
            return $found;
        }
    }

    return null;
}

/**
 * @return array<int, callable>
 */
function getDisableOptionCallbacks(ToggleButtons $component): array
{
    $reflection = new ReflectionProperty($component, 'isOptionDisabled');
    $reflection->setAccessible(true);

    /** @var array<int, callable> $callbacks */
    $callbacks = $reflection->getValue($component);

    return $callbacks;
}

test('booking form requires therapist selection in company mode', function () {
    app(BusinessSettings::class)->saveSettings([
        'business' => [
            'entity_type' => 'company',
        ],
    ]);

    $therapistField = findSchemaComponentByName(BookingForm::schema(), 'therapist_id');

    expect($therapistField)->not->toBeNull();
    expect($therapistField->isVisible())->toBeTrue();
    expect($therapistField->isRequired())->toBeTrue();
});

test('booking form hides therapist selection in individual mode', function () {
    app(BusinessSettings::class)->saveSettings([
        'business' => [
            'entity_type' => 'individual',
        ],
    ]);

    $therapistField = findSchemaComponentByName(BookingForm::schema(), 'therapist_id');

    expect($therapistField)->not->toBeNull();
    expect($therapistField->isVisible())->toBeFalse();
    expect($therapistField->isRequired())->toBeFalse();
});

test('individual mode blocks overlapping slots without therapist assignment', function () {
    $company = Company::factory()->create();

    app(BusinessSettings::class)->saveSettings([
        'business' => [
            'entity_type' => 'individual',
        ],
    ], $company);

    $customer = Customer::factory()->create(['company_id' => $company->id, 'is_vip' => false]);
    $listing = Listing::factory()->create(['company_id' => $company->id]);

    $date = now()->addDay()->toDateString();

    DB::table('bookings')->insert([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'listing_id' => $listing->id,
        'booking_number' => 'BK-90010',
        'title' => 'Conflict Booking',
        'type' => 'service',
        'price' => 500,
        'start_time' => "$date 09:00:00",
        'end_time' => "$date 10:00:00",
        'status' => 'confirmed',
        'payment_status' => 'pending',
        'branch_id' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $timeslotField = findSchemaComponentByName(BookingForm::schema(), 'available_timeslots');

    expect($timeslotField)->toBeInstanceOf(ToggleButtons::class);

    $callbacks = getDisableOptionCallbacks($timeslotField);
    $callback = $callbacks[0] ?? null;

    expect($callback)->not->toBeNull();

    $state = [
        'selected_date' => $date,
        'branch_id' => null,
    ];

    $isDisabled = $callback(
        '09:00 AM - 10:00 AM',
        fn (string $key) => data_get($state, $key),
        null
    );

    expect($isDisabled)->toBeTrue();

    expect(Booking::query()->where('booking_number', 'BK-90010')->exists())->toBeTrue();
});

test('therapist navigation is hidden in individual mode and shown in company mode', function () {
    app(BusinessSettings::class)->saveSettings([
        'business' => [
            'entity_type' => 'individual',
        ],
    ]);

    expect(TherapistCluster::shouldRegisterNavigation())->toBeFalse();
    expect(TherapistResource::shouldRegisterNavigation())->toBeFalse();
    expect(TherapistLeaveResource::shouldRegisterNavigation())->toBeFalse();

    app(BusinessSettings::class)->saveSettings([
        'business' => [
            'entity_type' => 'company',
        ],
    ]);

    expect(TherapistCluster::shouldRegisterNavigation())->toBeTrue();
    expect(TherapistResource::shouldRegisterNavigation())->toBeTrue();
    expect(TherapistLeaveResource::shouldRegisterNavigation())->toBeTrue();
});

test('booking form timeslot selection is not required in whole day mode', function () {
    app(BusinessSettings::class)->saveSettings([
        'booking' => [
            'mode' => 'whole_day',
        ],
    ]);
    app(BusinessSettings::class)->applyRuntimeConfig();

    $timeslotField = findSchemaComponentByName(BookingForm::schema(), 'available_timeslots');

    expect($timeslotField)->not->toBeNull();
    expect($timeslotField->isRequired())->toBeFalse();
});

test('booking form timeslot selection remains required in time slot mode', function () {
    app(BusinessSettings::class)->saveSettings([
        'booking' => [
            'mode' => 'time_slot',
        ],
    ]);
    app(BusinessSettings::class)->applyRuntimeConfig();

    $timeslotField = findSchemaComponentByName(BookingForm::schema(), 'available_timeslots');

    expect($timeslotField)->not->toBeNull();
    expect($timeslotField->isRequired())->toBeTrue();
});
