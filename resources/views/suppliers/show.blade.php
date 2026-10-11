@extends('layout.layout')

@section('title', 'Detail Supplier')

@section('content')
@php($canDeleteSupplier = auth()->check() && (auth()->user()->role ?? '') === 'superadmin')
<div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('suppliers.index') }}"
               class="text-sm font-medium text-blue-600 hover:text-blue-700">
                ← Kembali ke Supplier
            </a>

            <h1 class="mt-3 text-2xl font-bold text-gray-900">
                {{ $supplier->name }}
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Detail supplier / PBF.
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('suppliers.edit', $supplier) }}"
               class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
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
                    class="inline-flex items-center justify-center rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"
                >
                    Hapus
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">
                    Informasi Supplier
                </h2>

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Nama Supplier
                        </dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $supplier->name }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Kode
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $supplier->code ?: '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Telepon
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $supplier->phone ?: '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Email
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $supplier->email ?: '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Kontak / Sales
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $supplier->contact_person ?: '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Telepon Kontak
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $supplier->contact_person_phone ?: '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            NPWP
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $supplier->npwp ?: '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Status
                        </dt>
                        <dd class="mt-1">
                            @if ($supplier->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                    Nonaktif
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Alamat
                        </dt>
                        <dd class="mt-1 whitespace-pre-line text-sm text-gray-900">
                            {{ $supplier->address ?: '-' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">
                    Ringkasan
                </h2>

                <div class="mt-4 space-y-3">
                    <div class="rounded-lg bg-gray-50 p-4">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Total Penerimaan
                        </div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">
                            {{ $supplier->goodsReceipts()->count() }}
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-4">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Dibuat
                        </div>
                        <div class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $supplier->created_at?->format('d M Y H:i') }}
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-4">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Terakhir Update
                        </div>
                        <div class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $supplier->updated_at?->format('d M Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold text-gray-900">
            Penerimaan Barang Terakhir
        </h2>

        @if ($supplier->goodsReceipts->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">No SP</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Nominal</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @foreach ($supplier->goodsReceipts as $receipt)
                            <tr>
                                <td class="px-4 py-3">
                                    {{ $receipt->received_date?->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $receipt->sp_reference_number ?: '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    Rp {{ number_format($receipt->total_nominal, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $receipt->status_label }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('goods-receipts.show', $receipt) }}"
                                       class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="rounded-lg bg-gray-50 p-5 text-center text-sm text-gray-500">
                Belum ada penerimaan barang untuk supplier ini.
            </div>
        @endif
    </div>
</div>

<script>
    window.supplierFlash = {
        success: @json(session('success')),
        error: @json(session('error')),
    }
</script>
@endsection
