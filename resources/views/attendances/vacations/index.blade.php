@extends('layout.layout')

@section('title', 'On Leave Summary')
@section('page_title', 'On Leave Summary')

@section('content')

<div class="mx-auto container-fluid w-full max-w-7xl px-4 sm:px-6 space-y-6">

    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
        <nav class="flex items-center justify-between mb-4 text-sm text-slate-600">
            <div class="flex space-x-2">
                <a href="{{ route('attendances.index') }}"
                class="px-3 py-1.5 rounded-lg border text-sm font-medium
                        {{ request()->routeIs('attendances.index') ? 'bg-[#318f8c] text-white border-[#318f8c]' : 'border-slate-200 hover:bg-slate-50' }}">
                Attendance List
                </a>
                <a href="{{ route('attendances.balance') }}"
                class="px-3 py-1.5 rounded-lg border text-sm font-medium
                        {{ request()->routeIs('attendances.balance') ? 'bg-[#318f8c] text-white border-[#318f8c]' : 'border-slate-200 hover:bg-slate-50' }}">
                Time Balance
                </a>
                <a href="{{ route('attendances.lateness') }}"
                class="px-3 py-1.5 rounded-lg border text-sm font-medium
                        {{ request()->routeIs('attendances.lateness') ? 'bg-[#318f8c] text-white border-[#318f8c]' : 'border-slate-200 hover:bg-slate-50' }}">
                Lateness
                </a>
                <a href="{{ route('attendances.vacations') }}"
                class="px-3 py-1.5 rounded-lg border text-sm font-medium
                        {{ request()->routeIs('attendances.vacations') ? 'bg-[#318f8c] text-white border-[#318f8c]' : 'border-slate-200 hover:bg-slate-50' }}">
                On Leave
                </a>
            </div>
        </nav>

        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between mb-4">
            <form method="GET" action="{{ route('attendances.vacations') }}"
                class="flex flex-col gap-3 sm:flex-row sm:items-end">

                <div class="w-full sm:w-52">
                    <label for="year" class="block text-sm font-semibold text-slate-700">
                        Tahun
                    </label>

                    <select
                        id="year"
                        name="year"
                        class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                    >
                        @foreach (range(now()->year - 5, now()->year + 1) as $itemYear)
                            <option value="{{ $itemYear }}" @selected($year === $itemYear)>
                                {{ $itemYear }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="submit"
                    class="!rounded-md bg-[#318f8c] px-4 py-2 text-white font-semibold"
                >
                    Tampilkan
                </button>
            </form>
        </div>

        {{-- Info --}}
        <div class=" grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                <p class="text-sm font-bold text-blue-800">Cuti</p>
                <p class="mt-1 text-sm text-blue-700">
                    Data cuti karyawan pada tahun {{ $year }}.
                </p>
            </div>

            <div class="rounded-xl border border-yellow-100 bg-yellow-50 p-4">
                <p class="text-sm font-bold text-yellow-800">Tidak Masuk</p>
                <p class="mt-1 text-sm text-yellow-700">
                    Ketidakhadiran selain cuti dan hari libur.
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-bold text-slate-800">Status</p>
                <p class="mt-1 text-sm text-slate-500">
                    Tahun {{ $year }} · Jatah cuti berjalan sampai bulan ini: 
                    <span class="font-semibold text-slate-700">{{ $cutiAllowance }} hari</span>
                </p>
            </div>
        </div>
    </div>

<section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="flex flex-col gap-2 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">
                Data Karyawan
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Tahun {{ $year }} · {{ $employees->total() }} karyawan
            </p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                        Karyawan
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                        Cuti / Jatah
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                        Tidak Masuk
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                        Total
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                        Riwayat Terakhir
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500">
                        Status
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($employees as $employee)
                   @php

                        $absentCount = (int) $employee->absent_count;
                        $cutiCount = (int) $employee->cuti_count;
                        $totalCount = (int) $employee->total_absence_count;

                        $status = match (true) {
                            $cutiCount > $cutiAllowance => [
                                'label' => 'Cuti Melebihi Jatah',
                                'class' => 'bg-rose-100 text-rose-700',
                            ],

                            $absentCount > 0 => [
                                'label' => 'Ada Tidak Masuk',
                                'class' => 'bg-amber-100 text-amber-700',
                            ],

                            $totalCount === 0 => [
                                'label' => 'Tidak Ada Data',
                                'class' => '',
                            ],

                            default => [
                                'label' => 'Dalam Batas',
                                'class' => 'bg-emerald-100 text-emerald-700',
                            ],
                        };

                        $latest = $employee->latest_vacation;
                    @endphp

                    <tr class="transition hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <p class="text-sm font-semibold text-slate-800">
                                {{ $employee->name }}
                            </p>

                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $employee->branches?->name ?? 'Belum ada cabang' }}
                            </p>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex min-w-[42px] justify-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700">
                                {{ $employee->cuti_count }}x
                            </span>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex min-w-[42px] justify-center rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-bold text-yellow-700">
                                {{ $employee->absent_count }}x
                            </span>
                        </td>

                        <td class="px-4 py-3 text-center">
                            @php
                                $cutiExceeded = (int) $employee->total_absence_count > $cutiAllowance;
                            @endphp

                            <span class="inline-flex min-w-[72px] justify-center rounded-full px-2.5 py-1 text-xs font-bold
                                {{ $cutiExceeded ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $employee->total_absence_count }}/{{ $cutiAllowance }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            @if ($latest)
                                @php
                                    $isCuti = $latest->type === 'cuti';
                                @endphp

                                <p class="text-sm font-semibold text-slate-700">
                                    {{ \Carbon\Carbon::parse($latest->date_of_vacation)->locale('id_ID')->translatedFormat('d M Y') }}
                                </p>

                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $isCuti ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $isCuti ? 'Cuti' : 'Tidak Masuk' }}
                                    </span>

                                    @if ($latest->description)
                                        <span class="max-w-[220px] truncate text-xs text-slate-500">
                                            {{ $latest->description }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm text-slate-400">
                                    Belum ada data
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['class'] }}">
                                {{ $status['label'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                            Belum ada data karyawan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($employees->hasPages())
        <div class="border-t border-slate-200 px-4 py-4 sm:px-6">
            {{ $employees->links() }}
        </div>
    @endif
</section>

</div>
@endsection
