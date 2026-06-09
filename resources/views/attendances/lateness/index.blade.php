@extends('layout.layout')

@section('title', 'Lateness Analytics')
@section('page_title', 'Lateness Analytics')

@section('content')
@php
    $formatMinutes = function ($minutes) {
        $minutes = (int) $minutes;

        if ($minutes <= 0) {
            return '0 menit';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours} jam {$mins} menit";
        }

        if ($hours > 0) {
            return "{$hours} jam";
        }

        return "{$mins} menit";
    };

    $dailyLabels = $dailyTrend
        ->pluck('date')
        ->map(fn ($date) => \Carbon\Carbon::parse($date)->format('d M'))
        ->values();

    $dailyLateCounts = $dailyTrend
        ->pluck('late_count')
        ->map(fn ($value) => (int) $value)
        ->values();

    $dailyLateMinutes = $dailyTrend
        ->pluck('late_minutes')
        ->map(fn ($value) => (int) $value)
        ->values();

    $branchLabels = $branchSummary
        ->pluck('branch_name')
        ->values();

    $branchCounts = $branchSummary
        ->pluck('late_count')
        ->map(fn ($value) => (int) $value)
        ->values();

    $withPermission = (int) ($summary->with_permission_count ?? 0);
    $withoutPermission = (int) ($summary->without_permission_count ?? 0);

    $totalLateCount = (int) ($summary->total_late_count ?? 0);
    $totalLateEmployees = (int) ($summary->total_late_employees ?? 0);
    $totalLateMinutes = (int) ($summary->total_late_minutes ?? 0);
    $totalPenaltyMinutes = (int) ($summary->total_penalty_minutes ?? 0);
@endphp

<div class="mx-auto w-full max-w-7xl px-4 sm:px-6 space-y-6">


    {{-- Header + Filters --}}
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
            </div>
        </nav>
        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
            <div class="max-w-xl">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800">
                    Lateness Analytics
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Monitoring keterlambatan karyawan berdasarkan izin, penalti, cabang, dan pola risiko operasional.
                </p>
            </div>

            <form
                method="GET"
                action="{{ route('attendances.lateness') }}"
                class="w-full xl:max-w-3xl"
            >
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    <input
                        type="month"
                        name="month"
                        value="{{ $month }}"
                        class="rounded-lg border-slate-300 text-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                    >

                    <select
                        name="branch_id"
                        class="rounded-lg border-slate-300 text-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                    >
                        <option value="">Semua Cabang</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) $branchId === (string) $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="late_type"
                        class="rounded-lg border-slate-300 text-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                    >
                        <option value="">Semua Tipe</option>
                        <option value="with_permission" @selected($lateType === 'with_permission')>
                            Dengan Izin
                        </option>
                        <option value="without_permission" @selected($lateType === 'without_permission')>
                            Tanpa Izin
                        </option>
                    </select>

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search employee / branch..."
                        class="rounded-lg border-slate-300 text-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                    >
                </div>

                <div class="mt-2 grid grid-cols-2 gap-2">
                    <button
                        type="submit"
                        class="rounded-lg bg-[#318f8c] px-4 py-2 text-sm font-semibold text-white hover:opacity-90"
                    >
                        Apply Filter
                    </button>

                    <a
                        href="{{ route('attendances.lateness') }}"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Total Telat</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">
                {{ $totalLateCount }}x
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Karyawan Telat</p>
            <p class="mt-1 text-2xl font-bold text-slate-800">
                {{ $totalLateEmployees }}
            </p>
        </div>

        <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4 shadow-sm">
            <p class="text-xs text-rose-600">Total Menit Telat</p>
            <p class="mt-1 text-2xl font-bold text-rose-700">
                {{ $formatMinutes($totalLateMinutes) }}
            </p>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 shadow-sm">
            <p class="text-xs text-amber-700">Total Penalti</p>
            <p class="mt-1 text-2xl font-bold text-amber-800">
                {{ $formatMinutes($totalPenaltyMinutes) }}
            </p>
        </div>
    </div>

    {{-- Late Type + Suggestions --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-3">
                <h3 class="font-semibold text-slate-800">
                    Late Type Composition
                </h3>
                <p class="text-xs text-slate-500">
                    Perbandingan telat dengan izin dan tanpa izin.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-3 text-center">
                    <p class="text-xs text-emerald-700">Dengan Izin</p>
                    <p class="text-xl font-bold text-emerald-800">
                        {{ $withPermission }}x
                    </p>
                </div>

                <div class="rounded-xl bg-rose-50 border border-rose-100 p-3 text-center">
                    <p class="text-xs text-rose-700">Tanpa Izin</p>
                    <p class="text-xl font-bold text-rose-800">
                        {{ $withoutPermission }}x
                    </p>
                </div>
            </div>

            <div class="mt-4 h-64">
                <canvas id="lateTypeChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:col-span-2">
            <div class="mb-3">
                <h3 class="font-semibold text-slate-800">
                    Suggestions
                </h3>
                <p class="text-xs text-slate-500">
                    Rekomendasi otomatis berdasarkan pola keterlambatan periode ini.
                </p>
            </div>

            <div class="space-y-3">
                @forelse ($suggestions as $suggestion)
                    @php
                        $boxClass = match($suggestion['type']) {
                            'danger' => 'border-rose-200 bg-rose-50 text-rose-800',
                            'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
                            'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                            default => 'border-sky-200 bg-sky-50 text-sky-800',
                        };
                    @endphp

                    <div class="rounded-xl border p-4 {{ $boxClass }}">
                        <p class="font-semibold">
                            {{ $suggestion['title'] }}
                        </p>
                        <p class="mt-1 text-sm opacity-90">
                            {{ $suggestion['body'] }}
                        </p>
                    </div>
                @empty
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">
                        Belum ada suggestion untuk periode ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Main Charts --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-3">
                <h3 class="font-semibold text-slate-800">
                    Daily Lateness Trend
                </h3>
                <p class="text-xs text-slate-500">
                    Tren jumlah telat dan total menit telat per hari.
                </p>
            </div>

            <div class="h-80">
                <canvas id="dailyLateChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-3">
                <h3 class="font-semibold text-slate-800">
                    Top 10 Employees by Lateness
                </h3>
                <p class="text-xs text-slate-500">
                    Chart hanya menampilkan 10 karyawan dengan jumlah telat tertinggi.
                </p>
            </div>

            <div class="h-[420px]">
                <canvas id="employeeLateTypeChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Branch Chart --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-3">
            <h3 class="font-semibold text-slate-800">
                Branch Comparison
            </h3>
            <p class="text-xs text-slate-500">
                Perbandingan total keterlambatan antar cabang.
            </p>
        </div>

        <div class="h-80">
            <canvas id="branchChart"></canvas>
        </div>
    </div>

    {{-- Employee Summary Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-semibold text-slate-800">
                    All Employee Lateness Summary
                </h3>
                <p class="text-xs text-slate-500">
                    Menampilkan semua karyawan dengan pagination.
                </p>
            </div>

            <p class="text-xs text-slate-500">
                Total: {{ $employeeLateSummary->total() }} employees
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-slate-500">
                        <th class="py-2 pr-4">Employee</th>
                        <th class="py-2 pr-4">Branch</th>
                        <th class="py-2 pr-4">Total Late</th>
                        <th class="py-2 pr-4">With Permission</th>
                        <th class="py-2 pr-4">Without Permission</th>
                        <th class="py-2 pr-4">Total Minutes</th>
                        <th class="py-2 pr-4">Penalty</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($employeeLateSummary as $employee)
                        <tr class="border-b hover:bg-slate-50">
                            <td class="py-2 pr-4 font-semibold text-slate-700">
                                {{ $employee->employee_name }}
                            </td>

                            <td class="py-2 pr-4">
                                {{ $employee->branch_name ?? '-' }}
                            </td>

                            <td class="py-2 pr-4 font-semibold text-slate-800">
                                {{ $employee->total_late_count }}x
                            </td>

                            <td class="py-2 pr-4 text-emerald-700">
                                {{ $employee->with_permission_count }}x
                            </td>

                            <td class="py-2 pr-4 text-rose-700">
                                {{ $employee->without_permission_count }}x
                            </td>

                            <td class="py-2 pr-4">
                                {{ $formatMinutes($employee->total_late_minutes) }}
                            </td>

                            <td class="py-2 pr-4">
                                {{ $formatMinutes($employee->total_penalty_minutes) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-slate-400">
                                Tidak ada data karyawan ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $employeeLateSummary->links() }}
        </div>
    </div>

    {{-- Detail Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="font-semibold text-slate-800">
                    Lateness Detail
                </h3>
                <p class="text-xs text-slate-500">
                    Detail seluruh data keterlambatan berdasarkan filter aktif.
                </p>
            </div>

            <p class="text-xs text-slate-500">
                Total: {{ $latenessRows->total() }} records
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-slate-500">
                        <th class="py-2 pr-4">Date</th>
                        <th class="py-2 pr-4">Employee</th>
                        <th class="py-2 pr-4">Branch</th>
                        <th class="py-2 pr-4">Check-In</th>
                        <th class="py-2 pr-4">Late Type</th>
                        <th class="py-2 pr-4">Late Minutes</th>
                        <th class="py-2 pr-4">Penalty</th>
                        <th class="py-2 pr-4">Note</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($latenessRows as $row)
                        <tr class="border-b hover:bg-slate-50">
                            <td class="py-2 pr-4">
                                {{ optional($row->work_date)->format('Y-m-d') }}
                            </td>

                            <td class="py-2 pr-4 font-semibold text-slate-700">
                                {{ $row->employee_name }}
                            </td>

                            <td class="py-2 pr-4">
                                {{ $row->branch_name ?? '-' }}
                            </td>

                            <td class="py-2 pr-4">
                                {{ $row->check_in_at ? \Carbon\Carbon::parse($row->check_in_at)->format('H:i') : '—' }}
                            </td>

                            <td class="py-2 pr-4">
                                @if ($row->late_type === 'with_permission')
                                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">
                                        Dengan Izin
                                    </span>
                                @elseif ($row->late_type === 'without_permission')
                                    <span class="rounded-full bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700">
                                        Tanpa Izin
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="py-2 pr-4">
                                {{ $formatMinutes($row->late_minutes) }}
                            </td>

                            <td class="py-2 pr-4">
                                {{ $formatMinutes($row->penalty_minutes) }}
                            </td>

                            <td class="py-2 pr-4 max-w-xs truncate" title="{{ $row->minutes_note ?: '' }}">
                                {{ $row->minutes_note ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-4 text-center text-slate-400">
                                Tidak ada data telat ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $latenessRows->links() }}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dailyLabels = @json($dailyLabels);
    const dailyLateCounts = @json($dailyLateCounts);
    const dailyLateMinutes = @json($dailyLateMinutes);

    const employeeChartLabels = @json($employeeChartLabels);
    const employeeChartWithPermission = @json($employeeChartWithPermission);
    const employeeChartWithoutPermission = @json($employeeChartWithoutPermission);

    const branchLabels = @json($branchLabels);
    const branchCounts = @json($branchCounts);

    const withPermission = @json($withPermission);
    const withoutPermission = @json($withoutPermission);

    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    };

    function createChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);

        if (!canvas || typeof Chart === 'undefined') {
            return;
        }

        return new Chart(canvas, config);
    }

    createChart('lateTypeChart', {
        type: 'doughnut',
        data: {
            labels: ['Dengan Izin', 'Tanpa Izin'],
            datasets: [{
                data: [withPermission, withoutPermission]
            }]
        },
        options: defaultOptions
    });

    createChart('dailyLateChart', {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [
                {
                    label: 'Late Count',
                    data: dailyLateCounts,
                    tension: 0.35
                },
                {
                    label: 'Late Minutes',
                    data: dailyLateMinutes,
                    tension: 0.35
                }
            ]
        },
        options: {
            ...defaultOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    createChart('employeeLateTypeChart', {
        type: 'bar',
        data: {
            labels: employeeChartLabels,
            datasets: [
                {
                    label: 'With Permission',
                    data: employeeChartWithPermission
                },
                {
                    label: 'Without Permission',
                    data: employeeChartWithoutPermission
                }
            ]
        },
        options: {
            ...defaultOptions,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    stacked: true,
                    ticks: {
                        precision: 0
                    }
                },
                y: {
                    stacked: true
                }
            }
        }
    });

    createChart('branchChart', {
        type: 'bar',
        data: {
            labels: branchLabels,
            datasets: [{
                label: 'Late Count',
                data: branchCounts
            }]
        },
        options: {
            ...defaultOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>
@endsection