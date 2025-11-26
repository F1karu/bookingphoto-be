<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    public function store(Request $request, $bookingId)
    {
        $request->validate([
            'payment_method' => 'required|in:MANUAL,MIDTRANS',
            'options'        => 'required_if:payment_method,MANUAL|in:QRIS,BANK_TRANSFER'
        ]);

        $booking = Booking::findOrFail($bookingId);

        if ($booking->payment) {
            return response()->json([
                'message' => 'Payment already selected for this booking'
            ], 409);
        }

        $amount = $booking->total_price;

        $payment = Payment::create([
    'booking_id'     => $booking->id,
    'order_id'       => 'ORDER-' . strtoupper(Str::random(10)),
    'amount'         => $amount,
    'payment_method' => $request->payment_method,
    'options'        => $request->payment_method === 'MANUAL' ? $request->options : null,
    'payment_status' => 'pending',
    'expires_at'     => now()->addMinutes(1),
    'auto_failed_at' => null
]);


        if ($request->payment_method === 'MIDTRANS') {
            $payment->midtrans_payload = [
                'status'      => 'waiting',
                'message'     => 'Midtrans integration pending',
                'snap_token'  => null,
            ];
            $payment->save();
        }

        return response()->json([
            'message' => 'Payment created successfully',
            'amount'  => $amount,
            'expires_at' => $payment->expires_at,
            'data'    => $payment
        ], 201);
    }


    public function uploadProof(Request $request, $paymentId)
{
    $request->validate([
        'proof' => 'required|image|mimes:jpeg,png,jpg|max:2048'
    ]);

    $payment = Payment::findOrFail($paymentId);

    if ($payment->payment_method !== 'MANUAL') {
        return response()->json([
            'message' => 'Upload proof is only allowed for MANUAL payment'
        ], 403);
    }

    $fileName = 'proof_' . $payment->id . '_' . time() . '.' . $request->proof->extension();
    $request->proof->move(public_path('uploads/payments'), $fileName);

    $payment->proof = $fileName;
    $payment->payment_status = 'in_review';   // NEW
    $payment->expires_at = null;           // NEW stop timer
    $payment->save();

    return response()->json([
        'message' => 'Proof uploaded successfully. Waiting admin review.',
        'data'    => $payment
    ]);
}



    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed'
        ]);

        $payment = Payment::findOrFail($id);
        $payment->payment_status = $request->payment_status;
        $payment->save();

        $booking = $payment->booking;

        if ($payment->payment_status === 'paid') {
            $booking->booking_status = 'PAID';
        } elseif ($payment->payment_status === 'failed') {
            $booking->booking_status = 'CANCELED';
        }

        $booking->save();

        return response()->json([
            'message' => 'Payment status updated successfully',
            'payment' => $payment,
            'booking' => $booking
        ]);
    }


    public function expire($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->payment_status !== 'pending') {
            return response()->json([
                'message' => 'Payment already completed or failed'
            ], 409);
        }

        if (now()->lessThan($payment->expires_at)) {
            return response()->json([
                'message' => 'Cannot expire before deadline',
                'expires_at' => $payment->expires_at
            ], 409);
        }

        $payment->payment_status = 'failed';
        $payment->auto_failed_at = now();
        $payment->save();

        $booking = $payment->booking;
        $booking->booking_status = 'CANCELED';
        $booking->save();

        return response()->json([
            'message' => 'Payment expired automatically',
            'data' => [
                'payment' => $payment,
                'booking' => $booking
            ]
        ], 200);
    }


    public function index()
    {
        return response()->json([
            'message' => 'All payments list',
            'data'    => Payment::with('booking.user')->orderBy('created_at', 'desc')->get()
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'message' => 'Payment detail',
            'data'    => Payment::with('booking')->findOrFail($id)
        ]);
    }

    public function filterByStatus(Request $request)
{
    $request->validate([
        'status' => 'required|in:pending,paid,in_review,failed'
    ]);

    $payments = Payment::with('booking.user')
        ->where('payment_status', $request->status)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'message' => 'Filtered payment list',
        'status'  => $request->status,
        'data'    => $payments
    ]);
}


    






    
}
