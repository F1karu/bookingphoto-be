<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;

class ExpirePayment extends Command
{
    protected $signature = 'payment:expire';
    protected $description = 'Set expired payments to failed automatically';

    public function handle()
    {
        $payments = Payment::where('payment_status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($payments as $payment) {
            $payment->payment_status = 'failed';
            $payment->auto_failed_at = now();
            $payment->save();

            $booking = $payment->booking;
            $booking->booking_status = 'CANCELED';
            $booking->save();
        }


        $this->info("Expired payments processed: " . count($payments));
    }
}
