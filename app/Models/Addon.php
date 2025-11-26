<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Addon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'auto_enhanced_photo',
        'type'
    ];

    protected $casts = [
        'auto_enhanced_photo' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    public function bookingAddons()
    {
        return $this->hasMany(BookingAddon::class);
    }
}
