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
        Schema::create('therapist_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('therapist_id');
            $table->string('reason');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->enum('type',['sick_leave','vacation_leave','emergency_leave','other']);
            $table->enum('status',['pending','approved','rejected']); 
            // $table->softDeletesIfNotExists
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('therapist_leaves');
    }
};
