<?php

namespace App\Providers;

use App\Services\BusinessSettings;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BusinessSettings::class, fn () => new BusinessSettings());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            app(BusinessSettings::class)->applyRuntimeConfig();
        } catch (\Throwable $e) {
            // Ignore during fresh installs/migrations where settings tables may not exist yet.
        }
    }
}
