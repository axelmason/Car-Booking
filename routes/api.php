<?php

use App\Http\Controllers\API\CarsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\BookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::resource('users', UserController::class);
Route::post('users/auth', [UserController::class, 'login']);

Route::get('cars/all', [CarsController::class, 'index']);
Route::get('cars/{id}', [CarsController::class, 'show']);
Route::get('cars/{id}/seats', [CarsController::class, 'seats']);

Route::middleware('adminapi')->group(function() {
    Route::resource('cars', CarsController::class);
    Route::post('cars/restore/{id}', [CarsController::class, 'restore']);
});

Route::post('booking', [BookingController::class, 'booking']);
