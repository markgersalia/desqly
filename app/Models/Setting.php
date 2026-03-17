<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    //
    protected $fillable = ['data'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
