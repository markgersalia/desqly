<?php

namespace App\Models;

use Filament\Models\Contracts\HasCurrentTenantLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Company extends Model implements HasCurrentTenantLabel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'plan_code',
        'subscription_status',
        'trial_ends_at',
        'current_period_ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $company): void {
            if (blank($company->slug)) {
                $company->slug = Str::slug($company->name);
            }
        });
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function therapists()
    {
        return $this->hasMany(Therapist::class);
    }

    public function therapistLeaves()
    {
        return $this->hasMany(TherapistLeave::class);
    }

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function bookingPayments()
    {
        return $this->hasMany(BookingPayment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function settings()
    {
        return $this->hasOne(Setting::class);
    }

    public function isSubscribed(): bool
    {
        return in_array($this->subscription_status, ['active', 'trialing'], true);
    }

    public function canWrite(): bool
    {
        return $this->is_active && $this->isSubscribed();
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Current company';
    }
}

