<?php

namespace App\Models;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;
 

class CustomerPostAssesment extends Model
{
    //
    protected $fillable = [
        'primary_concern',
        'listing_id',
        'booking_id',
        'customer_id',
        'start_time',
        'end_time',
        'bp',
        'pr',
        'o2',
        'therapist_id',
        'therapist_rating',
        'post_pain_rating',
        'client_remarks',
        'next_session_date'
    ];

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function booking(){
        return $this->belongsTo(Booking::class);
    }

    public function listing(){
        return $this->belongsTo(Listing::class);
    }
    public function therapist(){
        return $this->belongsTo(Therapist::class);
    }
     


}
