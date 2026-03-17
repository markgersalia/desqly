<?php

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['booking.expire_after_hours' => 24]);
});

test('expires pending bookings older than configured hours', function () {
    $oldBooking = Booking::factory()->create([
        'status' => 'pending',
        'start_time' => Carbon::now()->subHours(25),
    ]);

    $this->artisan('booking:expire');

    $oldBooking->refresh();
    expect($oldBooking->status)->toBe('expired');
});

test('expires confirmed bookings older than configured hours', function () {
    $oldBooking = Booking::factory()->create([
        'status' => 'confirmed',
        'start_time' => Carbon::now()->subHours(25),
    ]);

    $this->artisan('booking:expire');

    $oldBooking->refresh();
    expect($oldBooking->status)->toBe('expired');
});

test('does not expire bookings within time limit', function () {
    $recentBooking = Booking::factory()->create([
        'status' => 'pending',
        'start_time' => Carbon::now()->subHours(12),
    ]);

    $this->artisan('booking:expire');

    $recentBooking->refresh();
    expect($recentBooking->status)->toBe('pending');
});

test('does not expire already canceled bookings', function () {
    $canceledBooking = Booking::factory()->create([
        'status' => 'canceled',
        'start_time' => Carbon::now()->subHours(25),
    ]);

    $this->artisan('booking:expire');

    $canceledBooking->refresh();
    expect($canceledBooking->status)->toBe('canceled');
});

test('does not expire already completed bookings', function () {
    $completedBooking = Booking::factory()->create([
        'status' => 'completed',
        'start_time' => Carbon::now()->subHours(25),
    ]);

    $this->artisan('booking:expire');

    $completedBooking->refresh();
    expect($completedBooking->status)->toBe('completed');
});

test('uses custom hours from config', function () {
    config(['booking.expire_after_hours' => 2]);

    $booking = Booking::factory()->create([
        'status' => 'pending',
        'start_time' => Carbon::now()->subHours(3),
    ]);

    $this->artisan('booking:expire');

    $booking->refresh();
    expect($booking->status)->toBe('expired');
});

test('does not expire booking newer than custom hours', function () {
    config(['booking.expire_after_hours' => 48]);

    $booking = Booking::factory()->create([
        'status' => 'pending',
        'start_time' => Carbon::now()->subHours(24),
    ]);

    $this->artisan('booking:expire');

    $booking->refresh();
    expect($booking->status)->toBe('pending');
});

test('reports correct count of expired bookings', function () {
    Booking::factory()->count(3)->create([
        'status' => 'pending',
        'start_time' => Carbon::now()->subHours(25),
    ]);

    $this->artisan('booking:expire')
        ->assertExitCode(0)
        ->expectsOutput('Expired 3 bookings older than 24 hours.');
});
