<?php

use App\Filament\Pages\Tenancy\CompanyBilling;
use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('company billing uses canonical subscription statuses', function () {
    $page = app(CompanyBilling::class);
    $schema = $page->form(Schema::make($page));

    $statusField = collect($schema->getComponents())
        ->first(fn ($component) => $component instanceof Select && $component->getName() === 'subscription_status');

    expect($statusField)->not->toBeNull();

    $options = array_keys($statusField->getOptions());

    expect($options)->toBe(['trialing', 'active', 'past_due', 'unpaid', 'canceled'])
        ->and($options)->not->toContain('inactive');
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
