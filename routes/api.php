<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PhotographerController;

/*
API WOI
*/


//ROUTE AUTH
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');


//ROUTE USER
Route::middleware('auth:sanctum')->group(function () {
Route::get('/profile', [AuthController::class, 'profile']);
Route::patch('/profile/password', [AuthController::class, 'updatePassword']);
Route::delete('/profile', [AuthController::class, 'deleteProfile']);

Route::get('/users', [AuthController::class, 'allUsers'])
        ->middleware('admin');
});





//ROUTE PHOTOGRAPHER
Route::middleware('auth:sanctum')->group(function () {
    Route::get('photographers', [PhotographerController::class, 'index']);
    Route::get('photographers/{id}', [PhotographerController::class, 'show']);
    Route::get('/cities', [CityController::class, 'index']);

    

    Route::middleware('admin')->group(function () {
        Route::post('photographers', [PhotographerController::class, 'store']);
        Route::put('photographers/{id}', [PhotographerController::class, 'update']);
        Route::patch('photographers/{id}/status', [PhotographerController::class, 'updateStatus']);
        Route::delete('photographers/{id}', [PhotographerController::class, 'destroy']);
    });
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
