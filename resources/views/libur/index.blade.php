@extends('layout.layout')

@section('content')

<div class="container-fluid mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
{{-- Header --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">
            Kelola Ketidakhadiran Karyawan
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            Kelola data cuti, libur, dan tidak masuk karyawan.
        </p>
    </div>

    <a
        href="{{ route('libur.create') }}"
        class="inline-flex w-fit items-center justify-center rounded-lg bg-[#318f8c] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#287673] focus:outline-none focus:ring-2 focus:ring-[#318f8c] focus:ring-offset-2"
    >
        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Data
    </a>
</div>

{{-- Flash messages --}}
@if (session('success'))
    <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
        {{ session('error') }}
    </div>
@endif

{{-- Filter --}}
<div class="mb-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
    <form
        action="{{ route('libur.index') }}"
        method="GET"
        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:items-end"
    >
        <div>
            <label for="bulan" class="block text-sm font-semibold text-slate-700">
                Bulan
            </label>

            <select
                id="bulan"
                name="bulan"
                class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
            >
                @foreach (range(1, 12) as $month)
                    <option value="{{ $month }}" @selected((int) $currentMonth === $month)>
                        {{ \Carbon\Carbon::create(null, $month)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="tahun" class="block text-sm font-semibold text-slate-700">
                Tahun
            </label>

            <select
                id="tahun"
                name="tahun"
                class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
            >
                @foreach (range(now()->year - 5, now()->year + 5) as $year)
                    <option value="{{ $year }}" @selected((int) $currentYear === $year)>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="lg:col-span-2">
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-700 focus:ring-offset-2 sm:w-auto"
            >
                Terapkan Filter
            </button>

            <a
                href="{{ route('libur.index') }}"
                class="ml-2 inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100"
            >
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Category information --}}
<div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
    <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
        <p class="text-sm font-bold text-blue-800">Cuti</p>
        <p class="mt-1 text-xs text-blue-700">
            Karyawan memiliki izin cuti yang tercatat.
        </p>
    </div>

    <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4">
        <p class="text-sm font-bold text-emerald-800">Libur</p>
        <p class="mt-1 text-xs text-emerald-700">
            Hari libur atau jadwal nonaktif karyawan.
        </p>
    </div>

    <div class="rounded-xl border border-rose-100 bg-rose-50 p-4">
        <p class="text-sm font-bold text-rose-800">Tidak Masuk</p>
        <p class="mt-1 text-xs text-rose-700">
            Ketidakhadiran di luar kategori cuti dan libur.
        </p>
    </div>
</div>

{{-- Calendar --}}
<section class="mb-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:p-6">
    <div class="mb-5">
        <h2 class="text-lg font-bold text-slate-800">
            Kalender Ketidakhadiran
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->translatedFormat('F Y') }}
        </p>
    </div>

    <div id="calendar" class="overflow-x-auto"></div>
</section>

{{-- Summary grouped by employee --}}
<section class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="border-b border-slate-200 px-4 py-4 sm:px-6">
        <h2 class="text-lg font-bold text-slate-800">
            Ringkasan Ketidakhadiran Karyawan
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Total data periode ini: {{ collect($summary)->sum('total') }}
        </p>
    </div>

    <div class="space-y-5 p-4 sm:p-6">
        @forelse ($summary as $item)
            <div class="overflow-hidden rounded-xl border border-slate-200">
                {{-- Employee header --}}
                <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800">
                            {{ $item['karyawan'] }}
                        </h3>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Riwayat ketidakhadiran pada periode terpilih
                        </p>
                    </div>

                    <span class="inline-flex w-fit items-center rounded-full bg-slate-800 px-3 py-1 text-xs font-bold text-white">
                        {{ $item['total'] }} Data
                    </span>
                </div>

                {{-- Records --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Tanggal
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Jenis
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Keterangan
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @foreach ($item['ids'] as $index => $id)
                                @php
                                    $type = $item['types'][$index] ?? 'libur';

                                    $typeLabel = match ($type) {
                                        'cuti' => 'Cuti',
                                        'absent' => 'Tidak Masuk',
                                        default => 'Libur',
                                    };

                                    $typeClass = match ($type) {
                                        'cuti' => 'bg-blue-100 text-blue-700 ring-blue-200',
                                        'absent' => 'bg-rose-100 text-rose-700 ring-rose-200',
                                        default => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                                    };
                                @endphp

                                <tr class="hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-700">
                                        {{ \Carbon\Carbon::parse($item['dates'][$index])->translatedFormat('d F Y') }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $typeClass }}">
                                            {{ $typeLabel }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-sm text-slate-600">
                                        {{ $item['keterangan'][$index] ?? '-' }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        <div class="inline-flex items-center gap-2">
                                            <a
                                                href="{{ route('libur.edit', $id) }}"
                                                class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                            >
                                                Edit
                                            </a>

                                            <form
                                                action="{{ route('libur.destroy', $id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Hapus data ketidakhadiran ini?');"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100"
                                                >
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="py-10 text-center text-sm text-slate-500">
                Belum ada data ketidakhadiran pada periode ini.
            </div>
        @endforelse
    </div>
</section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const holidays = @json($holidays);
    const calendar = document.getElementById('calendar');

    const selectedMonth = {{ (int) $currentMonth }};
    const selectedYear = {{ (int) $currentYear }};

    const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    const typeConfig = {
        cuti: {
            label: 'Cuti',
            className: 'bg-blue-100 text-blue-700',
        },
        libur: {
            label: 'Libur',
            className: 'bg-emerald-100 text-emerald-700',
        },
        absent: {
            label: 'Tidak Masuk',
            className: 'bg-rose-100 text-rose-700',
        },
    };

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = value ?? '';
        return element.innerHTML;
    }

    function generateCalendar() {
        const firstDay = new Date(selectedYear, selectedMonth - 1, 1).getDay();
        const daysInMonth = new Date(selectedYear, selectedMonth, 0).getDate();

        let html = `
            <div class="min-w-[850px]">
                <div class="mb-4 text-center">
                    <h3 class="text-xl font-bold text-slate-800">
                        ${monthNames[selectedMonth - 1]} ${selectedYear}
                    </h3>
                </div>

                <table class="w-full table-fixed border-collapse overflow-hidden rounded-lg border border-slate-200">
                    <thead>
                        <tr class="bg-slate-800 text-white">
                            ${['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
                                .map(day => `<th class="border border-slate-700 p-3 text-xs font-bold">${day}</th>`)
                                .join('')}
                        </tr>
                    </thead>
                    <tbody>
        `;

        let currentDay = 1;

        for (let week = 0; week < 6; week++) {
            html += '<tr>';

            for (let weekday = 0; weekday < 7; weekday++) {
                const emptyBeforeFirstDay = week === 0 && weekday < firstDay;
                const emptyAfterLastDay = currentDay > daysInMonth;

                if (emptyBeforeFirstDay || emptyAfterLastDay) {
                    html += '<td class="h-28 border border-slate-200 bg-slate-50"></td>';
                    continue;
                }

                const dateKey = `${selectedYear}-${String(selectedMonth).padStart(2, '0')}-${String(currentDay).padStart(2, '0')}`;
                const records = holidays[dateKey] || [];

                const recordsHtml = records.map(record => {
                    const config = typeConfig[record.type] || typeConfig.libur;

                    return `
                        <div class="mb-1 rounded px-1.5 py-1 text-left ${config.className}">
                            <div class="break-words text-[11px] font-bold leading-tight">
                                ${escapeHtml(record.name)}
                            </div>
                            <div class="mt-0.5 break-words text-[10px] leading-tight">
                                ${config.label}
                            </div>
                        </div>
                    `;
                }).join('');

                html += `
                    <td class="border border-slate-200 p-1.5 align-top">
                        <div class="mb-1 text-right text-sm font-bold text-slate-700">
                            ${currentDay}
                        </div>

                        <div class="space-y-1">
                            ${recordsHtml}
                        </div>
                    </td>
                `;

                currentDay++;
            }

            html += '</tr>';

            if (currentDay > daysInMonth) {
                break;
            }
        }

        html += `
                    </tbody>
                </table>
            </div>
        `;

        calendar.innerHTML = html;
    }

    generateCalendar();
});
</script>

@endsection
