@extends('layout.layout')

@section('content')
<div class="container mx-auto py-4">
    <h2 class="text-2xl font-bold mb-4">Tambah Libur</h2>
    <form method="POST" action="{{ route('libur.store') }}">
        @csrf
        <!-- Tanggal -->
        <div class="mb-4">
            <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" required 
                class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        </div>

        <!-- Karyawan -->
        <div class="mb-4">
            <label for="karyawan" class="block text-sm font-medium text-gray-700">Pilih Karyawan</label>
            <select id="karyawan" name="karyawan" required
                class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="" disabled selected>Pilih Karyawan</option>
                @foreach ($karyawans as $karyawan)
                    <option value="{{ $karyawan->id }}">{{ $karyawan->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Keterangan -->
        <div class="mb-4">
            <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
            <input type="text" id="keterangan" name="keterangan" required 
                class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        </div>

        <!-- Tombol Simpan -->
        <div>
            <button type="submit" 
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection
