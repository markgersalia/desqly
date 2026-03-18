<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
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
