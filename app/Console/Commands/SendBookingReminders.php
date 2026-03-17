<?php

namespace App\Console\Commands;

use App\Mail\BookingMailNotification;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:reminders';
    protected $description = 'Send reminder emails for upcoming bookings';
 
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $now = Carbon::today(); // today 00:00:00
        $reminderTime = $now->copy()->addDays(1)->endOfDay(); // 12:00 AM tomorrow 11:59:59 PM


        $bookings = Booking::confirmed()
            ->notYetReminded()
            ->whereBetween('start_time', [$now, $reminderTime])
            ->get();
 
        foreach ($bookings as $booking) {
            $subject = config('app.name') . " Upcoming Booking Reminder – [#{$booking->booking_number}]";
            $template = 'mails.bookings.reminder'; // create a new reminder markdown email

            $booking->reminded_at = $now;
            $booking->save();

            Mail::to($booking->customer->email)
                ->queue(new BookingMailNotification($subject, $template, $booking->toArray()));

            $this->info("Reminder sent for booking #{$booking->booking_number}");
        }
    }
}
