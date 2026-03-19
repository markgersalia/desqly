<?php

use Illuminate\Database\Migrations\Migration;
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

        DB::table('companies')
            ->where('subscription_status', 'inactive')
            ->update([
                'subscription_status' => 'unpaid',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: converting unpaid back to inactive is lossy after canonicalization.
    }
};
