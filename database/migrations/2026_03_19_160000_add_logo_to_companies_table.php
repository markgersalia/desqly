<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('companies') || Schema::hasColumn('companies', 'avatar')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('companies') || ! Schema::hasColumn('companies', 'avatar')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
    }
};
