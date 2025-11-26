<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Photographer;
use App\Models\Payments;


class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_phone',
        'date',
        'start_time',
        'end_time',
        'duration',
        'photographer_id',
        'price_type',
        'base_price',
        'total_price',
        'total_addons_price',
        'note',
        'booking_status',
    ];

    protected $dates = ['deleted_at'];

    /*
    |--------------------------------------------------------------------------
    | ENUM Base Price Getter
    |--------------------------------------------------------------------------
    */
    public static function getBasePriceByType($type)
    {
        return match ($type) {
            'normal' => 250000,        
            'professional' => 500000,
            default => 250000,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */
    public function photographer()
    {
        return $this->belongsTo(Photographer::class);
    }

    public function bookingAddons()
    {
        return $this->hasMany(BookingAddon::class);
    }

    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function payment()
{
    return $this->hasOne(Payment::class);
}


}
