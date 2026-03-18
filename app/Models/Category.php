<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name','description','slug'];

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }
}
