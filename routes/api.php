<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\jadwalController;
use App\Http\Controllers\AttendanceController;

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(AttendanceController::class)->group(function () {
    Route::get('/attendance/greeting', 'greeting')->name('attendance.greeting');
    Route::post('/attendance/checkin', 'checkIn')->middleware(['auth', 'single.session'])->name('attendance.checkin');
    Route::post('/attendance/checkout', 'checkOut')->middleware(['auth', 'single.session'])->name('attendance.checkout');
    Route::post('/attendance/dummy', 'dummy')->name('attendance.dummy');
});
