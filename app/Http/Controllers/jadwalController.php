<?php

namespace App\Http\Controllers;

use App\Models\Schedules;
use App\Models\Employees;
use App\Models\Branches;
use App\Models\Roles;
use App\Models\Vacations;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class jadwalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     // Ambil bulan & tahun dari query supaya sinkron dengan form
    //     $bulan = (int) $request->input('bulan', now()->addMonth()->month);
    //     $tahun = (int) $request->input('tahun', now()->year);
    //     $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    //     // Ambil hasil generate dari session (yang di-set pada generate())
    //     $jadwalFlat = session('jadwal'); // array flat seperti yang kamu kirim

    //     // Bentuk struktur untuk Blade: $calendars[DAY][BRANCH_NAME][SHIFT][] = employee
    //     $calendars = [];

    //     foreach ($jadwalFlat as $row) {
    //         // day: integer 1..31 (safe untuk index)
    //         $day = isset($row['tanggal']) ? (int) ltrim($row['tanggal'], '0') : null;
    //         if (!$day) continue;

    //         // label cabang: pakai nama jika ada, fallback ke "Cabang {id}"
    //         $branchKey = $row['cabang'] ?? ('Cabang ' . ($row['id_cabang'] ?? '-'));

    //         // shift: jika libur -> "Libur", selain itu pakai 'Pagi'/'Siang'
    //         $shift = !empty($row['libur']) ? 'Libur' : ($row['shift'] ?? '-');

    //         // data employee sesuai yang dibaca Blade
    //         $calendars[$day][$branchKey][$shift][] = [
    //             'nama_karyawan' => $row['karyawan'] ?? '-',
    //             'libur'         => (bool)($row['libur'] ?? false),
    //             'id_karyawan'   => $row['id_karyawan'] ?? null,
    //         ];
    //     }

    //     // (Opsional) urutkan day agar rapi
    //     ksort($calendars);

    //     dd($jadwalFlat);
    //     // Kirim juga $jadwal (flat) untuk tombol "Simpan Jadwal"
    //     $jadwal = $jadwalFlat;


    //     return view('jadwal.index', compact(
    //         'bulan', 'tahun', 'totalDaysInMonth', 'calendars', 'jadwal'
    //     ));
    // }

    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->addMonth()->month);
        $tahun = (int) $request->input('tahun', now()->year);
        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        $firstDay = Carbon::create($tahun, $bulan, 1);
        $lastDay  = $firstDay->copy()->endOfMonth();

        // Cek apakah jadwal sudah tersimpan di DB
        $hasSaved = Schedules::whereBetween('date', [$firstDay, $lastDay])->exists();

        if ($hasSaved) {
            // 🔹 Ambil jadwal langsung dari database
            $flat = Schedules::with(['branches:id,name', 'employees:id,name'])
                ->whereBetween('date', [$firstDay, $lastDay])
                ->orderBy('date')
                ->get();

            // Ubah jadi struktur sama seperti session version
            $jadwalFlat = $flat->map(function ($r) {
                return [
                    'id'           => $r->id,
                    'id_cabang'    => $r->id_branch,
                    'cabang'       => $r->branches->name ?? 'Cabang ' . $r->branch_id,
                    'id_karyawan'  => $r->id_employee,
                    'karyawan'     => $r->employees->name ?? '-',
                    'tanggal'      => str_pad(Carbon::parse($r->date)->day, 2, '0', STR_PAD_LEFT),
                    'tanggal_full' => $r->date,
                    'bulan'        => Carbon::parse($r->date)->month,
                    'tahun'        => Carbon::parse($r->date)->year,
                    'shift'        => $r->shift,
                    'libur'        => (bool) $r->is_vacation,
                ];
            })->toArray();

            // return response()->json($jadwalFlat, 200);
        } else {
            // 🔹 Belum disimpan → ambil dari session flash
            $jadwalFlat = session('jadwal', []);
        }

        // Build calendar view (fungsi sama)
        $calendars = $this->buildCalendars($jadwalFlat);

        return view('jadwal.index', compact(
            'bulan',
            'tahun',
            'totalDaysInMonth',
            'calendars',
            'jadwalFlat',
            'hasSaved'
        ))->with('jadwal', $jadwalFlat);
    }

    private function buildCalendars(array $jadwalFlat): array
    {
        $cal = [];
        foreach ($jadwalFlat as $row) {
            $day = isset($row['tanggal']) ? (int) ltrim($row['tanggal'], '0') : null;
            if (!$day) continue;

            $branchKey = $row['cabang'] ?? ('Cabang ' . ($row['id_cabang'] ?? '-'));

            // Selalu pakai shift asli (Pagi/Siang), walau libur
            $shift = $row['shift'] ?? 'Pagi';

            $cal[$day][$branchKey][$shift][] = [
                'id' => $row['id'] ?? null,
                'nama_karyawan' => $row['karyawan'] ?? '-',
                'libur'         => (bool)($row['libur'] ?? false),
                'id_karyawan'   => $row['id_karyawan'] ?? null,
            ];
        }
        ksort($cal);

        foreach ($cal as &$branches) {
            uksort($branches, function ($a, $b) {
                // extract number if exists (e.g., "Cabang 2" -> 2)
                preg_match('/\d+/', $a, $ma);
                preg_match('/\d+/', $b, $mb);
                $numA = $ma[0] ?? 0;
                $numB = $mb[0] ?? 0;

                // compare numeric first, fallback to string
                return $numA == $numB
                    ? strnatcasecmp($a, $b)
                    : ($numA <=> $numB);
            });
        }
        unset($branches);

        return $cal;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function generate(Request $request)
    {
        // Jangan flush seluruh session (bisa logout). Hapus yang perlu saja.
        session()->forget('jadwal');

        $bulan = (int) $request->input('bulan', now()->addMonth()->month);
        $tahun = (int) $request->input('tahun', now()->year);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        $cabangs   = Branches::with('employees.roles')->get();
        $pasangans = Roles::with('employees')->get();

        $liburs = Vacations::whereDate('date_of_vacation', '>=', Carbon::create($tahun, $bulan, 1))
            ->whereDate('date_of_vacation', '<=', Carbon::create($tahun, $bulan, $daysInMonth))
            ->get();

        // Index libur -> sama seperti sebelumnya
        $leaveByEmp = [];
        foreach ($liburs as $lv) {
            $eid = $lv->id_employee;
            $d   = Carbon::parse($lv->date_of_vacation)->toDateString();
            $leaveByEmp[$eid][$d] = true;
        }

        $jadwal = [];

        // ======== RANDOMNESS CONTROL ========
        // If you pass ?seed=12345, schedule becomes reproducible.
        // If no seed is provided, we use random_int (OS entropy) -> different each click.
        $seed = $request->query('seed');
        if ($seed !== null) {
            mt_srand((int)$seed);
            $rand = fn(int $min, int $max) => mt_rand($min, $max);
        } else {
            $rand = fn(int $min, int $max) => random_int($min, $max);
        }
        // ====================================

        $pairState = [];

        foreach ($cabangs as $cabang) {
            $cabangPasangans = $pasangans->filter(function ($role) use ($cabang) {
                return $role->employees->contains(fn ($k) => $k->id_branch == $cabang->id);
            });

            foreach ($cabangPasangans as $pasangan) {
                $pair = $pasangan->employees->where('id_branch', $cabang->id)->values();
                $karyawan1 = $pair[0] ?? null;
                $karyawan2 = $pair[1] ?? null;
                if (!$karyawan1 && !$karyawan2) continue;

                $empIds = array_filter([$karyawan1->id ?? null, $karyawan2->id ?? null]);
                sort($empIds);
                $pairKey = 'c'.$cabang->id.'-r'.$pasangan->id.'-'.implode('-', $empIds);

                if (!isset($pairState[$pairKey])) {
                    $pairState[$pairKey] = [
                        'pagi_count'   => [],
                        'last_pagi_emp'=> null,
                        'pagi_streak'  => [],
                    ];
                    foreach ($empIds as $eid) {
                        $pairState[$pairKey]['pagi_count'][$eid]  = 0;
                        $pairState[$pairKey]['pagi_streak'][$eid] = 0;
                    }
                }

                // Fisher–Yates shuffle -> benar-benar acak setiap generate
                $days = range(1, $daysInMonth);
                for ($i = $daysInMonth - 1; $i > 0; $i--) {
                    $j = $rand(0, $i);
                    [$days[$i], $days[$j]] = [$days[$j], $days[$i]];
                }

                foreach ($days as $day) {
                    $dateObj   = Carbon::create($tahun, $bulan, $day);
                    $tglString = $dateObj->toDateString();
                    $tanggal   = str_pad((string)$day, 2, '0', STR_PAD_LEFT);

                    $libur1 = $karyawan1 ? isset($leaveByEmp[$karyawan1->id][$tglString]) : false;
                    $libur2 = $karyawan2 ? isset($leaveByEmp[$karyawan2->id][$tglString]) : false;

                    $push = function ($emp, $shift, $libur) use (&$jadwal, $cabang, $tanggal, $bulan, $tahun, $tglString) {
                        $jadwal[] = [
                            'id_cabang'    => $cabang->id,
                            'cabang'       => $cabang->name,
                            'karyawan'     => $emp ? $emp->name : null,
                            'id_karyawan'  => $emp->id ?? null,
                            'tanggal'      => $tanggal,
                            'tanggal_full' => $tglString,
                            'bulan'        => $bulan,
                            'tahun'        => $tahun,
                            'shift'        => $shift,      // Pagi / Siang
                            'libur'        => (bool) $libur // Jika libur → tetap "Pagi", libur=true
                        ];
                    };

                    // LIBUR rules (tetap "Pagi", libur=true, tidak hitung fairness)
                    if ($karyawan1 && $karyawan2) {
                        if ($libur1 && !$libur2) { $push($karyawan1,'Pagi',true);  $push($karyawan2,'Siang',false); $pairState[$pairKey]['last_pagi_emp']=null; continue; }
                        if (!$libur1 && $libur2) { $push($karyawan1,'Siang',false); $push($karyawan2,'Pagi',true);  $pairState[$pairKey]['last_pagi_emp']=null; continue; }
                        if ($libur1 && $libur2)  { $push($karyawan1,'Pagi',true);  $push($karyawan2,'Pagi',true);  $pairState[$pairKey]['last_pagi_emp']=null; continue; }
                    } else {
                        $solo = $karyawan1 ?? $karyawan2;
                        if ($solo) {
                            $isLibur = isset($leaveByEmp[$solo->id][$tglString]);
                            if ($isLibur) { $push($solo,'Pagi',true); continue; }
                            // Single: variasi dengan random & balance Pagi count
                            $eid = $solo->id;
                            $choosePagi = ($rand(0,1) === 1);
                            $shift = $choosePagi ? 'Pagi' : 'Siang';
                            $push($solo,$shift,false);
                            if ($shift==='Pagi') {
                                $pairState[$pairKey]['pagi_count'][$eid] = ($pairState[$pairKey]['pagi_count'][$eid] ?? 0) + 1;
                                $pairState[$pairKey]['last_pagi_emp'] = $eid;
                                $pairState[$pairKey]['pagi_streak'][$eid] = ($pairState[$pairKey]['pagi_streak'][$eid] ?? 0) + 1;
                            }
                            continue;
                        }
                    }

                    // Keduanya bekerja → pilih Pagi secara adil + variasi
                    if ($karyawan1 && $karyawan2) {
                        $e1 = $karyawan1->id; $e2 = $karyawan2->id;
                        $c1 = $pairState[$pairKey]['pagi_count'][$e1] ?? 0;
                        $c2 = $pairState[$pairKey]['pagi_count'][$e2] ?? 0;

                        // pilih yang Pagi-nya lebih sedikit; kalau imbang → random
                        $pagiFor = ($c1 < $c2) ? $e1 : ( ($c2 < $c1) ? $e2 : (($rand(0,1)===0)?$e1:$e2) );

                        // anti-streak (maks 2x Pagi berturut-turut)
                        $streak1 = $pairState[$pairKey]['pagi_streak'][$e1] ?? 0;
                        $streak2 = $pairState[$pairKey]['pagi_streak'][$e2] ?? 0;
                        if ($pagiFor === $e1 && $streak1 >= 2) $pagiFor = $e2;
                        if ($pagiFor === $e2 && $streak2 >= 2) $pagiFor = $e1;

                        // variasi tambahan: kalau sama dengan kemarin, 50% swap
                        $last = $pairState[$pairKey]['last_pagi_emp'];
                        if ($last !== null && $last === $pagiFor && $rand(0,1)===1) {
                            $pagiFor = ($pagiFor === $e1) ? $e2 : $e1;
                        }

                        if ($pagiFor === $e1) {
                            $push($karyawan1,'Pagi',false);
                            $push($karyawan2,'Siang',false);
                            $pairState[$pairKey]['pagi_count'][$e1] = $c1 + 1;
                            $pairState[$pairKey]['last_pagi_emp'] = $e1;
                            $pairState[$pairKey]['pagi_streak'][$e1] = $streak1 + 1;
                            $pairState[$pairKey]['pagi_streak'][$e2] = 0;
                        } else {
                            $push($karyawan1,'Siang',false);
                            $push($karyawan2,'Pagi',false);
                            $pairState[$pairKey]['pagi_count'][$e2] = $c2 + 1;
                            $pairState[$pairKey]['last_pagi_emp'] = $e2;
                            $pairState[$pairKey]['pagi_streak'][$e2] = $streak2 + 1;
                            $pairState[$pairKey]['pagi_streak'][$e1] = 0;
                        }
                    }
                }
            }
        }

        // Simpan ke session
        session(['jadwal' => $jadwal]);
        // return response()->json($jadwal, 200);
        return redirect()
        ->route('jadwal.index', ['bulan' => $bulan, 'tahun' => $tahun])
        ->with('jadwal', $jadwal);
    }

    
    /**
     * Store a newly created resource in storage.
     *
     */
    public function store(Request $request)
    {
        $items = json_decode($request->input('jadwal', '[]'), true);
        // return response()->json($items, 200);
        if (!is_array($items) || empty($items)) {
            return back()->with('error', 'Tidak ada data jadwal untuk disimpan.');
        }

        $first = $items[0] ?? [];
        $bulan = (int)($first['bulan'] ?? now()->month);
        $tahun = (int)($first['tahun'] ?? now()->year);
        $firstDay = Carbon::create($tahun, $bulan, 1)->toDateString();
        $lastDay  = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();

        DB::transaction(function () use ($items, $firstDay, $lastDay) {
            // 1) Hapus bulan ini dulu (idempotent)
            Schedules::whereBetween('date', [$firstDay, $lastDay])->delete();

            // 2) Susun baris baru
            $rows = [];
            foreach ($items as $r) {
                if (empty($r['id_cabang']) || empty($r['id_karyawan']) || empty($r['tanggal_full']) || empty($r['shift'])) {
                    continue;
                }
                $rows[] = [
                    'id_branch'   => (int)$r['id_cabang'],
                    'id_employee' => (int)$r['id_karyawan'],
                    'date'        => $r['tanggal_full'],                   // 'Y-m-d'
                    'shift'       => $r['shift'] === 'Siang' ? 'Siang' : 'Pagi',
                    'is_vacation'    => (bool)($r['libur'] ?? false),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            // 3) Bulk insert per 1.000 baris
            foreach (array_chunk($rows, 1000) as $chunk) {
                Schedules::insert($chunk);
            }
        });

        return redirect()->route('jadwal.index', [
            'bulan' => $bulan,
            'tahun' => $tahun,
        ])->with('success', 'Jadwal bulan ini tersimpan.');
    }

    public function print(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $firstDay = Carbon::create($tahun, $bulan, 1);
        $lastDay  = $firstDay->copy()->endOfMonth();

        // Ambil jadwal sebulan dari DB
        $flat = Schedules::with(['branches:id,name','employees:id,name'])
            ->whereBetween('date', [$firstDay->toDateString(), $lastDay->toDateString()])
            ->orderBy('date')
            ->get();

        // Ubah ke format jadwalFlat (kompatibel dengan buildCalendars)
        $jadwalFlat = $flat->map(function ($r) {
            $d = Carbon::parse($r->date);
            return [
                'id_cabang'    => $r->id_branch,
                'cabang'       => $r->branches->name ?? ('Cabang '.$r->branch_id),
                'id_karyawan'  => $r->id_employee,
                'karyawan'     => $r->employees->name ?? '-',
                'tanggal'      => str_pad((string)$d->day, 2, '0', STR_PAD_LEFT),
                'tanggal_full' => $d->toDateString(),
                'bulan'        => (int)$d->month,
                'tahun'        => (int)$d->year,
                'shift'        => $r->shift,                // 'Pagi' / 'Siang'
                'libur'        => (bool)$r->is_vacation,
            ];
        })->toArray();

        // Pakai helper yang sudah kamu punya
        $calendars = $this->buildCalendars($jadwalFlat);

        // Info untuk header kalender
        $monthName = $firstDay->translatedFormat('F Y');
        $totalDaysInMonth = $firstDay->daysInMonth;
        $startWeekday = (int) $firstDay->dayOfWeekIso; // 1..7 (Mon..Sun)

        return view('jadwal.print', compact(
            'bulan','tahun','monthName','calendars','totalDaysInMonth','startWeekday'
        ));
    }

    public function dayShow(Request $request)
    {
        $date = Carbon::parse($request->query('date'))->toDateString();

        // Get all rows on that date
        $rows = Schedules::with(['branches:id,name','employees:id,name'])
            ->whereDate('date', $date)
            ->orderBy('id_branch')->orderBy('shift')
            ->get()
            ->map(function($r){
                return [
                    'id'          => $r->id,
                    'id_branch'   => $r->id_branch,
                    'branch_name' => $r->branches->name ?? ('Cabang '.$r->branch_id),
                    'id_employee' => $r->id_employee,
                    'employee'    => $r->employees->name ?? '-',
                    'shift'       => $r->shift,       // Pagi/Siang
                    'is_vacation'    => (bool)$r->is_vacation,
                ];
            });

        // Employee choices grouped per branch (for selects)
        $branches = Branches::with(['employees:id,id_branch,name'])
            ->get(['id','name'])
            ->map(function($b){
                return [
                    'id'   => $b->id,
                    'name' => $b->name,
                    'employees' => $b->employees->map(fn($e)=>['id'=>$e->id,'name'=>$e->name])->values(),
                ];
            });

        return response()->json([
            'date' => $date,
            'items' => $rows,
            'branches' => $branches,
        ]);
    }

    public function dayUpdate(Request $request)
    {
        $validated = $request->validate([
            'date'   => ['required','date'],
            'items'  => ['array'],
            'items.*.id'          => ['nullable','integer','exists:schedules,id'],
            'items.*.id_branch'   => ['required','exists:branches,id'],
            'items.*.id_employee' => ['required','exists:employees,id'],
            'items.*.shift'       => ['required', Rule::in(['Pagi','Siang'])],
            'items.*.is_vacation'    => ['boolean'],
        ]);

        $date = Carbon::parse($validated['date'])->toDateString();
        $items = $validated['items'] ?? [];

        // Rule: employee cannot have >1 row on same date
        $employeeIds = array_filter(array_map(fn($i)=>$i['id_employee'] ?? null, $items));
        if (count($employeeIds) !== count(array_unique($employeeIds))) {
            return response()->json([
                'message' => 'Setiap karyawan hanya boleh satu entri pada tanggal ini.'
            ], 422);
        }

        DB::transaction(function() use ($date, $items) {
            // Strategy: replace-by-date (simple & safe)
            Schedules::whereDate('date', $date)->delete();

            $rows = [];
            $now = now();
            foreach ($items as $i) {
                $rows[] = [
                    'id_branch'   => (int)$i['id_branch'],
                    'id_employee' => (int)$i['id_employee'],
                    'date'        => $date,
                    'shift'       => $i['shift'] === 'Siang' ? 'Siang' : 'Pagi',
                    'is_vacation'    => (bool)($i['is_vacation'] ?? false),
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
            if (!empty($rows)) {
                Schedules::insert($rows);
            }
        });

        return response()->json(['message' => 'Jadwal tanggal ini diperbarui.']);
    }

    public function destroy(Request $request)
    {
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        $start = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $end   = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        $deleted = Schedules::whereBetween('date', [$start, $end])->delete();

        return back()->with('status', "Jadwal bulan {$bulan}/{$tahun} telah dihapus ({$deleted} entri).");
    }
}
