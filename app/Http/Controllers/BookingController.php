<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Photographer;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // USER buat booking
    public function store(Request $request)
    {
        $request->validate([
            'user_name'   => 'required|string|max:255',
            'user_phone'  => 'required|string|max:20',
            'date'        => 'required|date',
            'start_time'  => 'nullable',
            'end_time'    => 'nullable',
            'base_price'  => 'required|numeric',
            'note'        => 'nullable|string',
        ]);

        $duration = null;
        if ($request->start_time && $request->end_time) {
            $duration = (strtotime($request->end_time) - strtotime($request->start_time)) / 3600;
        }

        $booking = Booking::create([
            'user_id'        => auth()->id(),
            'user_name'      => $request->user_name,
            'user_phone'     => $request->user_phone,
            'date'           => $request->date,
            'start_time'     => $request->start_time,
            'end_time'       => $request->end_time,
            'duration'       => $duration,
            'base_price'     => $request->base_price,
            'total_price'    => $request->base_price,
            'note'           => $request->note,
            'photographer_id'=> null,
            'booking_status' => 'PENDING_PAYMENT',
        ]);

        return response()->json([
            'message' => 'Booking created',
            'data'    => $booking
        ], 201);
    }

    // USER lihat booking mereka sendiri
    public function index()
    {
        $user = auth()->user();
        $bookings = Booking::with('photographer')
            ->where('user_id', $user->id)
            ->get();

        return response()->json($bookings);
    }

    // USER lihat detail booking lengkap
    public function show($id)
    {
        $user = auth()->user();
        $booking = Booking::with('photographer')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json($booking);
    }

    // ADMIN update status booking
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:PENDING_PAYMENT,PAID,IN_PROGRESS,COMPLETED,CANCELED'
        ]);

        $booking = Booking::findOrFail($id);
        $booking->booking_status = $request->status;
        $booking->save();

        // Update status photographer otomatis
        if ($booking->photographer) {
            if (in_array($request->status, ['COMPLETED', 'CANCELED'])) {
                $booking->photographer->status = 'available';
                $booking->photographer->save();
            } elseif ($request->status === 'IN_PROGRESS') {
                $booking->photographer->status = 'busy';
                $booking->photographer->save();
            }
        }

        return response()->json([
            'message' => 'Status updated',
            'data' => $booking
        ]);
    }

    // ADMIN soft delete booking
    public function destroy($id)
    {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking = Booking::findOrFail($id);

        // Jika ada photographer, set status available
        if ($booking->photographer) {
            $booking->photographer->status = 'available';
            $booking->photographer->save();
        }

        $booking->delete();

        return response()->json(['message' => 'Booking soft-deleted']);
    }

    // ADMIN restore booking
    public function restore($id)
    {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking = Booking::withTrashed()->findOrFail($id);
        $booking->restore();

        // Jika booking ada photographer dan status masih IN_PROGRESS, ubah jadi busy
        if ($booking->photographer && $booking->booking_status === 'IN_PROGRESS') {
            $booking->photographer->status = 'busy';
            $booking->photographer->save();
        }

        return response()->json(['message' => 'Booking restored']);
    }

    // ADMIN assign photographer
    public function assignPhotographer(Request $request, $id)
    {
        $request->validate([
            'photographer_id' => 'required|exists:photographers,id'
        ]);

        $booking = Booking::findOrFail($id);
        $photographer = Photographer::findOrFail($request->photographer_id);

        if ($photographer->status !== 'available') {
            return response()->json(['message' => 'Photographer tidak tersedia'], 422);
        }

        $booking->photographer_id = $photographer->id;
        $booking->booking_status = 'IN_PROGRESS';
        $booking->save();

        $photographer->status = 'busy';
        $photographer->save();

        return response()->json([
            'message' => 'Photographer assigned',
            'booking' => $booking,
            'photographer' => $photographer
        ]);
    }
}
