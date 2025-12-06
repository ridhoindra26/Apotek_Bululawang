@extends('layout.layout')

@section('title','Kelola Jadwal Karyawan')
@section('page_title','Kelola Jadwal Karyawan')
@section('content')
@php
    use Carbon\Carbon;
    // Fallbacks
    $bulan  = isset($bulan) ? (int)$bulan : (int) request('bulan', now()->addMonth()->month);
    $tahun  = isset($tahun) ? (int)$tahun : (int) request('tahun', now()->year);
    $totalDaysInMonth = isset($totalDaysInMonth)
        ? (int)$totalDaysInMonth
        : cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $calendars = $calendars ?? [];
    $jadwal    = $jadwal ?? (session('jadwal') ?? []);

    // Untuk navigasi bulan
    $current  = Carbon::create($tahun, $bulan, 1);
    $prev     = $current->copy()->subMonth();
    $next     = $current->copy()->addMonth();

    // Ambil semua nama cabang dari struktur calendars (unik)
    $allBranches = [];
    foreach ($calendars as $day => $branches) {
        if (!is_array($branches)) continue;
        foreach (array_keys($branches) as $bk) {
            $allBranches[$bk] = true;
        }
    }
    $allBranches = array_keys($allBranches);
    sort($allBranches);

    // Filter cabang aktif (via query)
    $selectedBranch = request('branch'); // string atau null
@endphp

<div class="container-fluid mx-auto px-2 sm:px-4 lg:px-6">
    {{-- Sticky header --}}
    <div class="sticky top-0 z-10 bg-white/80 backdrop-blur border-b border-gray-100 rounded px-3 sm:px-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 py-3">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                <h2 class="text-xl sm:text-2xl font-bold">
                    Kelola Jadwal Karyawan
                </h2>
                <span class="inline-flex items-center text-xs sm:text-sm px-2 py-1 rounded-full bg-gray-100">
                    {{ \Carbon\Carbon::create($tahun, $bulan, 1)->locale('id_ID')->translatedFormat('F Y') }}
                </span>
            </div>

            <div class="flex items-center gap-2 justify-start md:justify-end">
                {{-- Navigasi Bulan --}}
                <a href="{{ route('jadwal.index', ['bulan' => $prev->month, 'tahun' => $prev->year]) }}"
                   class="px-3 py-2 rounded-md border hover:bg-gray-50 text-sm"
                   title="Bulan sebelumnya">←</a>

                <a href="{{ route('jadwal.index', ['bulan' => $next->month, 'tahun' => $next->year]) }}"
                   class="px-3 py-2 rounded-md border hover:bg-gray-50 text-sm"
                   title="Bulan berikutnya">→</a>
            </div>
        </div>

        {{-- Bar filter --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3">
            <form action="{{ route('jadwal.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                {{-- Bulan --}}
                <select name="bulan" class="px-3 py-2 border rounded-md text-xs sm:text-sm">
                    @php
                        $bulanList = [
                            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                            7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
                        ];
                    @endphp
                    @foreach ($bulanList as $num => $label)
                        <option value="{{ $num }}" {{ (int)$bulan === (int)$num ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                {{-- Tahun dinamis --}}
                @php
                    $yearStart = now()->year - 1;
                    $yearEnd   = now()->year + 2;
                @endphp
                <select name="tahun" class="px-3 py-2 border rounded-md text-xs sm:text-sm">
                    @for ($y = $yearStart; $y <= $yearEnd; $y++)
                        <option value="{{ $y }}" {{ (int)$tahun === (int)$y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                <button type="submit"
                    class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs sm:text-sm w-full sm:w-auto text-center">
                    Filter
                </button>

                @if (!$hasSaved)
                    {{-- Jika belum disimpan → tampilkan tombol Generate --}}
                    <button type="submit"
                        formaction="{{ url('/jadwal/generate') }}"
                        class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs sm:text-sm w-full sm:w-auto">
                        Menyusun Jadwal
                    </button>
                @else
                    {{-- Jika sudah tersimpan → tampilkan notifikasi --}}
                    <span class="px-3 py-2 text-xs sm:text-sm bg-green-50 border border-green-200 text-green-700 rounded-md">
                        Jadwal bulan ini sudah tersimpan di database
                    </span>
                    <a href="{{ route('jadwal.print', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                        target="_blank"
                        class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs sm:text-sm w-full sm:w-auto text-center">
                        Cetak Jadwal
                    </a>
                @endif
            </form>

            @if (!$hasSaved && !empty($jadwal))
                {{-- Tampilkan tombol simpan hanya kalau hasil generate ada & belum disimpan --}}
                <form action="{{ route('jadwal.store') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <textarea name="jadwal" class="hidden">@json($jadwal)</textarea>
                    <button type="submit"
                            class="w-full sm:w-auto px-3 py-2 bg-green-600 text-white rounded-md text-xs sm:text-sm">
                        Simpan Jadwal
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Kalender --}}
    @php
        $firstDayOfMonth = Carbon::create($tahun, $bulan, 1);
        $firstWeekday = $firstDayOfMonth->dayOfWeekIso; // 1=Senin ... 7=Minggu
        $lastDayOfMonth = Carbon::create($tahun, $bulan, $totalDaysInMonth);
        $lastWeekday = $lastDayOfMonth->dayOfWeekIso;

        // Total cell = offset awal + hari bulan + offset akhir
        $leadingEmpty = $firstWeekday - 1; // kosong sebelum tanggal 1
        $trailingEmpty = 7 - $lastWeekday; // kosong setelah akhir bulan
    @endphp

    {{-- Grid: mobile=1, sm=2, md=4, lg=7 --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 sm:gap-4 mt-4">
        {{-- OFFSET KOSONG SEBELUM TANGGAL 1 (hanya tampil di md ke atas) --}}
        @for ($i = 0; $i < $leadingEmpty; $i++)
            <div class="hidden md:block bg-gray-100 p-3 rounded-xl border"></div>
        @endfor

        {{-- LOOP TANGGAL 1 SAMPAI AKHIR --}}
        @for ($day = 1; $day <= $totalDaysInMonth; $day++)
            @php
                $dateObj = Carbon::create($tahun, $bulan, $day);
                $itemsForDay = $calendars[$day] ?? null;

                $isWeekend = $dateObj->isSunday();
                $bg = $isWeekend ? 'bg-red-50' : 'bg-gray-50';

                // Jika filter cabang aktif, saring cabang hari ini
                if ($selectedBranch && is_array($itemsForDay)) {
                    $itemsForDay = array_key_exists($selectedBranch, $itemsForDay)
                        ? [$selectedBranch => $itemsForDay[$selectedBranch]]
                        : [];
                }
            @endphp

            <div class="{{ $bg }} p-2 sm:p-3 rounded-xl shadow-sm border hover:shadow-md transition text-xs sm:text-sm">
                <div class="flex items-center justify-between gap-2">
                    <h4 class="text-xs sm:text-sm font-bold">
                        {{ $dateObj->locale('id_ID')->translatedFormat('l, j') }}
                    </h4>
                    @if($hasSaved)
                        <button
                            type="button"
                            class="text-[10px] sm:text-xs px-2 py-1 rounded bg-yellow-600 text-white hover:bg-yellow-700 whitespace-nowrap"
                            onclick="openEditDay('{{ $dateObj->toDateString() }}')">
                            Ubah
                        </button>
                    @endif
                </div>

                {{-- Daftar shift & karyawan --}}
                @if (!empty($itemsForDay) && is_array($itemsForDay))
                    <div class="grid grid-cols-1 gap-2 sm:gap-3 mt-2 sm:mt-3">
                        @foreach ($itemsForDay as $branchKey => $shifts)
                            @php
                                $branchLabel = is_string($branchKey) && !ctype_digit($branchKey)
                                    ? $branchKey
                                    : ('Cabang ' . $branchKey);
                                $priority = ['Pagi' => 1, 'Siang' => 2];
                                if (is_array($shifts)) {
                                    uksort($shifts, function($a, $b) use ($priority) {
                                        return ($priority[$a] ?? 98) <=> ($priority[$b] ?? 98);
                                    });
                                }
                            @endphp

                            <div class="border rounded-lg bg-white">
                                <div class="px-3 py-2 border-b font-medium flex items-center justify-between text-xs sm:text-sm">
                                    <span class="truncate">{{ $branchLabel }}</span>
                                </div>
                                <div class="p-2 sm:p-3 grid grid-cols-1 gap-2">
                                    @foreach ($shifts as $shift => $employees)
                                        <div>
                                            <div class="text-[11px] sm:text-sm font-semibold mb-1">
                                                Shift {{ $shift }}
                                            </div>
                                            @if (is_array($employees) && !empty($employees))
                                                <ul class="space-y-1 p-0">
                                                    @foreach ($employees as $employee)
                                                        @php
                                                            $empName = $employee['nama_karyawan'] ?? $employee['karyawan'] ?? '—';
                                                            $isLibur = (bool)($employee['libur'] ?? false);
                                                            $dotClass = $shift === 'Pagi' ? 'bg-[#ffc107]' : 'bg-[#3498db]';
                                                            if ($isLibur) $dotClass = 'bg-red-500';
                                                        @endphp
                                                        <li class="flex items-center gap-2 text-[11px] sm:text-xs md:text-sm">
                                                            <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full {{ $dotClass }}"></span>
                                                            <span class="{{ $isLibur ? 'text-red-600' : '' }} truncate">
                                                                {{ $empName }} - {{ $employee['id_role'] ?? '' }}
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <div class="text-[11px] sm:text-xs text-gray-500 italic">Tidak ada data.</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 mt-2 sm:mt-3 text-[11px] sm:text-xs">Tidak ada jadwal untuk tanggal ini.</p>
                @endif
            </div>
        @endfor

        {{-- OFFSET KOSONG SETELAH AKHIR BULAN (hanya tampil di md ke atas) --}}
        @for ($i = 0; $i < $trailingEmpty; $i++)
            <div class="hidden md:block bg-gray-100 p-3 rounded-xl border"></div>
        @endfor
    </div>

    {{-- Rekap sederhana --}}
    @if (!empty($jadwal))
        @php
            // Hitung rekap per karyawan: total Pagi kerja, Siang kerja, Libur
            $rekap = [];
            foreach ($jadwal as $row) {
                $nama = $row['karyawan'] ?? '-';
                if (!isset($rekap[$nama])) {
                    $rekap[$nama] = ['pagi'=>0,'siang'=>0,'libur'=>0];
                }
                if (!empty($row['libur'])) {
                    $rekap[$nama]['libur']++;
                } else {
                    if (($row['shift'] ?? '') === 'Pagi') $rekap[$nama]['pagi']++;
                    if (($row['shift'] ?? '') === 'Siang') $rekap[$nama]['siang']++;
                }
            }
            ksort($rekap);
        @endphp

        <div id="rekap" class="mt-8 sm:mt-10">
            <h3 class="text-lg sm:text-xl font-semibold mb-3">Rekap Bulan Ini</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs sm:text-sm border rounded-lg overflow-hidden">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="text-left px-2 sm:px-3 py-2 border">Karyawan</th>
                            <th class="text-left px-2 sm:px-3 py-2 border">Pagi</th>
                            <th class="text-left px-2 sm:px-3 py-2 border">Siang</th>
                            <th class="text-left px-2 sm:px-3 py-2 border">Libur</th>
                            <th class="text-left px-2 sm:px-3 py-2 border">Total Entri</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rekap as $nama => $r)
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="px-2 sm:px-3 py-2 border font-medium whitespace-nowrap">{{ $nama }}</td>
                                <td class="px-2 sm:px-3 py-2 border text-center">{{ $r['pagi'] }}</td>
                                <td class="px-2 sm:px-3 py-2 border text-center">{{ $r['siang'] }}</td>
                                <td class="px-2 sm:px-3 py-2 border text-center text-red-600">{{ $r['libur'] }}</td>
                                <td class="px-2 sm:px-3 py-2 border text-center">
                                    {{ $r['pagi'] + $r['siang'] + $r['libur'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Aksi cepat --}}
            <div class="mt-4 flex flex-col sm:flex-row flex-wrap sm:justify-between gap-2">
                @if ($hasSaved)
                    <button type="button"
                        id="btnDestroyJadwal"
                        data-bulan="{{ $bulan }}"
                        data-tahun="{{ $tahun }}"
                        data-url="{{ route('jadwal.destroy', ['bulan'=>$bulan,'tahun'=>$tahun]) }}"
                        class="px-3 py-2 rounded-md bg-red-500 hover:bg-red-700 text-white border border-transparent text-xs sm:text-sm w-full sm:w-auto">
                        Menyusun Ulang
                    </button>
                @else
                    <div class="hidden sm:block"></div>
                @endif

                <a href="#top"
                   class="px-3 py-2 rounded-md border hover:bg-gray-50 text-xs sm:text-sm w-full sm:w-auto text-center">
                    Kembali ke Atas
                </a>
            </div>
        </div>
    @endif
</div>

{{-- Modal --}}
<div id="editDayModal"
     class="fixed inset-0 bg-black/40 hidden flex items-center justify-center z-50 px-2">
  <div class="bg-white w-full max-w-3xl rounded-lg shadow-lg p-3 sm:p-4 max-h-[80vh] flex flex-col">
    <div class="flex items-center justify-between mb-3 gap-2">
      <h3 class="text-sm sm:text-lg font-semibold">
        Edit Jadwal <span id="editDayTitle" class="text-gray-600 text-xs sm:text-sm"></span>
      </h3>
      <button class="px-2 py-1 text-sm sm:text-base" onclick="closeEditDay()">✕</button>
    </div>

    <div id="editDayBody"
         class="p-2 sm:p-3 overflow-y-auto overflow-x-hidden flex-1 border rounded-md bg-gray-50">
      <!-- rows injected here -->
    </div>

    <div class="flex flex-col sm:flex-row items-center justify-between mt-4 gap-2">
      <div class="w-full sm:w-auto">
        {{-- <button class="w-full sm:w-auto px-3 py-2 border rounded text-xs sm:text-sm" onclick="addRow()">+ Tambah</button> --}}
      </div>
      <div class="flex gap-2 w-full sm:w-auto justify-end">
        <button class="w-full sm:w-auto px-3 py-2 bg-gray-200 rounded text-xs sm:text-sm" onclick="closeEditDay()">Batal</button>
        <button class="w-full sm:w-auto px-3 py-2 bg-blue-600 text-white rounded text-xs sm:text-sm" onclick="saveEditDay()">Simpan</button>
      </div>
    </div>
  </div>
</div>

@endsection
