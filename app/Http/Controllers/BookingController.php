<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Photographer;
use App\Models\Addon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingController extends Controller
{
    // ---------------- USER - Create Booking ----------------
public function store(Request $request)
{
    $request->validate([
        'user_phone'  => 'required|regex:/^[0-9]+$/|min:10|max:15',
        'date'        => 'required|date',
        'start_time'  => 'nullable|date_format:H:i',
        'end_time'    => 'nullable|date_format:H:i|after:start_time',
        'price_type'  => 'required|in:normal,professional',
        'note'        => 'nullable|string',
        'addons'      => 'nullable|array',
        'addons.*.addon_id' => 'required_with:addons|exists:addons,id',
        'addons.*.quantity' => 'required_with:addons|integer|min:0', // addon boleh 0
    ]);

    $user = auth()->user();

    $duration = $request->start_time && $request->end_time
        ? (strtotime($request->end_time) - strtotime($request->start_time)) / 3600
        : null;

    // Durasi wajib minimal 1 jam jika start & end diisi
    if ($duration !== null && $duration < 1) {
        return response()->json([
            'message' => 'Duration must be at least 1 hour',
        ], 422);
    }

    $basePrice = Booking::getBasePriceByType($request->price_type);
    $totalPrice = $duration ? ($basePrice * $duration) : $basePrice;

    $booking = Booking::create([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'user_phone' => $request->user_phone,
        'date' => $request->date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'duration' => $duration,
        'photographer_id' => null,
        'price_type' => $request->price_type,
        'base_price' => $basePrice,
        'total_price' => $totalPrice,
        'total_addons_price' => 0,
        'note' => $request->note,
        'booking_status' => 'PENDING_PAYMENT',
    ]);

    $addonsTotal = 0;
    if ($request->addons) {
        foreach ($request->addons as $a) {
            if ($a['quantity'] > 0) {
                $addon = Addon::find($a['addon_id']);
                $subtotal = $addon->price * $a['quantity'];
                $addonsTotal += $subtotal;

                $booking->bookingAddons()->create([
                    'addon_id' => $addon->id,
                    'quantity' => $a['quantity'],
                    'subtotal' => $subtotal,
                ]);
            }
        }
    }

    $booking->total_addons_price = $addonsTotal;
    $booking->total_price += $addonsTotal;
    $booking->save();

    return response()->json([
        'message' => 'Booking created successfully with addons',
        'data' => $booking->load('bookingAddons.addon')
    ], 201);
}




    
    public function index()
    {
        $user = auth()->user();
        return Booking::with(['photographer', 'bookingAddons.addon'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    
    public function show($id)
    {
        $user = auth()->user();
        $booking = Booking::with(['photographer', 'bookingAddons.addon'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json($booking);
    }

    
    public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:PENDING_PAYMENT,PAID,IN_PROGRESS,COMPLETED,CANCELED'
    ]);

    $booking = Booking::findOrFail($id);
    $oldStatus = $booking->booking_status;
    $newStatus = $request->status;

    
    if ($newStatus === 'PAID') {
        $booking->booking_status = 'PAID';
    } elseif ($newStatus === 'CANCELED') {
        $booking->booking_status = 'CANCELED';
    } elseif ($newStatus === 'COMPLETED') {
        $booking->booking_status = 'COMPLETED';
    } elseif ($newStatus === 'IN_PROGRESS') {
        if ($booking->booking_status === 'PAID' && $booking->photographer_id) {
            $booking->booking_status = 'IN_PROGRESS';
        } else {
            return response()->json([
                'message' => 'Booking harus PAID dan sudah assign photographer untuk IN_PROGRESS'
            ], 409);
        }
    }

    $booking->save();

    
    if ($booking->photographer) {
        if ($booking->booking_status === 'IN_PROGRESS') {
            $booking->photographer->status = 'busy';
        }
        if (in_array($booking->booking_status, ['COMPLETED', 'CANCELED'])) {
            $booking->photographer->status = 'available';
        }
        $booking->photographer->save();
    }

    return response()->json([
        'message' => "Booking status updated from $oldStatus to {$booking->booking_status}",
        'data' => $booking
    ]);
}


    public function assignPhotographer(Request $request, $id)
{
    $request->validate([
        'photographer_id' => 'required|exists:photographers,id'
    ]);

    $booking = Booking::findOrFail($id);

    if ($booking->booking_status !== 'PAID') {
        return response()->json([
            'message' => 'Booking harus PAID terlebih dahulu sebelum assign photographer'
        ], 409);
    }

    $photographerId = $request->photographer_id;
    $photographer = Photographer::findOrFail($photographerId);

    // Cek jadwal bentrok
    if ($this->checkScheduleConflict(
        $photographerId,
        $booking->date,
        $booking->start_time,
        $booking->end_time
    )) {
        return response()->json([
            'message' => 'Jadwal bentrok! Photographer sudah ada booking pada waktu tersebut'
        ], 422);
    }

    // Assign karena tidak bentrok
    $booking->photographer_id = $photographerId;
    $booking->booking_status = 'IN_PROGRESS';
    $booking->save();

    // Ubah status PG hanya jika benar-benar ada booking aktif
    $photographer->status = 'busy';
    $photographer->save();

    return response()->json([
        'message' => 'Photographer assigned successfully & booking IN_PROGRESS',
        'booking' => $booking,
        'photographer' => $photographer
    ]);
}



    // ---------------- USER - Update Addons ----------------
    public function updateAddons(Request $request, $id)
{
    $request->validate([
        'addon_id' => 'required|exists:addons,id',
        'quantity' => 'required|integer|min:0'
    ]);

    $booking = Booking::findOrFail($id);

    if ($booking->booking_status !== 'PENDING_PAYMENT') {
        return response()->json([
            'message' => 'Addon tidak bisa diupdate, booking sudah dibayar atau in progress'
        ], 409);
    }

    if ($booking->bookingAddons()->exists()) {
        return response()->json([
            'message' => 'Addon sudah dibuat, tidak bisa update lagi'
        ], 409);
    }

    $subtotal = 0;
    if ($request->quantity > 0) {
        $addon = Addon::findOrFail($request->addon_id);
        $subtotal = $addon->price * $request->quantity;

        $booking->bookingAddons()->create([
            'addon_id' => $request->addon_id,
            'quantity' => $request->quantity,
            'subtotal' => $subtotal,
        ]);
    }

    $booking->total_addons_price = $subtotal;
    $booking->total_price = ($booking->base_price * ($booking->duration ?? 1)) + $subtotal;
    $booking->save();

    return response()->json([
        'message' => 'Addon berhasil ditambahkan',
        'data' => $booking->load('bookingAddons.addon')
    ], 201);
}
   

    // ---------------- ADMIN - List All Bookings ----------------
    public function adminIndex()
    {
        return response()->json([
            'message' => 'All bookings (admin)',
            'data' => Booking::with(['user', 'photographer', 'bookingAddons.addon'])
                ->orderBy('created_at', 'desc')
                ->get()
        ]);
    }

    // ---------------- ADMIN - Show Detail Booking ----------------
    public function adminShow($id)
    {
        $booking = Booking::with(['user', 'photographer', 'bookingAddons.addon'])->findOrFail($id);

        return response()->json([
            'message' => 'Booking detail (admin)',
            'data' => $booking
        ]);
    }

    public function downloadInvoice($id)
{
    $booking = Booking::with(['user','photographer','bookingAddons.addon','payment'])
        ->findOrFail($id);

if (!$booking->payment || $booking->payment->payment_status !== 'paid') {
    return response()->json([
        'message' => 'Invoice available only after payment is marked as PAID'
    ], 409);
}


    $pdf = Pdf::loadView('invoice.booking', ['booking' => $booking]);

    $fileName = 'Invoice-BOOKING-' . $booking->id . '.pdf';

    return $pdf->download($fileName);
}

public function filterByStatus(Request $request)
{
    $request->validate([
        'status' => 'required|in:PENDING_PAYMENT,PAID,IN_PROGRESS,COMPLETED,CANCELED'
    ]);

    $bookings = Booking::with(['photographer', 'bookingAddons.addon'])
        ->where('booking_status', $request->status)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'message' => "Filtered bookings by status: {$request->status}",
        'status' => $request->status,
        'data' => $bookings
    ]);
}

private function checkScheduleConflict($photographerId, $date, $start, $end)
{
    return Booking::where('photographer_id', $photographerId)
        ->where('booking_status', 'IN_PROGRESS')
        ->where('date', $date)
        ->where(function ($query) use ($start, $end) {
            $query->whereBetween('start_time', [$start, $end])
                ->orWhereBetween('end_time', [$start, $end])
                ->orWhere(function ($q) use ($start, $end) {
                    $q->where('start_time', '<=', $start)
                        ->where('end_time', '>=', $end);
                });
        })
        ->exists();
}

}
