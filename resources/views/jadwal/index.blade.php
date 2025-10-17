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

<div class="container mx-auto">
    {{-- Sticky header --}}
    <div class="sticky top-0 z-10 bg-white/80 backdrop-blur border-b border-gray-100 rounded px-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 py-3">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold">
                    Kelola Jadwal Karyawan
                </h2>
                <span class="inline-flex items-center text-sm px-2 py-1 rounded-full bg-gray-100">
                    {{ \Carbon\Carbon::create($tahun, $bulan, 1)->locale('id_ID')->translatedFormat('F Y') }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                {{-- Navigasi Bulan --}}
                <a href="{{ route('jadwal.index', ['bulan' => $prev->month, 'tahun' => $prev->year]) }}"
                   class="px-3 py-2 rounded-md border hover:bg-gray-50" title="Bulan sebelumnya">←</a>

                <a href="{{ route('jadwal.index', ['bulan' => $next->month, 'tahun' => $next->year]) }}"
                   class="px-3 py-2 rounded-md border hover:bg-gray-50" title="Bulan berikutnya">→</a>
            </div>
        </div>

        {{-- Bar filter --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3">
            <form action="{{ url('/jadwal/generate') }}" method="GET" class="flex flex-wrap items-center gap-2">
                {{-- Bulan --}}
                <select name="bulan" class="px-3 py-2 border rounded-md">
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
                <select name="tahun" class="px-3 py-2 border rounded-md">
                    @for ($y = $yearStart; $y <= $yearEnd; $y++)
                        <option value="{{ $y }}" {{ (int)$tahun === (int)$y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                {{-- Filter cabang (opsional) --}}
                {{-- <select name="branch" class="px-3 py-2 border rounded-md">
                    <option value="" {{ $selectedBranch ? '' : 'selected' }}>Semua Cabang</option>
                    @foreach ($allBranches as $bk)
                        <option value="{{ $bk }}" {{ $selectedBranch === $bk ? 'selected' : '' }}>
                            {{ $bk }}
                        </option>
                    @endforeach
                </select> --}}

                {{-- Optional: seed untuk reproduksi hasil --}}
                {{-- <input type="text" name="seed" placeholder="Seed (opsional)" value="{{ request('seed') }}"
                       class="px-3 py-2 border rounded-md w-32"/> --}}

                @if (!$hasSaved)
                    {{-- Jika belum disimpan → tampilkan tombol Generate --}}
                    <button type="submit" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">
                        Menyusun Jadwal
                    </button>
                @else
                    {{-- Jika sudah tersimpan → tampilkan notifikasi --}}
                    <span class="px-3 py-2 text-sm bg-green-50 border border-green-200 text-green-700 rounded-md">
                        Jadwal bulan ini sudah tersimpan di database
                    </span>
                    <a href="{{ route('jadwal.print', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                        target="_blank"
                        class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Cetak Jadwal
                    </a>
                @endif
            </form>

            @if (!$hasSaved && !empty($jadwal))
                {{-- Tampilkan tombol simpan hanya kalau hasil generate ada & belum disimpan --}}
                <form action="{{ route('jadwal.store') }}" method="POST">
                    @csrf
                    <textarea name="jadwal" class="hidden">@json($jadwal)</textarea>
                    <button type="submit" class="px-3 py-2 bg-green-600 text-white rounded-md">
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

    <div class="grid grid-cols-7 gap-4 mt-4">
        {{-- ======= OFFSET KOSONG SEBELUM TANGGAL 1 ======= --}}
        @for ($i = 0; $i < $leadingEmpty; $i++)
            <div class="bg-gray-500 p-3 rounded-xl shadow-sm border hover:shadow-md transition">
                
            </div>
        @endfor

        {{-- ======= LOOP TANGGAL 1 SAMPAI AKHIR ======= --}}
        @for ($day = 1; $day <= $totalDaysInMonth; $day++)
            @php
                $dateObj = Carbon::create($tahun, $bulan, $day);
                $itemsForDay = $calendars[$day] ?? null;

                $isWeekend = $dateObj->isSunday();
                $bg = $isWeekend ? 'bg-red-100' : 'bg-gray-50';

                // Jika filter cabang aktif, saring cabang hari ini
                if ($selectedBranch && is_array($itemsForDay)) {
                    $itemsForDay = array_key_exists($selectedBranch, $itemsForDay)
                        ? [$selectedBranch => $itemsForDay[$selectedBranch]]
                        : [];
                }
            @endphp

            <div class="{{ $bg }} p-3 rounded-xl shadow-sm border hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold">
                        {{ $dateObj->locale('id_ID')->translatedFormat('l, j') }}
                    </h4>
                    @if($hasSaved)
                        <button
                            type="button"
                            class="text-xs px-2 py-1 rounded bg-yellow-600 text-white hover:bg-yellow-700"
                            onclick="openEditDay('{{ $dateObj->toDateString() }}')">
                            Ubah
                        </button>
                    @endif
                </div>

                {{-- == Daftar shift & karyawan == --}}
                @if (!empty($itemsForDay) && is_array($itemsForDay))
                    <div class="grid grid-cols-1 gap-3 mt-3">
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
                                <div class="px-3 py-2 border-b font-medium flex items-center justify-between">
                                    <span>{{ $branchLabel }}</span>
                                </div>
                                <div class="p-3 grid grid-cols-1 gap-2">
                                    @foreach ($shifts as $shift => $employees)
                                        <div>
                                            <div class="text-sm font-semibold mb-1">
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
                                                        <li class="flex items-center gap-2 text-sm">
                                                            <span class="w-2.5 h-2.5 rounded-full {{ $dotClass }}"></span>
                                                            <span class="{{ $isLibur ? 'text-red-600' : '' }}">
                                                                {{ $empName }}
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <div class="text-xs text-gray-500 italic">Tidak ada data.</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 mt-3 text-sm">Tidak ada jadwal untuk tanggal ini.</p>
                @endif
            </div>
        @endfor

        {{-- ======= OFFSET KOSONG SETELAH AKHIR BULAN ======= --}}
        @for ($i = 0; $i < $trailingEmpty; $i++)
            <div></div>
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

        <div id="rekap" class="mt-10">
            <h3 class="text-xl font-semibold mb-3">Rekap Bulan Ini</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border rounded-lg overflow-hidden">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="text-left px-3 py-2 border">Karyawan</th>
                            <th class="text-left px-3 py-2 border">Pagi</th>
                            <th class="text-left px-3 py-2 border">Siang</th>
                            <th class="text-left px-3 py-2 border">Libur</th>
                            <th class="text-left px-3 py-2 border">Total Entri</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rekap as $nama => $r)
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="px-3 py-2 border font-medium">{{ $nama }}</td>
                                <td class="px-3 py-2 border">{{ $r['pagi'] }}</td>
                                <td class="px-3 py-2 border">{{ $r['siang'] }}</td>
                                <td class="px-3 py-2 border text-red-600">{{ $r['libur'] }}</td>
                                <td class="px-3 py-2 border">{{ $r['pagi'] + $r['siang'] + $r['libur'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Aksi cepat --}}
            <div class="mt-4 flex flex-wrap justify-between">
                @if ($hasSaved)
                    <button type="button"
                        id="btnDestroyJadwal"
                        data-url="{{ route('jadwal.destroy', ['bulan'=>$bulan,'tahun'=>$tahun]) }}"
                        class="px-3 py-2 rounded-md bg-red-500 hover:bg-red-700 text-white border border-transparent">
                        Menyusun Ulang
                    </button>
                @else
                    <div></div> 
                @endif

                <a href="#top" class="px-3 py-2 rounded-md border hover:bg-gray-50">
                    Kembali ke Atas
                </a>
            </div>
        </div>
    @endif
</div>

{{-- Modal --}}
<div id="editDayModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 flex items-center">
  <div class="bg-white w-full max-w-3xl rounded-lg shadow-lg p-4">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold">Edit Jadwal <span id="editDayTitle" class="text-gray-600 text-sm"></span></h3>
      <button class="px-2 py-1" onclick="closeEditDay()">✕</button>
    </div>

    <div id="editDayBody" class="p-3 overflow-y-auto overflow-x-hidden flex-1 max-h-[60vh]">
      <!-- rows injected here -->
    </div>

    <div class="flex items-center justify-between mt-4">
      {{-- <button class="px-3 py-2 border rounded" onclick="addRow()">+ Tambah</button> --}}
      <div class="space-x-2">
        <button class="px-4 py-2 bg-gray-200 rounded" onclick="closeEditDay()">Batal</button>
        <button class="px-4 py-2 bg-blue-600 text-white rounded" onclick="saveEditDay()">Simpan</button>
      </div>
    </div>
  </div>
</div>

@endsection
