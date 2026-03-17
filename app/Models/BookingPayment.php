<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingPayment extends Model
{

    protected $fillable = ['booking_id', 'amount', 'payment_status','processed_by_id','payment_method','payment_reference'];
    //

    public function booking(){
        return $this->belongsTo(Booking::class);
    }

    public function processed_by(){
        return $this->belongsTo(User::class);
    }

    public function scopePaid($q){
        return $q->where('payment_status', 'paid');
    }
    
    /**
     * The "booted" method of the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Before creating a new Customer
        static::creating(function ($data) {
            // Generate a unique code
            $data->processed_by_id = auth()->user()->id;
            
        });

        // Before saving (both creating and updating)
        static::saving(function ($data) {
            // Example: ensure name is title-cased
            $data->processed_by_id = auth()->user()->id;
        });
    }

}
