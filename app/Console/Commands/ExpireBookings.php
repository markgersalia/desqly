<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\BookingNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireBookings extends Command
{
    protected $signature = 'booking:expire';

    protected $description = 'Expire bookings that have passed their start time by configured hours';

    public function handle()
    {
        $hours = config('booking.expire_after_hours', 24);
        $cutoff = Carbon::now()->subHours($hours);

        $bookings = Booking::whereIn('status', ['pending', 'confirmed'])
            ->where('start_time', '<', $cutoff)
            ->whereNull('deleted_at')
            ->get();

        $count = 0;
        $notificationService = app(BookingNotificationService::class);

        foreach ($bookings as $booking) {
            $booking->update(['status' => 'expired', 'no_show' => false]);
            $notificationService->sendNotification($booking, 'expired');
            $count++;
        }
                       
        $this->info("Expired {$count} bookings older than {$hours} hours.");

        return Command::SUCCESS;
    }
}
