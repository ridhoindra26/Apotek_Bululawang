<?php

namespace App\Http\Controllers;

use App\Models\Vacations;
use App\Models\Employees;
use App\Models\Schedules;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class liburController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentMonth = (int) $request->input('bulan', now()->month);
        $currentYear  = (int) $request->input('tahun', now()->year);

        $vacations = Vacations::with('employees:id,name')
            ->whereMonth('date_of_vacation', $currentMonth)
            ->whereYear('date_of_vacation', $currentYear)
            ->orderBy('date_of_vacation')
            ->orderBy('id_employee')
            ->get();

        $holidays = $vacations
            ->map(function ($holiday) {
                return [
                    'id' => $holiday->id,
                    'date' => $holiday->date_of_vacation,
                    'name' => $holiday->employees?->name ?? 'Unknown',
                    'type' => $holiday->type ?? 'libur',
                    'description' => $holiday->description ?? '-',
                ];
            })
            ->groupBy('date')
            ->toArray();

        $summary = $vacations
            ->groupBy('id_employee')
            ->map(function ($days) {
                return [
                    'karyawan' => $days->first()->employees?->name ?? 'Unknown',
                    'total' => $days->count(),

                    // All arrays use the exact same record order.
                    'dates' => $days->pluck('date_of_vacation')->toArray(),
                    'types' => $days->map(fn ($day) => $day->type ?? 'libur')->toArray(),
                    'keterangan' => $days->pluck('description')->toArray(),
                    'ids' => $days->pluck('id')->toArray(),
                ];
            })
            ->values();

        return view('libur.index', compact(
            'holidays',
            'summary',
            'currentMonth',
            'currentYear'
        ));
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
            'type' => ['required', Rule::in(array_keys(Vacations::TYPES))],
            'keterangan' => 'nullable|string',
        ]);
    
        DB::transaction(function () use ($validated) {
            // Simpan data libur
            Vacations::create([
                'date_of_vacation' => $validated['tanggal'],
                'id_employee'      => $validated['karyawan'],
                'description'      => $validated['keterangan'],
                'type'             => $validated['type'],
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
            'keterangan'  => 'nullable|string',
            'type' => ['required', Rule::in(array_keys(Vacations::TYPES))],
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
                'type'             => $validated['type']
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
