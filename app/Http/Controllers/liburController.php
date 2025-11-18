<?php

namespace App\Http\Controllers;

use App\Models\Vacations;
use App\Models\Employees;
use App\Models\Schedules;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'karyawan'   => 'required|exists:employees,id',
            'keterangan' => 'required|string',
        ]);
    
        DB::transaction(function () use ($validated) {
            // Simpan data libur
            Vacations::create([
                'date_of_vacation' => $validated['tanggal'],
                'id_employee'      => $validated['karyawan'],
                'description'      => $validated['keterangan'],
            ]);

            // Update jadwal kalau ada
            Schedules::whereDate('date', $validated['tanggal'])
                ->where('id_employee', $validated['karyawan'])
                ->update([
                    'is_vacation' => 1,
                ]);
        });
    
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
        $validated = $request->validate([
            'tanggal'     => 'required|date',
            'keterangan'  => 'required|string',
        ]);

        $holiday = Vacations::findOrFail($id);

        DB::transaction(function () use ($holiday, $validated) {
            $oldDate    = $holiday->date_of_vacation;
            $newDate    = $validated['tanggal'];
            $employeeId = $holiday->id_employee;

            // Kalau tanggal libur berubah, sinkronkan ke schedules
            if ($oldDate != $newDate) {
                // Reset is_vacation di jadwal tanggal lama (kalau ada)
                Schedules::whereDate('date', $oldDate)
                    ->where('id_employee', $employeeId)
                    ->update([
                        'is_vacation' => 0,
                    ]);

                // Set is_vacation di jadwal tanggal baru (kalau ada)
                Schedules::whereDate('date', $newDate)
                    ->where('id_employee', $employeeId)
                    ->update([
                        'is_vacation' => 1,
                    ]);
            }

            // Update data liburnya
            $holiday->update([
                'date_of_vacation' => $newDate,
                'description'      => $validated['keterangan'],
            ]);
        });
    
        return redirect()->route('libur.index')->with('success', 'Libur berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::transaction(function () use ($id) {
            $holiday = Vacations::findOrFail($id);

            // Simpan dulu nilai yang dibutuhkan sebelum delete
            $tanggal   = $holiday->date_of_vacation;
            $karyawan  = $holiday->id_employee;

            // Reset flag is_vacation di jadwal (kalau ada)
            Schedules::whereDate('date', $tanggal)
                ->where('id_employee', $karyawan)
                ->update([
                    'is_vacation' => 0,
                ]);

            // Hapus liburnya
            $holiday->delete();
        });

        return redirect()->route('libur.index')->with('success', 'Data libur berhasil dihapus.');
    }
}
