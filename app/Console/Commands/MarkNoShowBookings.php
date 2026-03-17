<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\BookingNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkNoShowBookings extends Command
{
    protected $signature = 'booking:no-show';

    protected $description = 'Mark confirmed bookings as no_show when grace period has passed and payment is not made';

    public function handle()
    {
        $gracePeriodMinutes = config('booking.grace_period_minutes', 30);
        $cutoff = Carbon::now()->subMinutes($gracePeriodMinutes);

        $bookings = Booking::where('status', 'confirmed')
            ->whereIn('payment_status', ['pending', 'partially_paid'])
            ->whereDate('start_time', Carbon::now()->toDateString())
            ->where('start_time', '<', $cutoff)
            ->whereNull('deleted_at')
            ->get();

        $count = 0;
        $notificationService = app(BookingNotificationService::class);

        foreach ($bookings as $booking) {
            $booking->update(['status' => 'expired', 'no_show' => true]);
            $notificationService->sendNotification($booking, 'no_show');
            $count++;
        }

        $this->info("Marked {$count} bookings as expired (no_show) after {$gracePeriodMinutes} minutes.");

        return Command::SUCCESS;
    }
}
