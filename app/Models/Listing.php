<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Listing extends Model
{
    //
     use HasFactory;

    protected $fillable = [
        'images',
        'title',
        'description',
        'type',    // e.g., room, service, event
        'category_id',    // e.g., room, service, event
        'duration', // service duration in minutes
        'price',   // optional
        'available_from', // optional JSON or boolean
        'available_to', // optional JSON or boolean
        'is_always_available', // optional JSON or boolean
        'availability', // JSON availability data
    ];

    protected $appends = ['availability'];

    protected $casts = [
        'images' => 'array',
        'availability' => 'array'
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function getAvailabilityAttribute(){
        $avail = $this->attributes['availability'] ?? [];
        
        if (isset($avail['always_available']) && $avail['always_available']) {
            return "Always Available";
        }
        
        if (isset($avail['from']) && isset($avail['to'])) {
            return "Available on: " . $avail['from'] . " to " . $avail['to'];
        }
        
        return "Limited Availability";
    }

    public function isAvailable($date)
    {
        $availability = $this->attributes['availability'] ?? [];
        
        // If always available
        if (isset($availability['always_available']) && $availability['always_available']) {
            return true;
        }

        // Check date against available range
        if (isset($availability['from']) && isset($availability['to'])) {
            $checkDate = Carbon::parse($date);
            $fromDate = Carbon::parse($availability['from']);
            $toDate = Carbon::parse($availability['to']);

            return $checkDate->between($fromDate, $toDate);
        }

        // If no specific availability rules, assume available
        return true;
    }
} 
