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
        Schema::create('customer_post_assesments', function (Blueprint $table) {
            $table->id();
            $table->string('primary_concern')->nullable();
            $table->foreignId('booking_id')->nullable();
            $table->foreignId('customer_id')->nullable();
            $table->foreignId('listing_id')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('bp')->nullable();
            $table->string('pr')->nullable();
            $table->string('o2')->nullable();
            $table->foreignId('therapist_id')->nullable();
            $table->integer('therapist_rating')->nullable();
            $table->integer('post_pain_rating')->nullable();
            $table->string('client_remarks')->nullable();
            $table->date('next_session_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_post_assesments');
    }
};
