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
use App\Models\Vacations;


class dashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user?->hasRole('superadmin')) {
            return $this->superadmin();
        }

        $today = today()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Default dashboard values
        |--------------------------------------------------------------------------
        */
        $todayIsVacation   = false;
        $attendanceToday   = null;
        $recentAttendances = collect();
        $balanceMinutes    = 0;
        $announcements     = collect();

        $lateSummary = (object) [
            'late_count' => 0,
            'late_with_permission_count' => 0,
            'late_without_permission_count' => 0,
        ];

        $lateDetails = collect();

        $vacationSummary = (object) [
            'total_count' => 0,
            'cuti_count' => 0,
            'absent_count' => 0,
        ];

        $vacationDetails = collect();

        /*
        |--------------------------------------------------------------------------
        | Employee dashboard data
        |--------------------------------------------------------------------------
        */
        if (!$user?->id_employee) {
            return view('dashboard', compact(
                'attendanceToday',
                'recentAttendances',
                'balanceMinutes',
                'todayIsVacation',
                'announcements',
                'lateSummary',
                'lateDetails',
                'vacationSummary',
                'vacationDetails'
            ));
        }

        $employeeId = $user->id_employee;

        $startDate = now()->startOfMonth()->toDateString();
        $endDate   = now()->endOfMonth()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Today's schedule and attendance
        |--------------------------------------------------------------------------
        */
        $todayIsVacation = Schedules::query()
            ->where('id_employee', $employeeId)
            ->whereDate('date', $today)
            ->where('is_vacation', true)
            ->exists();

        $attendanceToday = Attendances::query()
            ->where('id_employee', $employeeId)
            ->whereDate('work_date', $today)
            ->first();

        $recentAttendances = Attendances::query()
            ->where('id_employee', $employeeId)
            ->latest('work_date')
            ->take(7)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Time balance
        |--------------------------------------------------------------------------
        */
        $timeBalance = TimeBalances::query()
            ->where('id_employee', $employeeId)
            ->first();

        $balanceMinutes = $timeBalance?->net_minutes ?? 0;

        /*
        |--------------------------------------------------------------------------
        | Announcements
        |--------------------------------------------------------------------------
        */
        $announcements = Announcement::query()
            ->active()
            ->whereHas('employees', function ($query) use ($employeeId) {
                $query->where('employees.id', $employeeId);
            })
            ->latest('date_from')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Monthly lateness summary
        |--------------------------------------------------------------------------
        */
        $lateSummary = Attendances::query()
            ->where('id_employee', $employeeId)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->where('is_late', true)
            ->selectRaw("
                COUNT(*) as late_count,
                SUM(CASE WHEN late_type = 'with_permission' THEN 1 ELSE 0 END) as late_with_permission_count,
                SUM(CASE WHEN late_type = 'without_permission' THEN 1 ELSE 0 END) as late_without_permission_count
            ")
            ->first();

        $lateDetails = Attendances::query()
            ->where('id_employee', $employeeId)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->where('is_late', true)
            ->select([
                'id',
                'work_date',
                'check_in_at',
                'penalty_minutes',
                'late_type',
            ])
            ->latest('work_date')
            ->take(3)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Cuti & Tidak Masuk Summary
        |--------------------------------------------------------------------------
        */
        $year = now()->year;
        $vacationSummary = Vacations::query()
            ->where('id_employee', $employeeId)
            ->whereYear('date_of_vacation', $year)
            ->whereIn('type', ['cuti', 'gone'])
            ->selectRaw("
                COUNT(*) as total_count,
                SUM(CASE WHEN type = 'cuti' THEN 1 ELSE 0 END) as cuti_count,
                SUM(CASE WHEN type = 'gone' THEN 1 ELSE 0 END) as gone_count
            ")
            ->first();

        return view('dashboard', compact(
            'attendanceToday',
            'recentAttendances',
            'balanceMinutes',
            'todayIsVacation',
            'announcements',
            'lateSummary',
            'lateDetails',
            'vacationSummary'
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
