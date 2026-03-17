<?php

use App\Http\Middleware\EnsureOnboardingCompleted;
use App\Models\User;
use App\Services\BusinessSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    Route::middleware(['web', EnsureOnboardingCompleted::class])->group(function () {
        Route::get('/test-admin/dashboard', fn () => 'dashboard')->name('filament.admin.pages.dashboard');
        Route::get('/test-admin/onboarding', fn () => 'onboarding')->name('filament.admin.pages.onboarding');
        Route::post('/test-admin/logout', fn () => 'logout')->name('filament.admin.auth.logout');
    });
});

test('redirects authenticated users to onboarding when incomplete', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/test-admin/dashboard');

    $response->assertRedirect(route('filament.admin.pages.onboarding'));
});

test('allows onboarding route when incomplete', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/test-admin/onboarding');

    $response->assertOk()->assertSeeText('onboarding');
});

test('allows dashboard when onboarding is complete', function () {
    $user = User::factory()->create();

    app(BusinessSettings::class)->saveSettings([
        'onboarding' => [
            'completed_at' => now()->toDateTimeString(),
        ],
    ]);

    $response = $this->actingAs($user)->get('/test-admin/dashboard');

    $response->assertOk()->assertSeeText('dashboard');
});
