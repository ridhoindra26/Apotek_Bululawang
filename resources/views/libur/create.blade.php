@extends('layout.layout')

@section('content')
<div class="container mx-auto max-w-2xl py-4">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Tambah Data Ketidakhadiran</h2>
        <p class="mt-1 text-sm text-slate-500">
            Tambahkan data cuti, libur, atau tidak masuk karyawan.
        </p>
    </div>

    <form method="POST" action="{{ route('libur.store') }}"
        class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        @csrf

        {{-- Jenis --}}
        <div class="mb-4">
            <label for="type" class="block text-sm font-medium text-gray-700">
                Jenis Ketidakhadiran
            </label>

            <select id="type" name="type" required
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] sm:text-sm">

                <option value="cuti" @selected(old('type', 'libur') === 'cuti')>
                    Cuti
                </option>

                <option value="libur" @selected(old('type', 'libur') === 'libur')>
                    Libur
                </option>

                <option value="absent" @selected(old('type', 'libur') === 'absent')>
                    Tidak Masuk
                </option>
            </select>

            @error('type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tanggal --}}
        <div class="mb-4">
            <label for="tanggal" class="block text-sm font-medium text-gray-700">
                Tanggal
            </label>

            <input
                type="date"
                id="tanggal"
                name="tanggal"
                value="{{ old('tanggal') }}"
                required
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] sm:text-sm @error('tanggal') border-red-500 @enderror"
            >

            @error('tanggal')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Karyawan --}}
        <div class="mb-4">
            <label for="karyawan" class="block text-sm font-medium text-gray-700">
                Pilih Karyawan
            </label>

            <select
                id="karyawan"
                name="karyawan"
                required
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] sm:text-sm @error('karyawan') border-red-500 @enderror"
            >
                <option value="" disabled @selected(!old('karyawan'))>
                    Pilih Karyawan
                </option>

                @foreach ($karyawans as $karyawan)
                    <option
                        value="{{ $karyawan->id }}"
                        @selected((string) old('karyawan') === (string) $karyawan->id)
                    >
                        {{ $karyawan->name }}
                    </option>
                @endforeach
            </select>

            @error('karyawan')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Keterangan --}}
        <div class="mb-5">
            <label for="keterangan" class="block text-sm font-medium text-gray-700">
                Keterangan
            </label>

            <input
                type="text"
                id="keterangan"
                name="keterangan"
                value="{{ old('keterangan') }}"
                placeholder="Contoh: Cuti tahunan, izin keluarga, libur nasional"
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] sm:text-sm @error('keterangan') border-red-500 @enderror"
            >

            @error('keterangan')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3">
            <button
                type="submit"
                class="inline-flex items-center rounded-md bg-[#318f8c] px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-[#287673] focus:outline-none focus:ring-2 focus:ring-[#318f8c] focus:ring-offset-2"
            >
                Simpan
            </button>

            <a
                href="{{ route('libur.index') }}"
                class="inline-flex items-center rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
            >
                Batal
            </a>
        </div>
    </form>
</div>
@endsection