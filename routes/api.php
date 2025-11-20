\<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PhotographerController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CityController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// -------------------- AUTH --------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Semua route berikut harus login
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // PROFILE
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::patch('/profile/password', [AuthController::class, 'updatePassword']);
    Route::delete('/profile', [AuthController::class, 'deleteProfile']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']); // update data user

    // ADMIN ONLY
    Route::get('/users', [AuthController::class, 'allUsers']); // cek admin di controller atau middleware
});

// -------------------- PHOTOGRAPHER --------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('photographers', [PhotographerController::class, 'index']);
    Route::get('photographers/{id}', [PhotographerController::class, 'show']);
    Route::get('photographers/status/{status}', [PhotographerController::class, 'getByStatus']);
    Route::get('/cities', [CityController::class, 'index']);

    Route::middleware('admin')->group(function () {
        Route::post('photographers', [PhotographerController::class, 'store']);
        Route::put('photographers/{id}', [PhotographerController::class, 'update']);
        Route::patch('photographers/{id}/status', [PhotographerController::class, 'updateStatus']);
        Route::delete('photographers/{id}', [PhotographerController::class, 'destroy']); // soft delete
    });
});

// -------------------- BOOKING --------------------
Route::middleware('auth:sanctum')->group(function () {
    // User booking
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings', [BookingController::class, 'index']); // bisa buat get list booking user
    Route::get('/bookings/{id}', [BookingController::class, 'show']);

    // Admin update booking status, delete, restore, assign photographer
    Route::middleware('admin')->group(function () {
        Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
        Route::delete('/bookings/{id}', [BookingController::class, 'destroy']); // soft delete
        Route::patch('/bookings/{id}/restore', [BookingController::class, 'restore']); 
        Route::patch('/bookings/{id}/assign-photographer', [BookingController::class, 'assignPhotographer']); 
    });
});

// -------------------- DEFAULT USER INFO --------------------
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
