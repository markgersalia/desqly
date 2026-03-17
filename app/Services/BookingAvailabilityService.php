<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Therapist;
use App\Models\Bed;
use Carbon\Carbon;

class BookingAvailabilityService
{
    public static function hasAvailableTherapist(
        string $date,
        string $slotStart,
        string $slotEnd,
        int $branchId,
        ?int $ignoreBookingId = null
    ): bool {
        $slotStart = Carbon::parse("$date $slotStart");
        $slotEnd   = Carbon::parse("$date $slotEnd");

        $therapists = Therapist::where('branch_id', $branchId)
            ->active()
            ->get();

        foreach ($therapists as $therapist) {
            $hasConflict = $therapist->bookings()
                ->confirmed()
                ->whereDate('start_time', $date)
                ->when($ignoreBookingId, fn ($q) => $q->where('id', '!=', $ignoreBookingId))
                ->where(function ($q) use ($slotStart, $slotEnd) {
                    $q->where('start_time', '<', $slotEnd)
                      ->where('end_time', '>', $slotStart);
                })
                ->exists();

            if (! $hasConflict) {
                return true; // at least one therapist free
            }
        }

        return false;
    }

    public static function therapistIsAvailable(
        int $therapistId,
        string $date,
        string $start,
        string $end,
        ?int $ignoreBookingId = null
    ): bool {
        $therapist = Therapist::active()->find($therapistId);

        if (! $therapist) {
            return false;
        }

        if ($therapist->isOnLeave($start, $end)) {
            return false;
        }

        return $therapist->isAvailable($date, $start, $end, $ignoreBookingId);
    }

    public static function bedIsAvailable(
        int $bedId,
        string $date,
        string $start,
        string $end,
        ?int $ignoreBookingId = null
    ): bool {
        $bed = Bed::available()->find($bedId);

        if (! $bed) {
            return false;
        }

        return $bed->isAvailable($date, $start, $end, $ignoreBookingId);
    }
}
