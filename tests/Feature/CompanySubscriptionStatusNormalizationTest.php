<?php

use App\Filament\Pages\Tenancy\CompanyBilling;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('company billing keeps canonical subscription statuses contract', function () {
    expect(CompanyBilling::CANONICAL_SUBSCRIPTION_STATUSES)
        ->toBe(['trialing', 'active', 'past_due', 'unpaid', 'canceled'])
        ->and(CompanyBilling::CANONICAL_SUBSCRIPTION_STATUSES)
        ->not->toContain('inactive');
});

test('subscription status normalization migration converts inactive to unpaid', function () {
    $inactiveCompany = Company::factory()->create(['subscription_status' => 'inactive']);
    $unpaidCompany = Company::factory()->create(['subscription_status' => 'unpaid']);

    $migration = require database_path('migrations/2026_03_18_130000_normalize_company_subscription_statuses.php');

    $migration->up();

    expect($inactiveCompany->fresh()->subscription_status)->toBe('unpaid')
        ->and($unpaidCompany->fresh()->subscription_status)->toBe('unpaid');

    $migration->up();

    expect($inactiveCompany->fresh()->subscription_status)->toBe('unpaid');
});
