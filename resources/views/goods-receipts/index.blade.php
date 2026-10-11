@php
    $isGuest = auth()->guest();
    $layout = $isGuest ? 'layout.guest' : 'layout.layout';
@endphp

@extends($layout)

@section('title', 'Penerimaan Barang')
@section('page_title', 'Penerimaan Barang')


@section('content')
<div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="mb-5 overflow-hidden rounded-2xl bg-white p-5 text-black shadow-lg shadow-teal-900/10 sm:flex sm:items-center sm:justify-between sm:p-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Penerimaan Barang
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Kelola arsip penerimaan barang, faktur, evaluasi, dan approval.
            </p>
        </div>

        <a href="{{ route('goods-receipts.create') }}"
           class="mt-4 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#287c79] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#206a66] sm:mt-0">
            <i class="ri-add-circle-line text-lg"></i> Tambah Penerimaan
        </a>
    </div>

    {{-- Filter --}}
    <details
        x-data="{ expanded: @js(request()->hasAny(['search','supplier_id','branch_id','status','evaluation_status','date_from','date_to'])) }"
        @toggle="expanded = $el.open"
        class="mb-5 rounded-2xl border border-gray-200 bg-white shadow-sm"
        @if(request()->hasAny(['search','supplier_id','branch_id','status','evaluation_status','date_from','date_to'])) open @endif
    >
        <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between px-4 py-3 text-sm font-semibold text-gray-800 [&::-webkit-details-marker]:hidden">
            <span class="flex items-center gap-2"><i class="ri-equalizer-line text-lg text-[#318f8c]"></i> Filter Penerimaan</span>
            <i class="text-xl text-gray-400 transition" :class="expanded ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'"></i>
        </summary>
        <div class="border-t border-gray-100 p-4">
        <form method="GET" action="{{ route('goods-receipts.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-7">
            <div class="md:col-span-2">
                <label for="search" class="mb-1 block text-sm font-medium text-gray-700">
                    Cari
                </label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="No SP, faktur, PBF, pengirim"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                >
            </div>

            <div>
                <label for="supplier_id" class="mb-1 block text-sm font-medium text-gray-700">
                    Supplier
                </label>
                <select
                    id="supplier_id"
                    name="supplier_id"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                >
                    <option value="">Semua</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) request('supplier_id') === (string) $supplier->id)>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-gray-700">
                    Status
                </label>
                <select
                    id="status"
                    name="status"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                >
                    <option value="">Semua</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="waiting_approval" @selected(request('status') === 'waiting_approval')>Menunggu Approval</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
            </div>

            <div>
                <label for="branch_id" class="mb-1 block text-sm font-medium text-gray-700">Cabang</label>
                <select id="branch_id" name="branch_id" class="block min-h-11 w-full rounded-xl border-gray-300 px-3 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c]">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="evaluation_status" class="mb-1 block text-sm font-medium text-gray-700">
                    Evaluasi
                </label>
                <select
                    id="evaluation_status"
                    name="evaluation_status"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                >
                    <option value="">Semua</option>
                    <option value="unchecked" @selected(request('evaluation_status') === 'unchecked')>Belum dicek</option>
                    <option value="matched" @selected(request('evaluation_status') === 'matched')>Sesuai</option>
                    <option value="rejected" @selected(request('evaluation_status') === 'rejected')>Ditolak</option>
                    <option value="partially_rejected" @selected(request('evaluation_status') === 'partially_rejected')>Ditolak sebagian</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800"
                >
                    Filter
                </button>

                <a href="{{ route('goods-receipts.index') }}"
                   class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>

            <div>
                <label for="date_from" class="mb-1 block text-sm font-medium text-gray-700">
                    Dari Tanggal
                </label>
                <input
                    type="date"
                    id="date_from"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                >
            </div>

            <div>
                <label for="date_to" class="mb-1 block text-sm font-medium text-gray-700">
                    Sampai Tanggal
                </label>
                <input
                    type="date"
                    id="date_to"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                >
            </div>
        </form>
        </div>
    </details>

    {{-- Desktop Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="hidden overflow-x-auto lg:block">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Tanggal</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Supplier / SP</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Penerima</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">Faktur</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">Nominal</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Evaluasi</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($goodsReceipts as $receipt)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 align-top">
                                <div class="font-medium text-gray-900">
                                    {{ $receipt->received_date?->format('d M Y') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $receipt->created_at?->format('H:i') }}
                                </div>
                            </td>

                            <td class="px-4 py-3 align-top">
                                <div class="font-semibold text-gray-900">
                                    {{ $receipt->supplier?->name ?? '-' }}
                                </div>
                                <div class="mt-0.5 text-xs text-gray-500">
                                    SP: {{ $receipt->sp_reference_number ?: '-' }}
                                </div>
                                <div class="mt-1 inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                    <i class="ri-store-2-line"></i>
                                    {{ $receipt->branch?->name ?? 'Cabang belum dipilih' }}
                                </div>
                                @if ($receipt->sender_name)
                                    <div class="mt-0.5 text-xs text-gray-500">
                                        Pengirim: {{ $receipt->sender_name }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-3 align-top">
                                {{ $receipt->receiver?->name ?? '-' }}
                            </td>

                            <td class="px-4 py-3 text-right align-top">
                                <span class="font-semibold text-gray-900">
                                    {{ $receipt->invoices_count }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-right align-top">
                                <span class="font-semibold text-gray-900">
                                    Rp {{ number_format($receipt->total_nominal, 0, ',', '.') }}
                                </span>
                            </td>

                            <td class="px-4 py-3 align-top">
                                @include('goods-receipts.partials.evaluation-badge', [
                                    'status' => $receipt->evaluation_status,
                                    'label' => $receipt->evaluation_status_label,
                                ])
                            </td>

                            <td class="px-4 py-3 align-top">
                                @include('goods-receipts.partials.status-badge', [
                                    'status' => $receipt->status,
                                    'label' => $receipt->status_label,
                                ])
                            </td>

                            <td class="px-4 py-3 align-top">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('goods-receipts.show', $receipt) }}"
                                       class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                        Detail
                                    </a>

                                    @if (in_array($receipt->status, ['draft']))
                                        <a href="{{ route('goods-receipts.edit', $receipt) }}"
                                           class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-600">
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-500">
                                Belum ada data penerimaan barang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="divide-y divide-gray-100 lg:hidden">
            @forelse ($goodsReceipts as $receipt)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-semibold text-gray-900">
                                {{ $receipt->supplier?->name ?? '-' }}
                            </div>
                            <div class="mt-0.5 text-xs text-gray-500">
                                {{ $receipt->received_date?->format('d M Y') }} · SP: {{ $receipt->sp_reference_number ?: '-' }}
                            </div>
                            <div class="mt-1 inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                <i class="ri-store-2-line"></i>
                                {{ $receipt->branch?->name ?? 'Cabang belum dipilih' }}
                            </div>
                        </div>

                        @include('goods-receipts.partials.status-badge', [
                            'status' => $receipt->status,
                            'label' => $receipt->status_label,
                        ])
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Faktur
                            </div>
                            <div class="mt-1 font-semibold text-gray-900">
                                {{ $receipt->invoices_count }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Nominal
                            </div>
                            <div class="mt-1 font-semibold text-gray-900">
                                Rp {{ number_format($receipt->total_nominal, 0, ',', '.') }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Penerima
                            </div>
                            <div class="mt-1 font-semibold text-gray-900">
                                {{ $receipt->receiver?->name ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                Evaluasi
                            </div>
                            <div class="mt-1">
                                @include('goods-receipts.partials.evaluation-badge', [
                                    'status' => $receipt->evaluation_status,
                                    'label' => $receipt->evaluation_status_label,
                                ])
                            </div>
                        </div>
                    </div>

                    @if ($receipt->sender_name)
                        <div class="mt-3 text-sm text-gray-600">
                            Pengirim: {{ $receipt->sender_name }}
                        </div>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('goods-receipts.show', $receipt) }}"
                           class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700">
                            Detail
                        </a>

                        @if (in_array($receipt->status, ['draft']))
                            <a href="{{ route('goods-receipts.edit', $receipt) }}"
                               class="rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-600">
                                Edit
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-gray-500">
                    Belum ada data penerimaan barang.
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-5">
        {{ $goodsReceipts->links() }}
    </div>
</div>

<script>
    window.goodsReceiptFlash = {
        success: @json(session('success')),
        error: @json(session('error')),
    }
</script>
@endsection
