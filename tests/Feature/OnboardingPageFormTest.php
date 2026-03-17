<?php

use App\Filament\Pages\OnboardingPage;
use App\Models\Branch;
use App\Models\User;
use App\Services\BusinessSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('onboarding page validates required fields before submit', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OnboardingPage::class)
        ->set('data.business.name', '')
        ->set('data.business.type', null)
        ->set('data.business.timezone', null)
        ->set('data.business.currency', '')
        ->set('data.initial_branch.name', '')
        ->call('submit')
        ->assertHasErrors([
            'data.business.name',
            'data.business.type',
            'data.business.timezone',
            'data.business.currency',
            'data.initial_branch.name',
        ]);
});

test('template selection updates booking and label defaults', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OnboardingPage::class)
        ->set('data.business.type', 'clinic')
        ->assertSet('data.booking.requires_bed', false)
        ->assertSet('data.booking.requires_follow_up', true)
        ->assertSet('data.labels.staff', 'Practitioner')
        ->assertSet('data.labels.resource', 'Room');
});

test('onboarding submit persists settings and creates default branch', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OnboardingPage::class)
        ->set('data.business.name', 'Test Wellness')
        ->set('data.business.type', 'gym')
        ->set('data.business.timezone', 'Asia/Manila')
        ->set('data.business.currency', 'php')
        ->set('data.booking.has_listings', true)
        ->set('data.booking.requires_bed', false)
        ->set('data.booking.requires_follow_up', false)
        ->set('data.booking.slot_interval_minutes', 60)
        ->set('data.booking.day_start', '09:00')
        ->set('data.booking.day_end', '18:00')
        ->set('data.booking.expire_after_hours', 24)
        ->set('data.booking.grace_period_minutes', 30)
        ->set('data.labels.staff', 'Trainer')
        ->set('data.labels.resource', 'Station')
        ->set('data.labels.service', 'Session')
        ->set('data.labels.booking', 'Booking')
        ->set('data.initial_branch.name', 'Main Branch')
        ->set('data.initial_branch.address', '123 Test')
        ->call('submit');

    $branch = Branch::query()->where('name', 'Main Branch')->first();

    expect($branch)->not->toBeNull();
    expect(app(BusinessSettings::class)->isOnboardingComplete())->toBeTrue();
    expect(app(BusinessSettings::class)->getDefaultBranchId())->toBe($branch->id);
});
