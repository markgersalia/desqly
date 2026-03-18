<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'email', 'phone', 'address','is_vip','image','created_by_id'];

    public function bookings(){
        return $this->hasMany(Booking::class);
    }

    public function post_assestments(){ 
        return $this->hasMany(CustomerPostAssesment::class);
    }

    
    public function displayNameWithStatus(){
        $is_vip = $this->is_vip;
        if($is_vip){
            return $this->name . " (VIP)";            
        }
        return $this->name;
    }
}
