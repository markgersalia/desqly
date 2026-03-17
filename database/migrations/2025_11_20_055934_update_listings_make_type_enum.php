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
               
            // Enum for listing types
            $table->enum('type', [
                'room',
                'service',
                'event',
                'apartment',
                'house',
                'studio',
                'transport',
                'equipment',
                'experience',
                'misc',
                'medical'
            ])->default('misc')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            //
            $table->string('type')->change();
        });
    }
};
