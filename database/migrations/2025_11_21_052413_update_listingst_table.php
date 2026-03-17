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
        Schema::table('listings', function (Blueprint $table) {
            //
            $table->boolean('is_always_available')->default(false);    
            $table->timestamp('available_from')->nullable()->change();
            $table->timestamp('available_to')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('is_always_available');
            
            $table->timestamp('available_from')->change();
            $table->timestamp('available_to')->change();
        });
    }
};
