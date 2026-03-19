<?php

use App\Filament\Pages\Dashboard;
use App\Livewire\BookingForm;
use App\Models\Company;
use App\Models\User;
use App\Services\BusinessSettings;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return mixed
 */
function callProtectedEntityMethod(object $object, string $method, array $arguments = [])
{
    $reflection = new ReflectionMethod($object, $method);
    $reflection->setAccessible(true);

    return $reflection->invokeArgs($object, $arguments);
}

test('dashboard branch filter is hidden in individual mode', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    app(BusinessSettings::class)->saveSettings([
        'onboarding' => [
            'completed_at' => now()->toDateTimeString(),
        ],
        'business' => [
            'entity_type' => 'individual',
        ],
        'branches' => [
            'default_branch_id' => null,
        ],
    ], $company);

    auth('web')->login($user);

    $page = app(Dashboard::class);
    $schema = $page->filtersForm(Schema::make($page));

    expect($schema->getComponents())->toHaveCount(0);
});

test('dashboard branch filter remains available in company mode', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    app(BusinessSettings::class)->saveSettings([
        'onboarding' => [
            'completed_at' => now()->toDateTimeString(),
        ],
        'business' => [
            'entity_type' => 'company',
        ],
    ], $company);

    auth('web')->login($user);

    $page = app(Dashboard::class);
    $schema = $page->filtersForm(Schema::make($page));

    $components = $schema->getComponents();

    expect($components)->toHaveCount(1);
    expect($components[0]->getName())->toBe('branch_id');
});

test('external booking form does not require branch in individual mode', function () {
    app(BusinessSettings::class)->saveSettings([
        'business' => [
            'entity_type' => 'individual',
        ],
    ]);

    $component = app(BookingForm::class);
    $component->mount();

    $rules = callProtectedEntityMethod($component, 'getValidationRules');

    expect($rules)->not->toHaveKey('selectedBranch');
});

test('external booking form requires branch in company mode', function () {
    app(BusinessSettings::class)->saveSettings([
        'business' => [
            'entity_type' => 'company',
        ],
    ]);

    $component = app(BookingForm::class);
    $component->mount();

    $rules = callProtectedEntityMethod($component, 'getValidationRules');

    expect($rules)->toHaveKey('selectedBranch');
});



test('external booking form does not require selected time in whole day mode', function () {
    app(BusinessSettings::class)->saveSettings([
        'booking' => [
            'mode' => 'whole_day',
        ],
    ]);
    app(BusinessSettings::class)->applyRuntimeConfig();

    $component = app(BookingForm::class);
    $component->mount();

    $rules = callProtectedEntityMethod($component, 'getValidationRules');

    expect($rules)->not->toHaveKey('selectedTime');
});

test('external booking form requires selected time in time slot mode', function () {
    app(BusinessSettings::class)->saveSettings([
        'booking' => [
            'mode' => 'time_slot',
        ],
    ]);
    app(BusinessSettings::class)->applyRuntimeConfig();

    $component = app(BookingForm::class);
    $component->mount();

    $rules = callProtectedEntityMethod($component, 'getValidationRules');

    expect($rules)->toHaveKey('selectedTime');
});
