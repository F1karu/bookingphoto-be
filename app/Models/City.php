<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'province',
    ];

    public function photographers()
    {
        return $this->hasMany(Photographer::class);
    }
}
