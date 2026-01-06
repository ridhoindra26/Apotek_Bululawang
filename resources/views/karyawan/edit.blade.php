@extends('layout.layout')

@section('title', 'Edit Karyawan')
@section('page_title', 'Edit Karyawan')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-emerald-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-rose-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-4">
        <h2 class="text-xl sm:text-2xl font-semibold text-slate-900">Edit Karyawan</h2>
        <p class="text-sm text-slate-500 mt-1">Perbarui data karyawan sesuai field yang ada di halaman daftar.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <form action="{{ route('karyawan.update', $karyawan->id) }}" method="POST" class="p-6 space-y-5">
            @csrf
            @method('POST')

            {{-- Nama --}}
            <div>
                <label for="name" class="block text-xs font-medium text-slate-600 mb-1">
                    Nama Karyawan <span class="text-rose-600">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $karyawan->name) }}"
                    class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                           @error('name') border-rose-300 focus:border-rose-500 focus:ring-rose-500
                           @else border-slate-200 focus:border-slate-400 focus:ring-slate-400 @enderror"
                    required
                >
                @error('name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Cabang --}}
            <div>
                <label for="id_branch" class="block text-xs font-medium text-slate-600 mb-1">
                    Cabang <span class="text-rose-600">*</span>
                </label>
                <select
                    id="id_branch"
                    name="id_branch"
                    class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm bg-white
                           @error('id_branch') border-rose-300 focus:border-rose-500 focus:ring-rose-500
                           @else border-slate-200 focus:border-slate-400 focus:ring-slate-400 @enderror"
                    required
                >
                    <option value="" disabled {{ old('id_branch', $karyawan->id_branch) ? '' : 'selected' }}>
                        Pilih Cabang
                    </option>
                    @foreach ($cabangs as $cabang)
                        <option value="{{ $cabang->id }}"
                            {{ (string) old('id_branch', $karyawan->id_branch) === (string) $cabang->id ? 'selected' : '' }}>
                            {{ $cabang->name }}
                        </option>
                    @endforeach
                </select>
                @error('id_branch')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Role/Jabatan (sesuai index) --}}
            <div>
                <label for="id_role" class="block text-xs font-medium text-slate-600 mb-1">
                    Role / Jabatan
                </label>
                <select
                    id="id_role"
                    name="id_role"
                    class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm bg-white
                           @error('id_role') border-rose-300 focus:border-rose-500 focus:ring-rose-500
                           @else border-slate-200 focus:border-slate-400 focus:ring-slate-400 @enderror"
                >
                    <option value="" {{ old('id_role', $karyawan->id_role) ? '' : 'selected' }}>
                        Tidak Ada
                    </option>
                    @foreach ($pasangans as $pasangan)
                        <option value="{{ $pasangan->id }}"
                            {{ (string) old('id_role', $karyawan->id_role) === (string) $pasangan->id ? 'selected' : '' }}>
                            {{ $pasangan->name }}
                        </option>
                    @endforeach
                </select>
                @error('id_role')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- No. Telepon (sesuai index) --}}
            <div>
                <label for="phone" class="block text-xs font-medium text-slate-600 mb-1">
                    No. Telepon
                </label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    value="{{ old('phone', $karyawan->phone) }}"
                    class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                           @error('phone') border-rose-300 focus:border-rose-500 focus:ring-rose-500
                           @else border-slate-200 focus:border-slate-400 focus:ring-slate-400 @enderror"
                    placeholder="Contoh: 0812xxxxxxx"
                >
                @error('phone')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email Payroll (sesuai index) --}}
            <div>
                <label for="payroll_email" class="block text-xs font-medium text-slate-600 mb-1">
                    Email Payroll
                </label>
                <input
                    type="email"
                    id="payroll_email"
                    name="payroll_email"
                    value="{{ old('payroll_email', $karyawan->payroll_email) }}"
                    class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                           @error('payroll_email') border-rose-300 focus:border-rose-500 focus:ring-rose-500
                           @else border-slate-200 focus:border-slate-400 focus:ring-slate-400 @enderror"
                    placeholder="nama@email.com"
                >
                @error('payroll_email')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Lahir (sesuai index) --}}
            <div>
                <label for="date_of_birth" class="block text-xs font-medium text-slate-600 mb-1">
                    Tanggal Lahir
                </label>
                @php
                    $dob = old('date_of_birth');
                    if ($dob === null) {
                        $dob = $karyawan->date_of_birth
                            ? \Carbon\Carbon::parse($karyawan->date_of_birth)->format('Y-m-d')
                            : '';
                    }
                @endphp
                <input
                    type="date"
                    id="date_of_birth"
                    name="date_of_birth"
                    value="{{ $dob }}"
                    class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                           @error('date_of_birth') border-rose-300 focus:border-rose-500 focus:ring-rose-500
                           @else border-slate-200 focus:border-slate-400 focus:ring-slate-400 @enderror"
                >
                @error('date_of_birth')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Masuk (sesuai index) --}}
            <div>
                <label for="date_start" class="block text-xs font-medium text-slate-600 mb-1">
                    Tanggal Masuk
                </label>
                @php
                    $ds = old('date_start');
                    if ($ds === null) {
                        $ds = $karyawan->date_start
                            ? \Carbon\Carbon::parse($karyawan->date_start)->format('Y-m-d')
                            : '';
                    }
                @endphp
                <input
                    type="date"
                    id="date_start"
                    name="date_start"
                    value="{{ $ds }}"
                    class="w-full rounded-lg border px-3 py-2 text-sm shadow-sm
                           @error('date_start') border-rose-300 focus:border-rose-500 focus:ring-rose-500
                           @else border-slate-200 focus:border-slate-400 focus:ring-slate-400 @enderror"
                >
                @error('date_start')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="pt-2 flex flex-col sm:flex-row sm:items-center gap-2">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                >
                    Simpan Perubahan
                </button>

                <a
                    href="{{ route('karyawan.index') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
