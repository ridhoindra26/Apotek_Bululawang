<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Attendances;


class dashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Default empty data
        $attendanceToday = null;
        $recentAttendances = collect();

        // Only load attendance if linked to employee
        if ($user && $user->id_employee) {
            $attendanceToday = Attendances::where('id_employee', $user->id_employee)
                ->whereDate('work_date', today())
                ->first();

            $recentAttendances = Attendances::where('id_employee', $user->id_employee)
                ->orderByDesc('work_date')
                ->take(7)
                ->get();
        }

        return view('dashboard', compact('attendanceToday', 'recentAttendances'));
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
