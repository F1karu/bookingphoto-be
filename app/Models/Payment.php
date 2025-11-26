<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'order_id',
        'amount',
        'payment_method',   // MANUAL / MIDTRANS
        'options',          // QRIS / BANK_TRANSFER (hanya untuk manual)
        'payment_status',   // pending / paid / failed
        'transaction_status',
        'fraud_status',
        'midtrans_payload',
        'snap_token',
        'redirect_url',
        'proof',
        'expires_at',
    'auto_failed_at'
    ];

    protected $casts = [
        'midtrans_payload' => 'array'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
