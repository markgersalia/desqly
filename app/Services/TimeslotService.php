<?php

namespace App\Services;

use Carbon\Carbon; 
class TimeslotService
{
    public static function generateForDay($date, $interval = null, $start = null, $end = null)
    {
        $interval ??= (int) config('booking.slot_interval_minutes', 60);
        $start ??= config('booking.day_start', '09:00');
        $end ??= config('booking.day_end', '17:00');

        $slots = [];

        $startTime = Carbon::parse("$date $start");
        $endTime   = Carbon::parse("$date $end");

        while ($startTime->copy()->addMinutes($interval) <= $endTime) {
            $slotStart = $startTime->copy();
            $slotEnd   = $startTime->copy()->addMinutes($interval);

            $slots[] = [
                'start' => $slotStart,
                'end'   => $slotEnd,
                'label' => $slotStart->format('h:i a') . " - " . $slotEnd->format('h:i a')
            ];

            $startTime->addMinutes($interval);
        }

        return $slots;
    }
}

