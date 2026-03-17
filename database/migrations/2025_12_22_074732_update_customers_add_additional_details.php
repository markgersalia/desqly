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
        Schema::table('customers', function (Blueprint $table) {
            //
            $table->string('gender')->nullable();
            $table->string('messenger')->nullable();
            $table->string('occupation')->nullable();
            $table->string('target_area')->nullable();
            $table->string('health_history')->nullable();
            $table->integer('pain_upon_cunsoltation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            
            $table->dropColumn('gender',
            'messenger',
            'occupation',
            'target_area',
            'health_history',
            'pain_upon_cunsoltation',
            );
        });
    }
};
