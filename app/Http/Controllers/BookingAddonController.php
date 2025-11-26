<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\Addon;
use Illuminate\Http\Request;

class BookingAddonController extends Controller
{
    
    public function index($bookingId)
    {
        $booking = Booking::with('bookingAddons.addon')->findOrFail($bookingId);

        return response()->json([
            'message' => 'Booking addons list',
            'data'    => $booking->bookingAddons
        ]);
    }

    
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $bookingAddon = BookingAddon::findOrFail($id);
$booking = $bookingAddon->booking;

if (in_array($booking->booking_status, ['PAID','IN_PROGRESS','COMPLETED','CANCELED'])) {
    return response()->json([
        'message' => 'Tidak bisa update addon, booking sudah dibayar atau sedang berlangsung'
    ], 403);
}

        $addon = Addon::findOrFail($bookingAddon->addon_id);

        $bookingAddon->quantity = $request->quantity;
        $bookingAddon->subtotal = $addon->price * $request->quantity;
        $bookingAddon->save();

        // Update total addon price di booking
        $booking->total_addons_price = $booking->bookingAddons()->sum('subtotal');
        $booking->total_price = $booking->base_price
                                + $booking->total_addons_price
                                + (($booking->duration && $booking->photographer)
                                    ? $booking->duration * $booking->photographer->price_per_hour
                                    : 0);
        $booking->save();

        return response()->json([
            'message' => 'Booking addon updated',
            'data'    => $bookingAddon
        ]);
    }

    // ---------------- Delete Addon from Booking ----------------
    public function destroy($id)
    {
        $bookingAddon = BookingAddon::findOrFail($id);
        $booking = $bookingAddon->booking;

        if ($booking->booking_status !== 'PENDING_PAYMENT') {
            return response()->json([
                'message' => 'Tidak bisa hapus addon, booking sudah dibayar atau in progress'
            ], 409);
        }

        $bookingAddon->delete();

        // Update total addon price di booking
        $booking->total_addons_price = $booking->bookingAddons()->sum('subtotal');
        $booking->total_price = $booking->base_price
                                + $booking->total_addons_price
                                + (($booking->duration && $booking->photographer)
                                    ? $booking->duration * $booking->photographer->price_per_hour
                                    : 0);
        $booking->save();

        return response()->json([
            'message' => 'Booking addon removed'
        ]);
    }
}
