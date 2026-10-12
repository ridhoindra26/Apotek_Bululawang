@extends('layout.layout')

@section('title', 'List Faktur TT')
@section('page_title', 'Faktur TT')

@section('content')
<div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="mb-5 overflow-hidden rounded-2xl bg-white p-5 text-black shadow-lg shadow-teal-900/10 sm:flex sm:items-center sm:justify-between sm:p-6">
        <div>
            <h1 class="text-xl font-bold sm:text-2xl">
                Faktur TT Supplier
            </h1>
            <p class="mt-1 max-w-2xl text-sm text-black">
                Monitoring faktur TT, jatuh tempo, potongan, dan status pembayaran supplier.
            </p>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-2 sm:mt-0">
            <a href="{{ route('supplier-tt-payment-batches.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-[#287c79] px-4 py-2.5 text-sm font-bold text-[#287c79]">Batch Bayar</a>
            <a href="{{ route('supplier-tt-invoices.create') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#287c79] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#206a66]"><i class="ri-add-circle-line text-lg"></i> Terima Faktur TT</a>
        </div>
    </div>

    {{-- Flash --}}
    <script>
        window.goodsReceiptFlash = {
            success: @json(session('success')),
            error: @json(session('error')),
            warning: @json(session('warning')),
            validationErrors: @json($errors->all()),
        };
    </script>

    {{-- Summary Cards --}}
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h2 class="text-sm font-bold text-gray-900">Ringkasan · {{ $summaryContext }}</h2>
            <p class="mt-0.5 text-xs text-gray-500">
                {{ $hasActiveFilters ? 'Nilai berikut mengikuti seluruh filter yang sedang aktif.' : 'Nilai berikut mencakup seluruh data Faktur TT.' }}
            </p>
        </div>
        @if ($hasActiveFilters)
            <span class="inline-flex items-center gap-1 rounded-full bg-[#318f8c]/10 px-2.5 py-1 text-xs font-semibold text-[#287c79]">
                <i class="ri-filter-3-line"></i> Filter aktif
            </span>
        @endif
    </div>
    <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50 p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Jatuh Tempo Hari Ini
            </div>
            <div class="mt-2 text-2xl font-bold text-amber-600">
                {{ number_format($summary['due_today'] ?? 0) }}
            </div>
            <div class="mt-1 text-xs text-gray-500">
                Faktur perlu diprioritaskan hari ini.
            </div>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-white to-blue-50 p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Due 7 Hari
            </div>
            <div class="mt-2 text-2xl font-bold text-blue-600">
                {{ number_format($summary['due_soon'] ?? 0) }}
            </div>
            <div class="mt-1 text-xs text-gray-500">
                Faktur yang segera jatuh tempo.
            </div>
        </div>

        <div class="rounded-2xl border border-red-100 bg-gradient-to-br from-white to-red-50 p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Overdue
            </div>
            <div class="mt-2 text-2xl font-bold text-red-600">
                {{ number_format($summary['overdue'] ?? 0) }}
            </div>
            <div class="mt-1 text-xs text-gray-500">
                Faktur melewati jatuh tempo.
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Total Belum Dibayar
            </div>
            <div class="mt-2 text-xl font-bold text-gray-900">
                Rp {{ number_format($summary['unpaid_total'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="mt-1 text-xs text-gray-500">
                Sesuai hasil filter aktif.
            </div>
        </div>

        <div class="rounded-2xl border border-green-100 bg-gradient-to-br from-white to-green-50 p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Total Dibayar
            </div>
            <div class="mt-2 text-xl font-bold text-green-600">
                Rp {{ number_format($summary['paid_total'] ?? 0, 0, ',', '.') }}
            </div>
            <div class="mt-1 text-xs text-gray-500">
                Sesuai hasil filter aktif.
            </div>
        </div>

    </div>

    {{-- Filter --}}
    <details
        x-data="{ expanded: @js(request()->hasAny(['search','supplier_id','branch_id','status','due_status','date_from','date_to'])) }"
        @toggle="expanded = $el.open"
        class="mb-5 rounded-2xl border border-gray-200 bg-white shadow-sm"
        @if(request()->hasAny(['search','supplier_id','branch_id','status','due_status','date_from','date_to'])) open @endif
    >
        <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between px-4 py-3 text-sm font-semibold text-gray-800 [&::-webkit-details-marker]:hidden">
            <span class="flex items-center gap-2"><i class="ri-equalizer-line text-lg text-[#318f8c]"></i> Filter Faktur</span>
            <i
                class="text-xl text-gray-400 transition"
                :class="expanded ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'"
            ></i>
        </summary>
        <div class="border-t border-gray-100 p-4">
        <form method="GET" action="{{ route('supplier-tt-invoices.index') }}">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">
                <div class="xl:col-span-2">
                    <label for="search" class="mb-1 block text-sm font-medium text-gray-700">
                        Search
                    </label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="No faktur, supplier, no SP..."
                        class="block min-h-11 w-full rounded-xl border-gray-300 px-3 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                    >
                </div>

                <div
                    x-data="searchableSelect({
                        options: @js($suppliers->map(fn ($supplier) => ['id' => (string) $supplier->id, 'label' => $supplier->name])->values()),
                        selectedId: @js((string) request('supplier_id', '')),
                        selectedLabel: @js($suppliers->firstWhere('id', request('supplier_id'))?->name ?? ''),
                        placeholder: 'Semua Supplier'
                    })"
                    class="relative"
                    @click.outside="open = false"
                >
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Supplier
                    </label>
                    <input type="hidden" name="supplier_id" :value="selectedId">
                    <button type="button" @click="open = !open" class="flex min-h-11 w-full items-center justify-between rounded-xl border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c]">
                        <span class="truncate" :class="selectedLabel ? 'text-gray-900' : 'text-gray-400'" x-text="selectedLabel || placeholder"></span>
                        <i class="text-lg text-gray-500" :class="open ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'"></i>
                    </button>
                    <div x-show="open" x-cloak class="absolute z-50 mt-2 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl">
                        <div class="border-b border-gray-100 p-2">
                            <input x-ref="searchInput" x-model="search" @keydown.enter.prevent type="text" placeholder="Cari supplier..." class="block w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-[#318f8c] focus:ring-[#318f8c]">
                        </div>
                        <div class="max-h-60 overflow-y-auto p-1">
                            <template x-if="filteredOptions.length === 0"><div class="px-3 py-3 text-sm text-gray-500">Supplier tidak ditemukan.</div></template>
                            <template x-for="option in filteredOptions" :key="option.id">
                                <button type="button" @click="choose(option)" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm hover:bg-teal-50" :class="String(option.id) === selectedId ? 'bg-teal-50 text-[#287c79]' : 'text-gray-700'">
                                    <span x-text="option.label"></span><i x-show="String(option.id) === selectedId" class="ri-check-line text-lg"></i>
                                </button>
                            </template>
                        </div>
                        <button x-show="selectedId" type="button" @click="clear" class="w-full border-t border-gray-100 px-3 py-2 text-left text-xs font-semibold text-red-600 hover:bg-red-50">Hapus pilihan</button>
                    </div>
                </div>

                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-gray-700">
                        Status
                    </label>
                    <select
                        id="status"
                        name="status"
                        class="block min-h-11 w-full rounded-xl border-gray-300 px-3 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                    >
                        <option value="">Semua Status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="verified" @selected(request('status') === 'verified')>Terverifikasi</option>
                        <option value="scheduled_payment" @selected(request('status') === 'scheduled_payment')>Dijadwalkan Bayar</option>
                        <option value="paid" @selected(request('status') === 'paid')>Dibayar</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
                    </select>
                </div>

                <div>
                    <label for="branch_id" class="mb-1 block text-sm font-medium text-gray-700">
                        Cabang
                    </label>
                    <select
                        id="branch_id"
                        name="branch_id"
                        class="block min-h-11 w-full rounded-xl border-gray-300 px-3 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                    >
                        <option value="">Semua Cabang</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="due_status" class="mb-1 block text-sm font-medium text-gray-700">
                        Due Status
                    </label>
                    <select
                        id="due_status"
                        name="due_status"
                        class="block min-h-11 w-full rounded-xl border-gray-300 px-3 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                    >
                        <option value="">Semua Due</option>
                        <option value="due_today" @selected(request('due_status') === 'due_today')>Jatuh Tempo Hari Ini</option>
                        <option value="due_soon" @selected(request('due_status') === 'due_soon')>Due 7 Hari</option>
                        <option value="overdue" @selected(request('due_status') === 'overdue')>Overdue</option>
                    </select>
                </div>

                <div>
                    <label for="date_from" class="mb-1 block text-sm font-medium text-gray-700">
                        Due Dari
                    </label>
                    <input
                        type="date"
                        id="date_from"
                        name="date_from"
                        value="{{ request('date_from') }}"
                        class="block min-h-11 w-full rounded-xl border-gray-300 px-3 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                    >
                </div>

                <div>
                    <label for="date_to" class="mb-1 block text-sm font-medium text-gray-700">
                        Due Sampai
                    </label>
                    <input
                        type="date"
                        id="date_to"
                        name="date_to"
                        value="{{ request('date_to') }}"
                        class="block min-h-11 w-full rounded-xl border-gray-300 px-3 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                    >
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('supplier-tt-invoices.index') }}"
                   class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:flex-none">
                    Reset
                </a>

                <button
                    type="submit"
                    class="inline-flex min-h-11 flex-1 items-center justify-center rounded-xl bg-[#287c79] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#206a66] sm:flex-none"
                >
                    Filter
                </button>
            </div>
        </form>
        </div>
    </details>

    {{-- Desktop Table --}}
    <div class="hidden overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm lg:block">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Faktur TT
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Supplier / Goods Receipt
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Nominal
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Due Date
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Status
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Aksi
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($supplierTtInvoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 align-top">
                            <div class="font-semibold text-gray-900">
                                {{ $invoice->invoice_number }}
                            </div>

                            <div class="mt-1 text-xs text-gray-500">
                                Tgl Faktur:
                                {{ $invoice->invoice_date?->format('d M Y') ?? '-' }}
                            </div>

                            <div class="mt-1 text-xs text-gray-500">
                                Diterima:
                                {{ $invoice->received_date?->format('d M Y') ?? '-' }}
                            </div>

                            <div class="mt-2 text-xs text-gray-500">
                                {{ $invoice->photos_count }} halaman foto
                            </div>
                        </td>

                        <td class="px-4 py-4 align-top">
                            <div class="font-semibold text-gray-900">
                                {{ $invoice->supplier?->name ?? '-' }}
                            </div>

                            <div class="mt-1 inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                <i class="ri-store-2-line"></i>
                                {{ $invoice->branch?->name ?? 'Cabang belum dipilih' }}
                            </div>

                            <div class="mt-1 text-xs text-gray-500">
                                {{ $invoice->goods_receipt_id ? 'GR ID: #'.$invoice->goods_receipt_id : 'Tanpa Goods Receipt' }}
                            </div>

                            <div class="mt-1 text-xs text-gray-500">
                                SP:
                                {{ $invoice->goodsReceipt?->sp_reference_number ?: '-' }}
                            </div>
                        </td>

                        <td class="px-4 py-4 text-right align-top">
                            <div class="text-sm text-gray-500">
                                Bruto
                            </div>
                            <div class="font-semibold text-gray-900">
                                Rp {{ number_format($invoice->gross_nominal, 0, ',', '.') }}
                            </div>

                            @if ((float) $invoice->deduction_total > 0)
                                <div class="mt-2 text-sm text-red-500">
                                    - Rp {{ number_format($invoice->deduction_total, 0, ',', '.') }}
                                </div>
                            @endif

                            <div class="mt-2 text-sm font-bold text-[#318f8c]">
                                Rp {{ number_format($invoice->net_nominal, 0, ',', '.') }}
                            </div>
                        </td>

                        <td class="px-4 py-4 align-top">
                            <div class="font-semibold text-gray-900">
                                {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                            </div>

                            @include('supplier-tt-invoices.partials.due-badge', [
                                'status' => $invoice->due_status,
                                'label' => $invoice->due_status_label,
                            ])
                        </td>

                        <td class="px-4 py-4 align-top">
                            @include('supplier-tt-invoices.partials.status-badge', [
                                'status' => $invoice->status,
                                'label' => $invoice->status_label,
                            ])

                            @if ($invoice->verification_note)
                                <div class="mt-2 line-clamp-2 max-w-xs text-xs text-gray-500">
                                    {{ $invoice->verification_note }}
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-4 align-top">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('supplier-tt-invoices.show', $invoice) }}"
                                   class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                    Detail
                                </a>

                                @if ($invoice->isEditable())
                                    <a href="{{ route('supplier-tt-invoices.edit', $invoice) }}"
                                       class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                        Edit
                                    </a>
                                @elseif ($invoice->status === 'scheduled_payment' && ($invoice->paymentBatch->first()?->status !== 'locked' || auth()->user()?->hasRole('superadmin')))
                                    <form method="POST" action="{{ route('supplier-tt-invoices.prepare-edit', $invoice) }}" data-supplier-tt-action-form data-confirm-title="Keluarkan faktur dari batch?" data-confirm-text="Faktur kembali menjadi Terverifikasi sebelum form edit dibuka." data-confirm-button="Ya, keluarkan & edit">
                                        @csrf
                                        <button class="rounded-lg border border-blue-200 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">Keluarkan & Edit</button>
                                    </form>
                                @endif

                                @if (auth()->user()?->hasRole('superadmin') && $invoice->isDeletable())
                                    <form
                                        method="POST"
                                        action="{{ route('supplier-tt-invoices.destroy', $invoice) }}"
                                        data-supplier-tt-delete-form
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
                        <td colspan="6" class="px-4 py-10 text-center">
                            <div class="text-sm font-semibold text-gray-700">
                                Belum ada Faktur TT.
                            </div>
                            <div class="mt-1 text-sm text-gray-500">
                                Klik tombol Terima Faktur TT untuk mencatat faktur pertama.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Cards --}}
    <div class="space-y-4 lg:hidden">
        @forelse ($supplierTtInvoices as $invoice)
            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="h-1 bg-gradient-to-r from-[#318f8c] to-teal-300"></div>
                <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="truncate text-base font-bold text-gray-900">
                            {{ $invoice->invoice_number }}
                        </div>
                        <div class="mt-1 text-sm text-gray-500">
                            {{ $invoice->supplier?->name ?? '-' }}
                        </div>
                        <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                            <i class="ri-store-2-line"></i>
                            {{ $invoice->branch?->name ?? 'Cabang belum dipilih' }}
                        </div>
                    </div>

                    @include('supplier-tt-invoices.partials.status-badge', [
                        'status' => $invoice->status,
                        'label' => $invoice->status_label,
                    ])
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                    <div class="rounded-xl bg-gray-50 p-3">
                        <div class="text-xs text-gray-500">Goods Receipt</div>
                        <div class="font-semibold text-gray-900">
                            {{ $invoice->goods_receipt_id ? '#'.$invoice->goods_receipt_id : 'Tidak dipilih' }}
                        </div>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-3">
                        <div class="text-xs text-gray-500">Due Date</div>
                        <div class="font-semibold text-gray-900">
                            {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                        </div>
                    </div>

                    <div class="rounded-xl bg-teal-50 p-3">
                        <div class="text-xs text-gray-500">Net Nominal</div>
                        <div class="font-semibold text-[#318f8c]">
                            Rp {{ number_format($invoice->net_nominal, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="rounded-xl bg-gray-50 p-3">
                        <div class="text-xs text-gray-500">Due Status</div>
                        <div class="mt-1">
                            @include('supplier-tt-invoices.partials.due-badge', [
                                'status' => $invoice->due_status,
                                'label' => $invoice->due_status_label,
                            ])
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2 border-t border-gray-100 pt-4">
                    <a href="{{ route('supplier-tt-invoices.show', $invoice) }}"
                       class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-xl bg-[#318f8c] px-3 py-2 text-xs font-semibold text-white hover:bg-[#287c79]">
                        <i class="ri-eye-line text-base"></i> Detail
                    </a>

                    @if ($invoice->isEditable())
                        <a href="{{ route('supplier-tt-invoices.edit', $invoice) }}"
                       class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-xl border border-blue-200 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                            <i class="ri-edit-line text-base"></i> Edit
                        </a>
                    @elseif ($invoice->status === 'scheduled_payment' && ($invoice->paymentBatch->first()?->status !== 'locked' || auth()->user()?->hasRole('superadmin')))
                        <form method="POST" action="{{ route('supplier-tt-invoices.prepare-edit', $invoice) }}" data-supplier-tt-action-form data-confirm-title="Keluarkan faktur dari batch?" data-confirm-text="Faktur kembali menjadi Terverifikasi sebelum form edit dibuka." data-confirm-button="Ya, keluarkan & edit">
                            @csrf
                            <button class="inline-flex min-h-10 w-full items-center justify-center gap-1.5 rounded-xl border border-blue-200 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50"><i class="ri-edit-line text-base"></i> Keluarkan & Edit</button>
                        </form>
                    @endif

                    @if (auth()->user()?->hasRole('superadmin') && $invoice->isDeletable())
                        <form class="col-span-2"
                            method="POST"
                            action="{{ route('supplier-tt-invoices.destroy', $invoice) }}"
                            data-supplier-tt-delete-form
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                            class="inline-flex min-h-10 w-full items-center justify-center gap-1.5 rounded-xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50"
                            >
                            <i class="ri-delete-bin-line text-base"></i> Hapus
                            </button>
                        </form>
                    @endif
                </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm">
                <div class="text-sm font-semibold text-gray-700">
                    Belum ada Faktur TT.
                </div>
                <div class="mt-1 text-sm text-gray-500">
                    Klik tombol Terima Faktur TT untuk mencatat faktur pertama.
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $supplierTtInvoices->links() }}
    </div>
</div>
@endsection
