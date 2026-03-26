<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Attendances;
use App\Models\TimeBalances;
use App\Models\Schedules;
use App\Models\Announcement;
use App\Models\Employees;
use App\Models\User;


class dashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user  = Auth::user();

        if ($user && $user->hasRole('superadmin')) {
            return $this->superadmin();
        }

        $today = today()->toDateString(); // sama dengan now()->toDateString()

        // Default values
        $todayIsVacation   = false;
        $attendanceToday   = null;
        $recentAttendances = collect();
        $balance_minutes   = 0;
        $announcements     = collect();

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

            $announcements = Announcement::active()   // scopeActive dari model
            ->whereHas('employees', function ($q) use ($employeeId) {
                // kolom di tabel employees biasanya `id`
                $q->where('employees.id', $employeeId);
            })
            ->orderByDesc('date_from')
            ->get();
        }

        return view('dashboard', compact(
            'attendanceToday',
            'recentAttendances',
            'balance_minutes',
            'todayIsVacation',
            'announcements'
        ));
    }

    /**
     * Show the form for creating a new resource for superadmin.
     */
    public function superadmin()
    {
        $closestBirthdayEmployees = Employees::orderByClosestBirthday()->limit(5)->get();

        $today = now()->toDateString();
        $soonDate = now()->addDays(90)->toDateString();

        $totalMedicines = 10;
        $lowStockCount = 5;
        $expiringSoonCount = 2;

        $todaySales = 3;
        $todayTransactionCount = 1;

        $attendanceTodayCount = Attendances::whereDate('created_at', $today)->count();
        $employeeCount = Employees::count();

        $activeSupplierCount = 13;
        $userCount = User::count();

        $cashInToday = 56;

        $recentSales = [
            'total' => 48,
            'created_at' => now()->toDateTimeString(),
        ];

        $criticalMedicines = [
            [
                'name' => 'Paracetamol',
                'stock' => 10,
                'minimum_stock' => 20,
            ],
            [
                'name' => 'Ibuprofen',
                'stock' => 15,
                'minimum_stock' => 30,
            ],
            [
                'name' => 'Metformin',
                'stock' => 12,
                'minimum_stock' => 25,
            ],
            [
                'name' => 'Amlodipin',
                'stock' => 8,
                'minimum_stock' => 18,
            ],
            [
                'name' => 'Captopril',
                'stock' => 5,
                'minimum_stock' => 15,
            ],
        ];

        return view('dashboard.superadmin', compact(
            'totalMedicines',
            'lowStockCount',
            'expiringSoonCount',
            'todaySales',
            'todayTransactionCount',
            'attendanceTodayCount',
            'employeeCount',
            'activeSupplierCount',
            'userCount',
            'cashInToday',
            'recentSales',
            'criticalMedicines',
            'closestBirthdayEmployees'
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
