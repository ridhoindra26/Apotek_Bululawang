@extends('layout.layout')

@section('title', 'Import Supplier')
@section('page_title', 'Supplier / PBF')

@section('content')
<div class="mx-auto max-w-3xl px-3 sm:px-6 lg:px-8">
    <div class="mb-5 rounded-2xl bg-white p-5 shadow-lg shadow-teal-900/10 sm:p-6">
        <a href="{{ route('suppliers.index') }}" class="text-sm font-semibold text-[#287c79]">← Kembali ke Supplier</a>
        <h1 class="mt-3 text-2xl font-bold text-gray-900">Import Supplier dari Excel</h1>
        <p class="mt-1 text-sm text-gray-500">Tambahkan banyak supplier sekaligus menggunakan XLSX atau CSV.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="rounded-xl border border-teal-100 bg-teal-50 p-4">
            <h2 class="font-bold text-gray-900">1. Unduh template</h2>
            <p class="mt-1 text-sm text-gray-600">Kolom nama wajib. Kode disarankan karena dipakai untuk mendeteksi supplier yang sudah ada.</p>
            <a href="{{ route('suppliers.import.template') }}" class="mt-3 inline-flex min-h-11 items-center gap-2 rounded-xl border border-[#287c79] px-4 py-2 text-sm font-bold text-[#287c79]">
                <i class="ri-file-excel-2-line text-lg"></i> Download Template
            </a>
        </div>

        <form method="POST" action="{{ route('suppliers.import') }}" enctype="multipart/form-data" class="mt-5 space-y-5">
            @csrf
            <div>
                <label for="file" class="mb-2 block text-sm font-semibold text-gray-700">2. Pilih file Excel <span class="text-red-500">*</span></label>
                <input id="file" name="file" type="file" accept=".xlsx,.csv" required
                    class="block min-h-11 w-full rounded-xl border border-gray-300 bg-gray-50 p-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[#318f8c] file:px-3 file:py-2 file:font-semibold file:text-white">
                <p class="mt-2 text-xs text-gray-500">Maksimal 10 MB dan 2.000 baris per import.</p>
            </div>

            <label class="flex items-start gap-3 rounded-xl border border-gray-200 p-4">
                <input type="checkbox" name="update_existing" value="1" @checked(old('update_existing')) class="mt-1 rounded border-gray-300 text-[#318f8c] focus:ring-[#318f8c]">
                <span><span class="block text-sm font-semibold text-gray-800">Perbarui supplier dengan kode yang sama</span><span class="mt-1 block text-xs text-gray-500">Jika tidak dicentang, baris dengan kode yang sudah terdaftar akan dilewati.</span></span>
            </label>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <a href="{{ route('suppliers.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Batal</a>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#287c79] px-5 py-2 text-sm font-bold text-white hover:bg-[#206a66]">
                    <i class="ri-upload-2-line"></i> Import Supplier
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
