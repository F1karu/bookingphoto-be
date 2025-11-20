<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $fillable = [
        'name',
        'price',
        'auto_enhanced_photo',
        'type'
    ];

    public function bookingAddons()
    {
        return $this->hasMany(BookingAddon::class);
    }
}
