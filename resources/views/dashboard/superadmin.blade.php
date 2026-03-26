@extends('layout.layout')

@section('title', 'Dashboard Superadmin')

@section('content')
<div class="p-4 md:p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard Superadmin</h1>
            <p class="text-sm text-slate-500">
                Ringkasan operasional Apotek Bululawang hari ini.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('jadwal.index') }}"
               class="inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">
                Kelola Obat
            </a>
            <a href="{{ route('jadwal.index') }}"
               class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                Lihat Penjualan
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-dashboard.stat-card
            title="Total Obat"
            :value="$totalMedicines"
            subtitle="Produk terdaftar"
            color="blue"
            icon="archive-box" />

        <x-dashboard.stat-card
            title="Stok Menipis"
            :value="$lowStockCount"
            subtitle="Perlu restock"
            color="amber"
            icon="exclamation-triangle" />

        <x-dashboard.stat-card
            title="Hampir Expired"
            :value="$expiringSoonCount"
            subtitle="Dalam 90 hari"
            color="rose"
            icon="clock" />

        <x-dashboard.stat-card
            title="Penjualan Hari Ini"
            :value="'Rp ' . number_format($todaySales, 0, ',', '.')"
            subtitle="{{$todayTransactionCount}} transaksi"
            color="emerald"
            icon="banknotes" />
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-dashboard.stat-card
            title="Kehadiran Hari Ini"
            :value="$attendanceTodayCount"
            subtitle="{{$employeeCount}} total karyawan"
            color="indigo"
            icon="users" />

        <x-dashboard.stat-card
            title="Supplier Aktif"
            :value="$activeSupplierCount"
            subtitle="Supplier terhubung"
            color="cyan"
            icon="truck" />

        <x-dashboard.stat-card
            title="Total User"
            :value="$userCount"
            subtitle="Akun sistem"
            color="violet"
            icon="user-circle" />

        <x-dashboard.stat-card
            title="Kas Masuk Hari Ini"
            :value="'Rp ' . number_format($cashInToday, 0, ',', '.')"
            subtitle="Dari transaksi hari ini"
            color="green"
            icon="wallet" />
    </div>

    {{-- Main Panels --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Grafik --}}
        <div class="xl:col-span-2 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-800">Pengunjung Website</h2>
                <p class="text-sm text-slate-500">Monitoring tren website</p>
            </div>

            <div class="h-72 flex items-center justify-center rounded-xl border border-dashed border-slate-300 text-sm text-slate-400">
                Area chart penjualan
            </div>
        </div>

        {{-- Alert --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-800">Alert Operasional</h2>
                <p class="text-sm text-slate-500">Hal yang perlu diperhatikan</p>
            </div>

            <div class="space-y-3">
                <div class="rounded-xl bg-amber-50 p-4 ring-1 ring-amber-200">
                    <p class="text-sm font-semibold text-amber-800">Stok menipis</p>
                    <p class="mt-1 text-sm text-amber-700">{{ $lowStockCount }} item perlu restock.</p>
                </div>

                <div class="rounded-xl bg-rose-50 p-4 ring-1 ring-rose-200">
                    <p class="text-sm font-semibold text-rose-800">Obat hampir expired</p>
                    <p class="mt-1 text-sm text-rose-700">{{ $expiringSoonCount }} item mendekati masa expired.</p>
                </div>

                <div class="rounded-xl bg-blue-50 p-4 ring-1 ring-blue-200">
                    <p class="text-sm font-semibold text-blue-800">Kehadiran</p>
                    <p class="mt-1 text-sm text-blue-700">
                        {{ $employeeCount - $attendanceTodayCount }} karyawan belum check-in.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Panels --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        {{-- Transaksi terbaru --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Transaksi Terbaru</h2>
                    <p class="text-sm text-slate-500">Aktivitas penjualan terakhir</p>
                </div>
                <a href="{{ route('jadwal.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                    Lihat semua
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recentSales as $sale)
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 p-3">
                        <div>
                            <p class="font-medium text-slate-800">{{ $sale->invoice_number ?? 'Transaksi' }}</p>
                            <p class="text-sm text-slate-500">
                                {{ today()}}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-slate-800">
                                Rp {{ number_format($sale->grand_total ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada transaksi terbaru.</p>
                @endforelse
            </div>
        </div>

        {{-- Birthday --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Ulang Tahun</h3>
                <p class="text-sm font-medium text-slate-500">Ulang Tahun Terdekat</p>
            </div>

            <div class="space-y-3">
                @forelse($closestBirthdayEmployees as $employee)
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 p-3">
                        <div>
                            <p class="font-medium text-slate-800">{{ $employee->name }}</p>
                            <p class="text-sm text-slate-500">
                                {{ \Carbon\Carbon::parse($employee->next_birthday)->format('d M Y') }}
                            </p>
                        </div>

                        <div class="text-right">
                            <p class="text-sm font-semibold text-pink-600">
                                @if($employee->days_to_birthday == 0)
                                    Hari ini 🎉
                                @else
                                    {{ $employee->days_to_birthday }} hari lagi
                                @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada data tanggal lahir.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection