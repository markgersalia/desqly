<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'branch_id',
        'description',
        'is_available',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /* --------------------
     | Scopes
     -------------------- */

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    
    public function isAvailable($date, $startTime, $endTime, $excludeBookingId = null)
    {

        $start = Carbon::parse($startTime);
        $end   = Carbon::parse($endTime);

        // Override date to avoid double-date issue
        $start->setDate(Carbon::parse($date)->year, Carbon::parse($date)->month, Carbon::parse($date)->day);
        $end->setDate(Carbon::parse($date)->year, Carbon::parse($date)->month, Carbon::parse($date)->day);


        return !$this->bookings()
            ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId))
            ->confirmed()
            ->whereDate('start_time', $date)
            ->where(function ($q) use ($start, $end) {
                $q->where(function ($q2) use ($start, $end) {
                    $q2->where('start_time', '<', $end)
                        ->where('end_time', '>', $start);
                });
            })
            ->exists();
    }
}
