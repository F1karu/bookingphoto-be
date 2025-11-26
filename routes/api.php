<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PhotographerController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AddonController;
use App\Http\Controllers\BookingAddonController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (AUTH REQUIRED)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // ------------ PROFILE ------------
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::patch('/profile/password', [AuthController::class, 'updatePassword']);
    Route::delete('/profile', [AuthController::class, 'deleteProfile']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);

    // ------------ USER LIST (ADMIN CHECK IN CONTROLLER) ------------
    Route::middleware('admin')->group(function () {
    Route::get('/users', [AuthController::class, 'allUsers']);
    Route::get('/cities', [CityController::class, 'index']);
    });


    
    Route::post('/photographers', [PhotographerController::class, 'store']);
    Route::get('/photographers', [PhotographerController::class, 'index']);
    Route::get('/photographers/{id}', [PhotographerController::class, 'show']);
    Route::get('/photographers/status/{status}', [PhotographerController::class, 'getByStatus']);
    




    
    Route::get('/addons', [AddonController::class, 'index']);
    Route::get('/addons/{id}', [AddonController::class, 'show']);

    // Admin only: create, update, delete
    Route::middleware('admin')->group(function () {
        Route::post('/addons', [AddonController::class, 'store']);
        Route::put('/addons/{id}', [AddonController::class, 'update']);
        Route::delete('/addons/{id}', [AddonController::class, 'destroy']);
    });


    /*
    |--------------------------------------------------------------------------
    | BOOKING
    |--------------------------------------------------------------------------
    */
    // User features
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::post('/bookings/{id}/addons', [BookingController::class, 'updateAddons']);

    // Admin features
    Route::middleware('admin')->group(function () {
        Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
        Route::patch('/bookings/{id}/assign-photographer', [BookingController::class, 'assignPhotographer']);
        Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);       // soft delete
        Route::patch('/bookings/{id}/restore', [BookingController::class, 'restore']); // restore
        Route::get('/admin/bookings', [BookingController::class, 'adminIndex']);
        Route::get('/admin/bookings/{id}', [BookingController::class, 'adminShow']);
        Route::post('/booking-addons', [BookingAddonController::class, 'store']);
        Route::patch('/addons/{id}/restore', [AddonController::class, 'restore']);
        Route::delete('/booking-addons/{id}', [BookingAddonController::class, 'destroy']);
        Route::get('/bookings/status/{status}', [BookingController::class, 'filterByStatus']);
        Route::get('/bookings/filter/status', [BookingController::class, 'filterByStatus']);
    });


    /*
    |--------------------------------------------------------------------------
    | BOOKING ADDONS (Opsional)
    |--------------------------------------------------------------------------
    */
    // Route::post('/booking-addons/{id}', [BookingAddonController::class, 'store']);
    Route::get('/booking-addons', [BookingAddonController::class, 'index']);
    Route::put('/booking-addons/{id}', [BookingAddonController::class, 'update']);




    /*
    |--------------------------------------------------------------------------
    | DEFAULT USER RETURN
    |--------------------------------------------------------------------------
    */
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

});

Route::middleware(['auth:sanctum'])->group(function () {

    // ---------------- USER ----------------
    
        Route::post('bookings/{bookingId}/payment', [PaymentController::class, 'store'])
            ->name('payment.store');

        Route::post('payment/{paymentId}/upload-proof', [PaymentController::class, 'uploadProof'])
            ->name('payment.uploadProof');

        // View user payment detail
        Route::get('payment/{id}', [PaymentController::class, 'show'])
            ->name('payment.show');
            Route::patch('/payment/{id}/expire', [PaymentController::class, 'expire']);
        
        Route::get('/bookings/{id}/invoice', [BookingController::class, 'downloadInvoice']);
    


    // ---------------- ADMIN ----------------
    Route::middleware('admin')->group(function () {

        // List all payments
        Route::get('/payments', [PaymentController::class, 'index'])
            ->name('payment.index');

        // Update status payment (Paid / Failed)
        Route::patch('payment/{id}/status', [PaymentController::class, 'updateStatus'])
            ->name('payment.updateStatus');

        Route::get('getpayment/{id}', [PaymentController::class, 'show'])
            ->name('payment.detail');

        Route::get('/payments/{id}/proof', [PaymentController::class, 'getProof']);

        Route::get('/payments/status/{status}', [PaymentController::class, 'filterByStatus']);
        Route::get('/payments/filter/status', [PaymentController::class, 'filterByStatus']);



    });

});
