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
        'location',
        'price_per_hour',
        'status',
    ];

    protected $dates = ['deleted_at']; 
}
