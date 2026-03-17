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
        Schema::table('therapist_leaves', function (Blueprint $table) {
            //\
            if (!Schema::hasColumn('therapist_leaves', 'type')) {
                $table->enum('type',['sick_leave','vacation_leave','emergency_leave','other']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('therapist_leaves', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
