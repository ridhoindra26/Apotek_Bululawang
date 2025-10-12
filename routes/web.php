<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\jadwalController;
use App\Http\Controllers\branchesController;
use App\Http\Controllers\karyawanController;
use App\Http\Controllers\liburController;
use App\Http\Controllers\pasanganController;
use App\Http\Controllers\dashboardController;

Route::get('/', function () {
    return redirect()->route('auth.index');
});

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'index')->name('auth.index');
    Route::post('/login', 'login')->name('auth.login');
    // Route::delete('/libur/{id}', 'destroy')->middleware('auth:sanctum')->name('libur.destroy');
});

Route::controller(jadwalController::class)->group(function () {
    Route::get('/jadwal', 'index')->name('jadwal.index');
    // Route::get('/jadwal/{id}', 'show')->name('jadwal.show');
    Route::get('/jadwal/generate', 'generate')->name('jadwal.generate');
    Route::post('/jadwal/store', 'store')->name('jadwal.store');
    Route::get('/jadwal/print', 'print')->name('jadwal.print');
    Route::get('/jadwal/day', 'dayShow')->name('jadwal.day.show');
    Route::patch('/jadwal/day', 'dayUpdate')->name('jadwal.day.update');
    Route::delete('/jadwal/day', 'destroy')->name('jadwal.destroy');
});

Route::controller(branchesController::class)->group(function () {
    Route::get('/cabang', 'index')->name('cabang.index');
    Route::get('/cabang/create', 'create')->name('cabang.create');
    Route::post('/cabang', 'store')->name('cabang.store');
    Route::get('/cabang/{id}', 'show')->name('cabang.show');
    Route::get('/cabang/{id}/edit', 'edit')->name('cabang.edit');
    Route::post('/cabang/{id}', 'update')->name('cabang.update');
    Route::delete('/cabang/{id}', 'destroy')->name('cabang.destroy');
});

Route::controller(karyawanController::class)->group(function () {
    Route::get('/karyawan', 'index')->name('karyawan.index');
    Route::get('/karyawan/create', 'create')->name('karyawan.create');
    Route::post('/karyawan', 'store')->name('karyawan.store');
    Route::get('/karyawan/{id}', 'show')->name('karyawan.show');
    Route::get('/karyawan/{id}/edit', 'edit')->name('karyawan.edit');
    Route::post('/karyawan/{id}', 'update')->name('karyawan.update');
    Route::delete('/karyawan/{id}', 'destroy')->name('karyawan.destroy');
});

Route::controller(liburController::class)->group(function () {
    Route::get('/libur', 'index')->name('libur.index');
    Route::get('/libur/create', 'create')->name('libur.create');
    Route::post('/libur', 'store')->name('libur.store');
    Route::get('/libur/{id}', 'show')->name('libur.show');
    Route::get('/libur/{id}/edit', 'edit')->name('libur.edit');
    Route::post('/libur/{id}', 'update')->name('libur.update');
    Route::delete('/libur/{id}', 'destroy')->name('libur.destroy');
});

Route::controller(pasanganController::class)->group(function () {
    Route::get('/pasangan', 'index')->name('pasangan.index');
    Route::get('/pasangan/create', 'create')->name('pasangan.create');
    Route::post('/pasangan', 'store')->name('pasangan.store');
    Route::get('/pasangan/{id}', 'show')->name('pasangan.show');
    Route::get('/pasangan/{id}/edit', 'edit')->name('pasangan.edit');
    Route::post('/pasangan/{id}', 'update')->name('pasangan.update');
    Route::delete('/pasangan/{id}', 'destroy')->name('pasangan.destroy');
});

Route::controller(dashboardController::class)->group(function () {
    Route::get('/dashboard', 'index')->name('dashboard');
});