@extends('layout.layout')

@section('title', 'Supplier / PBF')
@section('page_title', 'Supplier / PBF')

@section('content')
@php($canDeleteSupplier = auth()->check() && (auth()->user()->role ?? '') === 'superadmin')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Supplier / PBF
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Kelola data supplier atau PBF untuk penerimaan barang datang.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('suppliers.import.form') }}"
               class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-[#287c79] px-4 py-2 text-sm font-semibold text-[#287c79] hover:bg-teal-50">
                <i class="ri-file-excel-2-line"></i> Import Excel
            </a>
            <a href="{{ route('suppliers.create') }}"
               class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#287c79] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#206a66]">
                <i class="ri-add-circle-line"></i> Tambah Supplier
            </a>
        </div>
    </div>

    @if (session('import_errors'))
        <details class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4" open>
            <summary class="cursor-pointer text-sm font-bold text-amber-900">{{ count(session('import_errors')) }} baris dilewati — lihat detail</summary>
            <ul class="mt-3 max-h-56 list-disc space-y-1 overflow-y-auto pl-5 text-sm text-amber-800">
                @foreach (session('import_errors') as $importError)
                    <li>{{ $importError }}</li>
                @endforeach
            </ul>
        </details>
    @endif

    <div class="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('suppliers.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="search" class="mb-1 block text-sm font-medium text-gray-700">
                    Cari
                </label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Nama, kode, telepon, atau kontak"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-gray-700">
                    Status
                </label>
                <select
                    id="status"
                    name="status"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Semua</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800"
                >
                    Filter
                </button>

                <a href="{{ route('suppliers.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Supplier</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Kontak</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Alamat</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 align-top">
                                <div class="font-semibold text-gray-900">
                                    {{ $supplier->name }}
                                </div>

                                @if ($supplier->code)
                                    <div class="mt-0.5 text-xs text-gray-500">
                                        Kode: {{ $supplier->code }}
                                    </div>
                                @endif

                                @if ($supplier->npwp)
                                    <div class="mt-0.5 text-xs text-gray-500">
                                        NPWP: {{ $supplier->npwp }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-3 align-top">
                                @if ($supplier->contact_person)
                                    <div class="font-medium text-gray-800">
                                        {{ $supplier->contact_person }}
                                    </div>
                                @endif

                                @if ($supplier->phone)
                                    <div class="text-gray-600">
                                        {{ $supplier->phone }}
                                    </div>
                                @endif

                                @if ($supplier->email)
                                    <div class="text-gray-600">
                                        {{ $supplier->email }}
                                    </div>
                                @endif

                                @if (!$supplier->contact_person && !$supplier->phone && !$supplier->email)
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="max-w-xs px-4 py-3 align-top text-gray-600">
                                {{ $supplier->address ?: '-' }}
                            </td>

                            <td class="px-4 py-3 align-top">
                                @if ($supplier->is_active)
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 align-top">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('suppliers.show', $supplier) }}"
                                       class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                        Detail
                                    </a>

                                    <a href="{{ route('suppliers.edit', $supplier) }}"
                                       class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                        Edit
                                    </a>

                                    @if ($canDeleteSupplier)
                                    <form
                                        method="POST"
                                        action="{{ route('suppliers.destroy', $supplier) }}"
                                        data-supplier-delete-form
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                                Belum ada data supplier.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-gray-100 md:hidden">
            @forelse ($suppliers as $supplier)
                <div class="p-4">
                    <div class="mb-2 flex items-start justify-between gap-3">
                        <div>
                            <div class="font-semibold text-gray-900">
                                {{ $supplier->name }}
                            </div>

                            @if ($supplier->code)
                                <div class="text-xs text-gray-500">
                                    Kode: {{ $supplier->code }}
                                </div>
                            @endif
                        </div>

                        @if ($supplier->is_active)
                            <span class="shrink-0 rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                Aktif
                            </span>
                        @else
                            <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                Nonaktif
                            </span>
                        @endif
                    </div>

                    <div class="space-y-1 text-sm text-gray-600">
                        <div>Kontak: {{ $supplier->contact_person ?: '-' }}</div>
                        <div>Telepon: {{ $supplier->phone ?: '-' }}</div>
                        <div>Email: {{ $supplier->email ?: '-' }}</div>
                        <div>Alamat: {{ $supplier->address ?: '-' }}</div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('suppliers.show', $supplier) }}"
                           class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700">
                            Detail
                        </a>

                        <a href="{{ route('suppliers.edit', $supplier) }}"
                           class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-700">
                            Edit
                        </a>

                        @if ($canDeleteSupplier)
                        <form
                            method="POST"
                            action="{{ route('suppliers.destroy', $supplier) }}"
                            data-supplier-delete-form
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700"
                            >
                                Hapus
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-gray-500">
                    Belum ada data supplier.
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-5">
        {{ $suppliers->links() }}
    </div>
</div>

<script>
    window.supplierFlash = {
        success: @json(session('success')),
        error: @json(session('error')),
    }
</script>
@endsection
