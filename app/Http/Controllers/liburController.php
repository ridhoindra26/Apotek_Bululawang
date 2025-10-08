<?php

namespace App\Http\Controllers;

use App\Models\Vacations;
use App\Models\Employees;

use Illuminate\Http\Request;
use Carbon\Carbon;

class liburController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentMonth = $request->input('bulan', now()->month);
        $currentYear = $request->input('tahun', now()->year); 

        $holidays = Vacations::with('employees')
            ->whereMonth('date_of_vacation', $currentMonth)
            ->whereYear('date_of_vacation', $currentYear)
            ->get()
            ->map(function ($holiday) {
                return [
                    'date' => $holiday->date_of_vacation,
                    'name' => $holiday->employees->name ?? 'Unknown',
                ];
            })
            ->groupBy('date');

        $summary = Vacations::with('employees')
            ->whereMonth('date_of_vacation', $currentMonth)
            ->whereYear('date_of_vacation', $currentYear)
            ->get()
            ->groupBy('id_employee')
            ->map(function ($days) {
                return [
                    'karyawan' => $days->first()->employees->name ?? 'Unknown',
                    'dates' => $days->pluck('date_of_vacation')->toArray(),
                    'total' => $days->count(),
                    'keterangan' => $days->pluck('description')->toArray(),
                    'ids' => $days->pluck('id')->toArray(),
                ];
            });

        return view('libur.index', compact('holidays', 'summary', 'currentMonth', 'currentYear'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $karyawans = Employees::all();

        return view('libur.create', compact('karyawans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'karyawan' => 'required|exists:employees,id',
            'keterangan' => 'required|string',
        ]);
    
        $holiday = Vacations::create([
            // 'tanggal' => Carbon::parse($request->tanggal)->day,
            // 'bulan' => Carbon::parse($request->tanggal)->month,
            // 'tahun' => Carbon::parse($request->tanggal)->year,
            'date_of_vacation' => $request->tanggal,
            'id_employee' => $request->karyawan,
            'description' => $request->keterangan
        ]);
    
        return redirect()->route('libur.index')->with('success', 'Libur berhasil ditambahkan.');
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
    public function edit(string $id)
    {
        $holiday = Vacations::with('employees')->findOrFail($id);

        // dd($holiday);

        return view('libur.edit', compact('holiday'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string',
        ]);
    
        $holiday = Vacations::findOrFail($id);
    
        $holiday->update([
            'date_of_vacation' => $request->tanggal,
            'description' => $request->keterangan
        ]);
    
        return redirect()->route('libur.index')->with('success', 'Libur berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $holiday = Vacations::findOrFail($id);
        $holiday->delete();

        return redirect()->route('libur.index')->with('success', 'Data libur berhasil dihapus.');
    }
}
