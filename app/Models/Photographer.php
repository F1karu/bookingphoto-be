<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Photographer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'bio',
        'photo_url',
        'city_id',
        'status',
        'category',
        'price_type'
    ];

    protected $dates = ['deleted_at'];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /*
    |--------------------------------------------------------------------------
    | APPENDED ATTRIBUTE
    |--------------------------------------------------------------------------
    */
    protected $appends = [
        'city_name',
        'price_per_hour'
    ];

    public function getCityNameAttribute()
    {
        return $this->city ? $this->city->name : null;
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO PRICE CALCULATION
    |--------------------------------------------------------------------------
    */
    public function getPricePerHourAttribute()
    {
        return $this->price_type === 'professional' ? 500000 : 250000;
    }

    protected $hidden = ['city_id'];
}
