<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Therapist extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'image', 'name', 'bio', 'availability', 'branch_id', 'email', 'phone', 'is_active'];

    protected $casts = [
        'boolean' => 'is_active'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    
    public function post_assestments(){ 
        return $this->hasMany(CustomerPostAssesment::class);
    }

    public function leaves()
    {
        return $this->hasMany(TherapistLeave::class);
    }
    public function scopeActive($q)
    {
        return $q->where('is_active', 1);
    }

    public function getRating(){
        return round($this->post_assestments()->average('therapist_rating'));
    }

    // public function isAvailable($date, $startTime, $endTime)
    // {
    //     $start = Carbon::parse($startTime);
    //     $end   = Carbon::parse($endTime);

    //     // Override date to avoid double-date issue
    //     $start->setDate(Carbon::parse($date)->year, Carbon::parse($date)->month, Carbon::parse($date)->day);
    //     $end->setDate(Carbon::parse($date)->year, Carbon::parse($date)->month, Carbon::parse($date)->day);

    //     return !$this->bookings()
    //         ->confirmed()
    //         ->whereDate('start_time', $date)
    //         ->where(function ($q) use ($start, $end) {
    //             $q->where('start_time', '<', $end)
    //                 ->where('end_time', '>', $start);
    //         })
    //         ->exists();
    // }

    public function isOnLeave($start, $end): bool
    {

        $start = Carbon::parse($start)->startOfDay();
        $end   = Carbon::parse($end)->endOfDay();
        

        return $this->leaves()
            ->where('start_date', '<=', $end->toDateTimeString())
            ->where('end_date', '>=', $start->toDateTimeString())
            ->exists();
    }


    public function isAvailable($date, $startTime, $endTime, $excludeBookingId = null)
    {

        $start = Carbon::parse($startTime);
        $end   = Carbon::parse($endTime);

        //     // Override date to avoid double-date issue
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
