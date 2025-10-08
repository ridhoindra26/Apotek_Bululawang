@extends('layout.layout')

@section('content')
<div class="container py-4">

    <!-- Header Halaman -->
    <div class="mb-4">
        <h2 class="text-2xl font-bold">Edit Karyawan</h2>
    </div>

    <!-- Form Edit Karyawan -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('karyawan.update', $karyawan->id) }}" method="POST">
                @csrf
                @method('POST')

                <!-- Nama -->
                <div class="mb-3">
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama Karyawan</label>
                    <input type="text" id="nama" name="name" value="{{ $karyawan->name }}" 
                           class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                           required>
                </div>

                <!-- Cabang -->
                <div class="mb-3">
                    <label for="cabang_id" class="block text-sm font-medium text-gray-700">Cabang</label>
                    <select id="cabang_id" name="id_branch" 
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                            required>
                        <option value="" disabled>Pilih Cabang</option>
                        @foreach ($cabangs as $cabang)
                            <option value="{{ $cabang->id }}" {{ $karyawan->id_branch == $cabang->id ? 'selected' : '' }}>
                                {{ $cabang->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Pasangan -->
                <div class="mb-3">
                    <label for="pasangan_id" class="block text-sm font-medium text-gray-700">Pasangan (Opsional)</label>
                    <select id="pasangan_id" name="id_role" 
                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="" {{ $karyawan->id_role == null ? 'selected' : '' }}>Tidak Ada</option>
                        @foreach ($pasangans as $pasangan)
                            <option value="{{ $pasangan->id }}" {{ $karyawan->id_role == $pasangan->id ? 'selected' : '' }}>
                                {{ $pasangan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tombol Simpan -->
                <div class="mt-4">
                    <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('karyawan.index') }}" class="px-4 py-2 ml-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection