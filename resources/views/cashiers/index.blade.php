@extends('layout.layout')

@section('title', 'Upload Dokumen Kasir')
@section('page_title', 'Upload Dokumen Kasir')

@section('content')

@php
    $currentShift = old('shift', $defaultShift ?? 'Pagi');
    $currentBranch = old('branch', $defaultBranch ?? 0);
@endphp

<div class="space-y-4">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-emerald-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-rose-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Header + tombol ke halaman list --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">Form Upload Dokumen Kasir</h2>
            <p class="text-sm text-slate-500">
                Upload foto kertas tutup kasir, bukti setoran, bukti cek, dan kas kecil. Foto muka kasir gaperluu.
            </p>
        </div>

        {{-- Tombol ke halaman list --}}
        <a href="{{ route('cashier.list') }}" 
           class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
            Lihat Dokumen Terupload
        </a>
    </div>

    {{-- FORM UPLOAD --}}
    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
        <form method="POST"
              action="{{ route('cashier.store') }}"
              enctype="multipart/form-data"
              class="space-y-5">
            @csrf

            {{-- Bagian: Info dasar --}}
            <div class="grid gap-4 sm:grid-cols-2">
                {{-- Tanggal --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal</label>
                    <input type="date"
                           name="date"
                           value="{{ old('date', now()->toDateString()) }}"
                           class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                           required>
                    @error('date')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Cabang --}}
                <div class="col-span-1">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cabang</label>
                    <select name="branch" required
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                        <option value="" {{ $currentBranch === 0 ? 'selected' : '' }}>Pilih Cabang</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $currentBranch === $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Shift --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Shift</label>

                    <div class="flex w-full gap-2">
                        {{-- Pagi --}}
                        <label class="flex-1 cursor-pointer">
                            <input
                                type="radio"
                                name="shift"
                                value="Pagi"
                                class="peer sr-only"
                                {{ $currentShift === 'Pagi' ? 'checked' : '' }}
                            >
                            <div
                                class="w-full rounded-xl border px-3 py-2 text-center text-sm font-medium shadow-sm
                                    border-slate-200 bg-white text-slate-600
                                    peer-checked:border-emerald-400 peer-checked:!bg-emerald-50 peer-checked:text-emerald-700">
                                Pagi
                            </div>
                        </label>

                        {{-- Siang --}}
                        <label class="flex-1 cursor-pointer">
                            <input
                                type="radio"
                                name="shift"
                                value="Siang"
                                class="peer sr-only"
                                {{ $currentShift === 'Siang' ? 'checked' : '' }}
                            >
                            <div
                                class="w-full rounded-xl border px-3 py-2 text-center text-sm font-medium shadow-sm
                                    border-slate-200 bg-white text-slate-600
                                    peer-checked:border-emerald-400 peer-checked:!bg-emerald-50 peer-checked:text-emerald-700">
                                Siang
                            </div>
                        </label>
                    </div>

                    @error('shift')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-span-1">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan (Opsional)</label>
                    <textarea name="description"
                              rows="2"
                              class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                              placeholder="Contoh: ibuk hutang 1jt setoran">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- Bagian: Upload foto per jenis dokumen --}}
            <div class="grid gap-4 md:grid-cols-2">

                {{-- 1. Kertas Tutup Kasir (WAJIB) --}}
                <div x-data="{ preview: null }"
                    class="rounded-2xl border border-emerald-200 bg-white/90 p-4 shadow-sm">
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <div>
                            <p class="text-xs !font-semibold text-slate-800 mb-0">
                                Foto Kertas Tutup Kasir
                            </p>
                            <p class="text-xs text-slate-500">
                                Wajib diisi setiap tutup shift.
                            </p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-600 border border-rose-100">
                            WAJIB
                        </span>
                    </div>

                    <input type="file"
                        name="closing_cash_photo"
                        accept="image/*"
                        class="w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-emerald-700 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                        required
                        @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">

                    <p class="mt-2 text-xs text-slate-400">
                        Maks 5MB. Format: JPG, PNG, dll.
                    </p>
                    @error('closing_cash_photo')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror

                    {{-- PREVIEW --}}
                    <template x-if="preview">
                        <div class="mt-3">
                            <p class="text-sm text-slate-500 mb-1">Preview:</p>
                            <img :src="preview"
                                alt="Preview kertas tutup kasir"
                                class="h-32 w-full max-w-xs rounded-xl border border-slate-200 object-cover">
                        </div>
                    </template>
                </div>

                {{-- 2. Bukti Setoran --}}
                <div x-data="{ preview: null }"
                    class="rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm">
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <div>
                            <p class="text-xs !font-semibold text-slate-800 mb-0">
                                Foto Bukti Setoran
                            </p>
                            <p class="text-xs text-slate-500">
                                Isi jika ada setoran ke bank.
                            </p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 border border-slate-200">
                            OPSIONAL
                        </span>
                    </div>

                    <input type="file"
                        name="deposit_slip_photo"
                        accept="image/*"
                        class="w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-700 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-slate-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                        @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">

                    @error('deposit_slip_photo')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror

                    {{-- PREVIEW --}}
                    <template x-if="preview">
                        <div class="mt-3">
                            <p class="text-sm text-slate-500 mb-1">Preview:</p>
                            <img :src="preview"
                                alt="Preview bukti setoran"
                                class="h-32 w-full max-w-xs rounded-xl border border-slate-200 object-cover">
                        </div>
                    </template>
                </div>

                {{-- 3. Bukti Cek Darah (MULTI) --}}
                <div x-data="multiUpload({ name: 'blood_check_photo', required: false })"
                    class="rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm">
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <div>
                            <p class="text-xs !font-semibold text-slate-800 mb-0">
                                Foto Bukti Cek Darah
                            </p>
                            <p class="text-xs text-slate-500">
                                Bisa upload lebih dari satu foto jika ada beberapa halaman.
                            </p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 border border-slate-200">
                            OPSIONAL
                        </span>
                    </div>

                    <template x-for="(field, index) in fields" :key="field.id">
                        <div class="mb-2 flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2">
                            <div class="flex-1">
                                <input
                                    type="file"
                                    :name="name + '[]'"  {{-- blood_check_photo[] --}}
                                    accept="image/*"
                                    class="w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700
                                        file:mr-3 file:rounded-lg file:border-0 file:bg-slate-700 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white
                                        hover:file:bg-slate-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                                    @change="onFileChange($event, index)"
                                >
                            </div>

                            <div class="flex flex-col items-center gap-1">
                                <template x-if="field.preview">
                                    <img
                                        :src="field.preview"
                                        alt="Preview bukti cek darah"
                                        class="h-14 w-14 rounded-lg border border-slate-200 object-cover"
                                    >
                                </template>

                                <button
                                    type="button"
                                    class="text-[10px] text-rose-500 hover:text-rose-600"
                                    @click="removeField(index)"
                                    x-show="fields.length > 1"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>

                    <button
                        type="button"
                        class="mt-1 inline-flex items-center rounded-full border border-dashed border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50"
                        @click="addField()"
                    >
                        + Tambah Foto Cek Darah
                    </button>

                    @error('blood_check_photo')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 4. Foto Kas Kecil (MULTI) --}}
                <div x-data="multiUpload({ name: 'petty_cash_photo', required: false })"
                    class="rounded-2xl border border-slate-200 bg-white/90 p-4 shadow-sm">
                    <div class="mb-2 flex items-start justify-between gap-2">
                        <div>
                            <p class="text-xs !font-semibold text-slate-800 mb-0">
                                Foto Kas Kecil
                            </p>
                            <p class="text-xs text-slate-500">
                                Bisa upload lebih dari satu foto jika ada beberapa halaman.
                            </p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 border border-slate-200">
                            OPSIONAL
                        </span>
                    </div>

                    <template x-for="(field, index) in fields" :key="field.id">
                        <div class="mb-2 flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2">
                            <div class="flex-1">
                                <input
                                    type="file"
                                    :name="name + '[]'"  {{-- petty_cash_photo[] --}}
                                    accept="image/*"
                                    class="w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700
                                        file:mr-3 file:rounded-lg file:border-0 file:bg-slate-700 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white
                                        hover:file:bg-slate-900 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                                    @change="onFileChange($event, index)"
                                >
                            </div>

                            <div class="flex flex-col items-center gap-1">
                                <template x-if="field.preview">
                                    <img
                                        :src="field.preview"
                                        alt="Preview kas kecil"
                                        class="h-14 w-14 rounded-lg border border-slate-200 object-cover"
                                    >
                                </template>

                                <button
                                    type="button"
                                    class="text-sm text-rose-500 hover:text-rose-600"
                                    @click="removeField(index)"
                                    x-show="fields.length > 1"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </template>

                    <button
                        type="button"
                        class="mt-1 inline-flex items-center rounded-full border border-dashed border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50"
                        @click="addField()"
                    >
                        + Tambah Foto Kas Kecil
                    </button>

                    @error('petty_cash_photo')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

            {{-- Tombol Submit --}}
            <div class="flex justify-end gap-2 pt-2">
                <button type="submit"
                        class="rounded bg-emerald-600 px-5 py-1.5 text-md font-semibold text-white shadow-sm hover:bg-emerald-700">
                    Simpan Dokumen
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
