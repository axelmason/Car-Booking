<?php

use App\Http\Controllers\API\CarsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

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

Route::resource('cars', CarsController::class);
Route::post('cars/restore/{id}', [CarsController::class, 'restore']);
