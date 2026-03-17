<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'address',
        'is_active',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function therapists()
    {
        return $this->hasMany(Therapist::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
