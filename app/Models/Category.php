<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //

    protected $fillable = ['name','description','slug'];

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }
}
