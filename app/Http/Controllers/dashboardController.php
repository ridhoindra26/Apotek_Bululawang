<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Attendances;
use App\Models\TimeBalances;
use App\Models\Schedules;


class dashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user  = Auth::user();
        $today = today()->toDateString(); // sama dengan now()->toDateString()

        // Default values
        $todayIsVacation   = false;
        $attendanceToday   = null;
        $recentAttendances = collect();
        $balance_minutes   = 0;

        if ($user && $user->id_employee) {

            $employeeId = $user->id_employee;

            // Cek libur hari ini
            $todayIsVacation = Schedules::where('id_employee', $employeeId)
                ->whereDate('date', $today)
                ->where('is_vacation', 1)
                ->exists();

            // Absensi hari ini
            $attendanceToday = Attendances::where('id_employee', $employeeId)
                ->whereDate('work_date', $today)
                ->first();

            // 7 absensi terakhir
            $recentAttendances = Attendances::where('id_employee', $employeeId)
                ->orderByDesc('work_date')
                ->take(7)
                ->get();

            // Time balance (pakai accessor kalau ada)
            $timeBalance = TimeBalances::where('id_employee', $employeeId)->first();
            $balance_minutes = $timeBalance?->net_minutes ?? 0;  // daripada getNetMinutesAttribute()
        }

        return view('dashboard', compact(
            'attendanceToday',
            'recentAttendances',
            'balance_minutes',
            'todayIsVacation'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(c $c)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(c $c)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, c $c)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(c $c)
    {
        //
    }
}
