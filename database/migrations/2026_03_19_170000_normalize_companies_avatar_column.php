<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        $hasAvatar = Schema::hasColumn('companies', 'avatar');
        $hasLogo = Schema::hasColumn('companies', 'logo');

        if (! $hasAvatar) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('avatar')->nullable();
            });
        }

        if ($hasLogo) {
            DB::table('companies')
                ->whereNull('avatar')
                ->whereNotNull('logo')
                ->update([
                    'avatar' => DB::raw('logo'),
                ]);

            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('logo');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank. This migration normalizes legacy schemas.
    }
};
