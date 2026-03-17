<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompleteBookings extends Command
{
    protected $signature = 'booking:complete';

    protected $description = 'Mark confirmed bookings with paid status as completed when end time has passed';

    public function handle()
    {
        $now = Carbon::now();

        $bookings = Booking::where('status', 'confirmed')
            ->where('payment_status', 'paid')
            ->where('end_time', '<', $now)
            ->whereNull('deleted_at')
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {
            $booking->update(['status' => 'completed']);
            $count++;
        }

        $this->info("Completed {$count} bookings.");

        return Command::SUCCESS;
    }
}
