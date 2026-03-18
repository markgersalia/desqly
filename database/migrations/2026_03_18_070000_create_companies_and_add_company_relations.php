<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('plan_code')->default('starter');
            $table->string('subscription_status')->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $tenantTables = [
            'users',
            'branches',
            'customers',
            'listings',
            'categories',
            'therapists',
            'therapist_leaves',
            'beds',
            'bookings',
            'booking_payments',
            'invoices',
            'settings',
            'customer_post_assesments',
        ];

        foreach ($tenantTables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'company_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('companies')
                    ->cascadeOnDelete();
            });
        }

        $settingsRow = Schema::hasTable('settings')
            ? DB::table('settings')->select('data')->first()
            : null;

        $rawSettingsData = $settingsRow?->data;
        $settingsData = is_string($rawSettingsData) ? json_decode($rawSettingsData, true) : null;
        $settingsData = is_array($settingsData) ? $settingsData : [];

        $nameFromSettings = data_get($settingsData, 'business.name');
        $companyName = is_string($nameFromSettings) && $nameFromSettings !== '' ? $nameFromSettings : config('app.name', 'Default Company');

        $baseSlug = Str::slug($companyName);
        $slug = $baseSlug !== '' ? $baseSlug : 'default-company';
        $suffix = 2;

        while (DB::table('companies')->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        $companyId = DB::table('companies')->insertGetId([
            'name' => $companyName,
            'slug' => $slug,
            'plan_code' => 'starter',
            'subscription_status' => 'active',
            'trial_ends_at' => now()->addDays(14),
            'current_period_ends_at' => now()->addMonth(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($tenantTables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'company_id')) {
                continue;
            }

            DB::table($tableName)->whereNull('company_id')->update(['company_id' => $companyId]);
        }

        if (DB::getDriverName() !== 'sqlite') {
            foreach ($tenantTables as $tableName) {
                if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'company_id')) {
                    continue;
                }

                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('company_id')->nullable(false)->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tenantTables = [
            'users',
            'branches',
            'customers',
            'listings',
            'categories',
            'therapists',
            'therapist_leaves',
            'beds',
            'bookings',
            'booking_payments',
            'invoices',
            'settings',
            'customer_post_assesments',
        ];

        foreach ($tenantTables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'company_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $foreignKey = "{$tableName}_company_id_foreign";

                if (Schema::hasColumn($tableName, 'company_id')) {
                    try {
                        $table->dropForeign($foreignKey);
                    } catch (\Throwable $e) {
                    }

                    $table->dropColumn('company_id');
                }
            });
        }

        Schema::dropIfExists('companies');
    }
};

