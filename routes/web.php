<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\jadwalController;
use App\Http\Controllers\branchesController;
use App\Http\Controllers\karyawanController;
use App\Http\Controllers\liburController;
use App\Http\Controllers\pasanganController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TimeBalanceController;

Route::get('/', function () {
    return redirect()->route('auth.index');
})->name('login');

Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'index')->middleware('guest')->name('auth.index');
    Route::post('/login', 'login')->middleware('guest')->name('auth.login');
    Route::post('/logout', 'logout')->middleware('auth')->name('auth.logout');
});

// Route::controller(jadwalController::class)->group(function () {
//     Route::get('/jadwal', 'index')->name('jadwal.index');
//     // Route::get('/jadwal/{id}', 'show')->name('jadwal.show');
//     Route::get('/jadwal/generate', 'generate')->name('jadwal.generate');
//     Route::post('/jadwal/store', 'store')->name('jadwal.store');
//     Route::get('/jadwal/print', 'print')->name('jadwal.print');
//     Route::get('/jadwal/day', 'dayShow')->name('jadwal.day.show');
//     Route::patch('/jadwal/day', 'dayUpdate')->name('jadwal.day.update');
//     Route::delete('/jadwal/day', 'destroy')->name('jadwal.destroy');
// });

// Route::controller(branchesController::class)->group(function () {
//     Route::get('/cabang', 'index')->name('cabang.index');
//     Route::get('/cabang/create', 'create')->name('cabang.create');
//     Route::post('/cabang', 'store')->name('cabang.store');
//     Route::get('/cabang/{id}', 'show')->name('cabang.show');
//     Route::get('/cabang/{id}/edit', 'edit')->name('cabang.edit');
//     Route::post('/cabang/{id}', 'update')->name('cabang.update');
//     Route::delete('/cabang/{id}', 'destroy')->name('cabang.destroy');
// });

// Route::controller(karyawanController::class)->group(function () {
//     Route::get('/karyawan', 'index')->name('karyawan.index');
//     Route::get('/karyawan/create', 'create')->name('karyawan.create');
//     Route::post('/karyawan', 'store')->name('karyawan.store');
//     Route::get('/karyawan/{id}', 'show')->name('karyawan.show');
//     Route::get('/karyawan/{id}/edit', 'edit')->name('karyawan.edit');
//     Route::post('/karyawan/{id}', 'update')->name('karyawan.update');
//     Route::delete('/karyawan/{id}', 'destroy')->name('karyawan.destroy');
// });

// Route::controller(liburController::class)->group(function () {
//     Route::get('/libur', 'index')->name('libur.index');
//     Route::get('/libur/create', 'create')->name('libur.create');
//     Route::post('/libur', 'store')->name('libur.store');
//     Route::get('/libur/{id}', 'show')->name('libur.show');
//     Route::get('/libur/{id}/edit', 'edit')->name('libur.edit');
//     Route::post('/libur/{id}', 'update')->name('libur.update');
//     Route::delete('/libur/{id}', 'destroy')->name('libur.destroy');
// });

// Route::controller(pasanganController::class)->group(function () {
//     Route::get('/pasangan', 'index')->name('pasangan.index');
//     Route::get('/pasangan/create', 'create')->name('pasangan.create');
//     Route::post('/pasangan', 'store')->name('pasangan.store');
//     Route::get('/pasangan/{id}', 'show')->name('pasangan.show');
//     Route::get('/pasangan/{id}/edit', 'edit')->name('pasangan.edit');
//     Route::post('/pasangan/{id}', 'update')->name('pasangan.update');
//     Route::delete('/pasangan/{id}', 'destroy')->name('pasangan.destroy');
// });

// Route::controller(dashboardController::class)->group(function () {
//     Route::get('/dashboard', 'index')->middleware(['auth', 'single.session'])->name('dashboard');
// });

// Route::controller(AttendanceController::class)->group(function () {
//     Route::post('/attendance/checkin', 'checkIn')->middleware(['auth', 'single.session'])->name('attendance.checkin');
//     Route::post('/attendance/checkout', 'checkOut')->middleware(['auth', 'single.session'])->name('attendance.checkout');
//     Route::get('/attendance/photo/{type}/{id}', 'getPhoto')->name('attendance.photo');

//     Route::get('/admin/attendances', 'index')->name('attendances.index');
//     Route::get('/admin/attendances/{attendance}/minutes', 'minutesData')->name('attendances.minutesData');
//     Route::post('/admin/attendances/{attendance}/minutes', 'minutesConfirm')->name('attendances.minutesConfirm');
//     Route::get('/admin/attendances/{attendance}/{type}/photo', 'photoUrl')->where('type', 'check_in|check_out')->name('attendances.photoUrl');
// });

/**
 * Protected routes: require auth + single.session
 */
Route::middleware(['auth', 'single.session'])->group(function () {

    /**
     * Dashboard — semua role
     */
    Route::controller(dashboardController::class)
        ->middleware('role:superadmin,admin,user')
        ->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard');
        });

    /**
     * Jadwal — superadmin & admin
     */
    Route::controller(jadwalController::class)
        ->middleware('role:superadmin,admin')
        ->group(function () {
            Route::get('/jadwal', 'index')->name('jadwal.index');
            // Route::get('/jadwal/{id}', 'show')->name('jadwal.show');
            Route::get('/jadwal/generate', 'generate')->name('jadwal.generate');
            Route::post('/jadwal/store', 'store')->name('jadwal.store');
            Route::get('/jadwal/print', 'print')->name('jadwal.print');
            Route::get('/jadwal/day', 'dayShow')->name('jadwal.day.show');
            Route::patch('/jadwal/day', 'dayUpdate')->name('jadwal.day.update');
            Route::delete('/jadwal/day', 'destroy')->name('jadwal.destroy');
        });

    /**
     * Master data (Cabang, Karyawan, Libur, Pasangan) — superadmin only
     */
    Route::middleware('role:superadmin')->group(function () {

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

        Route::controller(UserController::class)->group(function () {
            Route::get('/accounts', 'index')->name('accounts.index');
            Route::get('/users/modal/{id?}', 'getUserDataForModal')->name('accounts.modal');
            Route::post('/accounts', 'store')->name('accounts.store');
            Route::patch('/accounts/{user}', 'update')->name('accounts.update');
            Route::delete('/accounts/{user}', 'destroy')->name('accounts.destroy');
        });

    });

    /**
     * Attendance
     * - checkin/checkout: semua role login
     * - admin pages: superadmin & admin
     */
    Route::controller(AttendanceController::class)->group(function () {
        // Semua role bisa absen
        Route::post('/attendance/checkin', 'checkIn')
            ->middleware('role:superadmin,admin,user')
            ->name('attendance.checkin');

        Route::post('/attendance/checkout', 'checkOut')
            ->middleware('role:superadmin,admin,user')
            ->name('attendance.checkout');

        // Foto absen (akses sesuai kebutuhan — di sini izinkan semua login)
        Route::get('/attendance/photo/{type}/{id}', 'getPhoto')
            ->where('type', 'check_in|check_out')
            ->middleware('role:superadmin,admin,user')
            ->name('attendance.photo');

        // Halaman admin attendance
        Route::middleware('role:superadmin,admin')->group(function () {
            Route::get('/admin/attendances', 'index')->name('attendances.index');
            Route::get('/admin/attendances/{attendance}/minutes', 'minutesData')->name('attendances.minutesData');
            Route::post('/admin/attendances/{attendance}/minutes', 'minutesConfirm')->name('attendances.minutesConfirm');
            Route::get('/admin/attendances/{attendance}/{type}/photo', 'photoUrl')
                ->where('type', 'check_in|check_out')
                ->name('attendances.photoUrl');
        });
    });

    /**
     * Laporan time balances
     * - admin pages: superadmin & admin
     */

    Route::controller(timeBalanceController::class)->group(function () {
        Route::middleware('role:user')->group(function () {
            Route::get('/user/timebalances', 'showMe')->name('user.balance.show');
        });

        Route::middleware('role:superadmin,admin')->group(function () {
            Route::get('/admin/timebalances', 'index')->name('attendances.balance');
            Route::get('/admin/timebalances/{id}', 'show')->name('attendances.balance.show');
            Route::post('/admin/timebalances/{id}', 'adjust')->name('attendances.balance.adjust');
            // Route::get('/admin/timebalances/{timebalance}/{type}/photo', 'photoUrl')
            //     ->where('type', 'check_in|check_out')
            //     ->name('timebalances.photoUrl');
        });
    });

});