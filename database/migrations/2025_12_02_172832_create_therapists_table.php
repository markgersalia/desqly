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
        Schema::create('therapists', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('name');
            $table->string('bio');
            
            // Link therapist to a branch
            $table->foreignId('branch_id')->nullable();
            
            // Availability in JSON format (e.g., {"Mon":["9:00-12:00","14:00-18:00"], ...})
            $table->json('availability')->nullable();

            // Optional: contact info, email, phone
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();

            // Optional: active status
            $table->boolean('is_active')->default(true);
 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('therapists');
    }
};
