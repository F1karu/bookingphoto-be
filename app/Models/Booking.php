<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'base_price',
        'total_price',
        'note',
        'photographer_id',
        'booking_status',
    ];

    protected $dates = ['deleted_at'];

    // Relasi
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function photographer()
    {
        return $this->belongsTo(Photographer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
