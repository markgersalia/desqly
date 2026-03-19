<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'company_id')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'company_id')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $fallbackCompanyId = Company::query()->value('id');

        if (! $fallbackCompanyId) {
            $name = config('app.name', 'Default Company');
            $baseSlug = Str::slug($name) ?: 'default-company';
            $slug = $baseSlug;
            $suffix = 2;

            while (Company::query()->where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            $fallbackCompanyId = Company::query()->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'plan_code' => 'starter',
                'subscription_status' => 'active',
                'trial_ends_at' => now()->addDays(14),
                'current_period_ends_at' => now()->addMonth(),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('users')
            ->whereNull('company_id')
            ->update(['company_id' => $fallbackCompanyId]);

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable(false)->change();
        });
    }
};

