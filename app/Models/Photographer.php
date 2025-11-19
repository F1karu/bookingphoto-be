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
        'location',
        'price_per_hour',
        'status',
        'category'
    ];

    protected $dates = ['deleted_at']; 

    public function city()
{
    return $this->belongsTo(City::class);
}

   
protected $appends = ['city_name'];

public function getCityNameAttribute()
{
    return $this->city ? $this->city->name : null;
}

protected $hidden = ['city_id'];


}
