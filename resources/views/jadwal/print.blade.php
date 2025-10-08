<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Cetak Jadwal – {{ $monthName }}</title>
<style>
    @page { size: A4 landscape; margin: 0mm; }
    @media print { .noprint { display:none !important; } }

    body { font-family: Arial, Helvetica, sans-serif; background: #fff; color:#111827; }
    h1 { margin: 0 0 8px; font-size: 20px; text-align: center; }
    .toolbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; 
    page-break-after: avoid;
    break-after: avoid-page;
}
    .legend { font-size:12px; color:#6b7280; display:flex; gap:14px; align-items:center; justify-content:center; }
    .dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:6px; vertical-align:middle; }
    .dot-pagi  { background:#ffc107; }
    .dot-siang { background:#3498db; }
    .dot-libur { background:#ef4444; }

    /* Calendar grid */
    .calendar {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        page-break-inside: auto !important;
        break-inside: auto !important;
        page-break-before: avoid !important;
        break-before: avoid-page !important;
        page-break-after: auto !important;
    }
    .cell {
        border:1px solid #d1d5db;
        border-radius:8px;
        min-height: 120px;
        background: #f9fafb;
        display:flex; flex-direction:column;
        page-break-inside: avoid;
        break-inside: avoid;
    }
    .cell-header {
        display:flex; justify-content:center; align-items:center;
        padding:6px 8px; border-bottom:1px solid #d1d5db; background:#fff; border-top-left-radius:8px; border-top-right-radius:8px;
    }
    .date {
        font-weight:700; font-size:13px;
    }
    .dayname {
        font-size:11px; margin-left:4px;
    }
    .content {
        padding:8px; display:flex; flex-direction:column; gap:6px;
    }
    .branch {
        border:1px solid #d1d5db; background:#fff; border-radius:6px;
    }
    .branch-title {
        padding:5px 6px; font-size:12px; font-weight:600; border-bottom:1px solid #d1d5db;
        background:#f3f4f6;
    }
    .shift { padding:6px; }
    .shift-title { font-size:11px; font-weight:700; margin-bottom:3px; }
    .emp { font-size:11px; display:flex; align-items:center; gap:6px; line-height:1.35; }
    .emp.libur { color:#dc2626; font-weight:600; }
    .empty { font-size:11px; color:#6b7280; font-style:italic; }
    .footer { margin-top: 8px; text-align:center; }
    .btn {
        padding:8px 12px; border:1px solid #111827; border-radius:6px; background:#111827; color:#fff; text-decoration:none;
    }
</style>
</head>
<body>

<div class="toolbar">
    <div></div>
    <div>
        <h1>Jadwal Karyawan – {{ $monthName }}</h1>
        <div class="legend">
            <span><span class="dot dot-pagi"></span>Pagi</span>
            <span><span class="dot dot-siang"></span>Siang</span>
            <span><span class="dot dot-libur"></span>Libur</span>
        </div>
    </div>
    <div></div>
</div>

@php
    // Hitung offset awal
    $leading = max(0, $startWeekday - 1);
    $total = $leading + $totalDaysInMonth;
    $rows = (int) ceil($total / 7);
    $cells = $rows * 7;
    $dayNames = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'];
@endphp

<div class="calendar">
    {{-- Leading empty cells --}}
    @for ($i = 0; $i < $leading; $i++)
        <div class="cell"><div class="cell-header"><span class="date">&nbsp;</span></div></div>
    @endfor

    {{-- Actual days --}}
    @for ($day = 1; $day <= $totalDaysInMonth; $day++)
        @php
            $dateObj = \Carbon\Carbon::create($tahun, $bulan, $day);
            $weekday = $dayNames[$dateObj->dayOfWeekIso] ?? '';
            $dayData = $calendars[$day] ?? [];
        @endphp
        <div class="cell">
            <div class="cell-header">
                <span class="date">{{ $day }} -<span class="dayname">{{ $weekday }}</span></span>
            </div>
            <div class="content">
                @if (!empty($dayData))
                    @foreach ($dayData as $branchName => $shifts)
                        @php
                            $priority = ['Pagi'=>1,'Siang'=>2];
                            if (is_array($shifts)) {
                                uksort($shifts, function($a,$b) use ($priority){
                                    return ($priority[$a] ?? 99) <=> ($priority[$b] ?? 99);
                                });
                            }
                        @endphp
                        <div class="branch">
                            <div class="branch-title">{{ $branchName }}</div>
                            <div class="shift">
                                @foreach ($shifts as $shift => $employees)
                                    <div class="shift-title">Shift {{ $shift }}</div>
                                    @if (is_array($employees) && !empty($employees))
                                        @foreach ($employees as $emp)
                                            @php
                                                $name = $emp['nama_karyawan'] ?? $emp['karyawan'] ?? '—';
                                                $isLibur = (bool)($emp['libur'] ?? false);
                                                $dotClass = $isLibur ? 'dot-libur' : ($shift === 'Pagi' ? 'dot-pagi' : 'dot-siang');
                                            @endphp
                                            <div class="emp {{ $isLibur ? 'libur' : '' }}">
                                                <span class="dot {{ $dotClass }}"></span>
                                                <span>{{ $name }}</span>
                                                @if($isLibur)@endif
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="empty">Tidak ada data</div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty">Tidak ada jadwal.</div>
                @endif
            </div>
        </div>
    @endfor

    {{-- Trailing empty cells --}}
    @php $trailing = $cells - $leading - $totalDaysInMonth; @endphp
    @for ($i = 0; $i < $trailing; $i++)
        <div class="cell"><div class="cell-header"><span class="date">&nbsp;</span></div></div>
    @endfor
</div>

<div class="footer noprint">
    <p style="font-size:12px;color:#6b7280;margin:8px 0 12px;">
        Periode: {{ $monthName }} — dicetak pada {{ now()->translatedFormat('d F Y H:i') }}
    </p>
    <button class="btn" onclick="window.print()">🖨️ Print</button>
</div>

</body>
</html>
