@php
    $isGuest = auth()->guest();
    $layout = $isGuest ? 'layout.guest' : 'layout.layout';

    $supplierOptions = $suppliers
        ->map(fn ($supplier) => [
            'id' => $supplier->id,
            'label' => $supplier->name,
        ])
        ->values();

    $employeeOptions = $employees
        ->map(fn ($employee) => [
            'id' => $employee->id,
            'label' => $employee->name,
        ])
        ->values();

    $selectedSupplier = $suppliers->firstWhere(
        'id',
        old('supplier_id', $goodsReceipt->supplier_id ?? null)
    );

    $selectedReceiver = $employees->firstWhere(
        'id',
        old('received_by', $goodsReceipt->received_by ?? null)
    );
@endphp

@extends($layout)

@section('title', 'Tambah Penerimaan Barang')
@section('page_title', 'Tambah Penerimaan Barang')

@section('content')
<div
    x-data="goodsReceiptWizard()"
    class="mx-auto max-w-7xl px-4 pb-28 sm:px-6 sm:pb-6 lg:px-8"
>
    {{-- Header --}}
    <div class="mb-3 rounded-xl border border-gray-200 bg-white p-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between shadow-sm">
        <div>
            <a href="{{ route('goods-receipts.index') }}"
               class="text-sm font-medium text-blue-600 hover:text-blue-700">
                ← Kembali ke Penerimaan Barang
            </a>

            <h1 class="mt-3 text-2xl font-bold text-gray-900">
                Tambah Penerimaan Barang
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Catat penerimaan barang datang, dokumentasi faktur, evaluasi, dan persetujuan.
            </p>
        </div>
    </div>

    {{-- Stepper --}}
    <div class="mb-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 shrink-0 rounded-full p-[6px] transition-all duration-500 ease-out"
                 :style="{ background: `conic-gradient(#318f8c ${stepProgress}, #e5e7eb 0)` }"
                 role="progressbar" :aria-valuenow="currentStep" aria-valuemin="1" :aria-valuemax="totalSteps">
                <div class="flex h-full w-full items-center justify-center rounded-full bg-white text-xs font-bold text-gray-900">
                    <span x-text="currentStep"></span><span class="mx-0.5 font-medium text-gray-400">of</span><span x-text="totalSteps"></span>
                </div>
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="truncate text-base font-bold text-gray-900 sm:text-lg" x-text="currentStepInfo.title"></h2>
                <p class="mt-1 truncate text-xs font-medium text-gray-400 sm:text-sm" :class="!nextStepInfo && 'text-green-600'"
                   x-text="nextStepInfo ? `Next: ${nextStepInfo.title}` : 'Ready to save'"></p>
            </div>
        </div>
        <div class="hidden">
            <template x-for="step in steps" :key="step.number">
                <button
                    type="button"
                    class="flex items-start gap-3 rounded-xl border p-4 text-left transition"
                    :class="{
                        'border-blue-500 bg-blue-50': isStepActive(step.number),
                        'border-green-500 bg-green-50': isStepCompleted(step.number),
                        'border-gray-200 bg-white hover:bg-gray-50': !isStepActive(step.number) && !isStepCompleted(step.number),
                    }"
                    @click="goToStep(step.number)"
                >
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold"
                        :class="{
                            'bg-blue-600 text-white': isStepActive(step.number),
                            'bg-green-600 text-white': isStepCompleted(step.number),
                            'bg-gray-100 text-gray-600': !isStepActive(step.number) && !isStepCompleted(step.number),
                        }"
                    >
                        <span x-show="!isStepCompleted(step.number)" x-text="step.number"></span>
                        <span x-show="isStepCompleted(step.number)">✓</span>
                    </div>

                    <div>
                        <div
                            class="text-sm font-semibold"
                            :class="{
                                'text-blue-900': isStepActive(step.number),
                                'text-green-900': isStepCompleted(step.number),
                                'text-gray-900': !isStepActive(step.number) && !isStepCompleted(step.number),
                            }"
                            x-text="step.title"
                        ></div>

                        <div
                            class="mt-1 text-xs"
                            :class="{
                                'text-blue-700': isStepActive(step.number),
                                'text-green-700': isStepCompleted(step.number),
                                'text-gray-500': !isStepActive(step.number) && !isStepCompleted(step.number),
                            }"
                            x-text="step.description"
                        ></div>
                    </div>
                </button>
            </template>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('goods-receipts.store') }}"
        enctype="multipart/form-data"
        data-goods-receipt-form
    >
        @csrf

        <input type="hidden" name="submit_action" value="draft">

        {{-- Step Content --}}
        <div class="goods-receipt-fields rounded-xl border border-gray-200 bg-white p-4 shadow-sm [&_label]:mb-2 [&_input]:min-h-11 [&_input]:rounded-xl [&_input]:border-gray-200 [&_input]:bg-gray-50/60 [&_input:focus]:border-[#318f8c] [&_input:focus]:ring-4 [&_input:focus]:ring-[#318f8c]/10 [&_select]:min-h-11 [&_select]:rounded-xl [&_select]:border-gray-200 [&_select]:bg-gray-50/60 [&_select:focus]:border-[#318f8c] [&_select:focus]:ring-4 [&_select:focus]:ring-[#318f8c]/10 [&_textarea]:rounded-xl [&_textarea]:border-gray-200 [&_textarea]:bg-gray-50/60">

            {{-- Step 1 --}}
            <section x-show="currentStep === 1" x-cloak>
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Informasi Penerimaan
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Isi data utama penerimaan barang datang.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700">Cabang <span class="text-red-500">*</span></label>
                        <select id="branch_id" name="branch_id" required>
                            <option value="">Pilih cabang</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((string) old('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="received_date" class="mb-1 block text-sm font-medium text-gray-700">
                            Tanggal Barang Datang <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            id="received_date"
                            name="received_date"
                            value="{{ old('received_date', now()->format('Y-m-d')) }}"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                            required
                        >
                    </div>

                    <div
                        x-data="searchableSelect({
                            options: @js($supplierOptions),
                            selectedId: @js((string) old('supplier_id', $goodsReceipt->supplier_id ?? '')),
                            selectedLabel: @js($selectedSupplier?->name ?? ''),
                            placeholder: 'Cari atau pilih supplier'
                        })"
                        class="relative"
                        @click.outside="open = false"
                    >
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Supplier / PBF <span class="text-red-500">*</span>
                        </label>

                        <input type="hidden" name="supplier_id" :value="selectedId">

                        <button
                            type="button"
                            @click="open = !open"
                            class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            :class="{ 'border-red-500': !selectedId && false }"
                        >
                            <span
                                class="truncate"
                                :class="selectedLabel ? 'text-gray-900' : 'text-gray-400'"
                                x-text="selectedLabel || placeholder"
                            ></span>

                            <i class="ri-arrow-down-s-line text-lg text-gray-500"></i>
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            class="absolute z-50 mt-2 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl"
                        >
                            <div class="border-b border-gray-100 p-2">
                                <input
                                    x-ref="searchInput"
                                    x-model="search"
                                    type="text"
                                    placeholder="Cari supplier..."
                                    class="block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                >
                            </div>

                            <div class="max-h-60 overflow-y-auto p-1">
                                <template x-if="filteredOptions.length === 0">
                                    <div class="px-3 py-3 text-sm text-gray-500">
                                        Supplier tidak ditemukan.
                                    </div>
                                </template>

                                <template x-for="option in filteredOptions" :key="option.id">
                                    <button
                                        type="button"
                                        @click="choose(option)"
                                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm hover:bg-blue-50"
                                        :class="String(option.id) === selectedId ? 'bg-blue-50 text-blue-700' : 'text-gray-700'"
                                    >
                                        <span x-text="option.label"></span>

                                        <i
                                            x-show="String(option.id) === selectedId"
                                            class="ri-check-line text-lg"
                                        ></i>
                                    </button>
                                </template>
                            </div>

                            <button
                                x-show="selectedId"
                                type="button"
                                @click="clear"
                                class="w-full border-t border-gray-100 px-3 py-2 text-left text-xs font-semibold text-red-600 hover:bg-red-50"
                            >
                                Hapus pilihan
                            </button>
                        </div>

                        @error('supplier_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sp_reference_number" class="mb-1 block text-sm font-medium text-gray-700">
                            No Referensi SP <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="sp_reference_number"
                            name="sp_reference_number"
                            value="{{ old('sp_reference_number') }}"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                            placeholder="Contoh: 230902/1"
                            required
                        >
                    </div>

                    <div
                        x-data="searchableSelect({
                            options: @js($employeeOptions),
                            selectedId: @js((string) old('received_by', $goodsReceipt->received_by ?? '')),
                            selectedLabel: @js($selectedReceiver?->name ?? ''),
                            placeholder: 'Cari atau pilih penerima'
                        })"
                        class="relative"
                        @click.outside="open = false"
                    >
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Nama Penerima <span class="text-red-500">*</span>
                        </label>

                        <input type="hidden" name="received_by" :value="selectedId">

                        <button
                            type="button"
                            @click="open = !open"
                            class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <span
                                class="truncate"
                                :class="selectedLabel ? 'text-gray-900' : 'text-gray-400'"
                                x-text="selectedLabel || placeholder"
                            ></span>

                            <i class="ri-arrow-down-s-line text-lg text-gray-500"></i>
                        </button>

                        <div
                            x-show="open"
                            x-cloak
                            class="absolute z-50 mt-2 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl"
                        >
                            <div class="border-b border-gray-100 p-2">
                                <input
                                    x-ref="searchInput"
                                    x-model="search"
                                    type="text"
                                    placeholder="Cari nama penerima..."
                                    class="block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                >
                            </div>

                            <div class="max-h-60 overflow-y-auto p-1">
                                <template x-if="filteredOptions.length === 0">
                                    <div class="px-3 py-3 text-sm text-gray-500">
                                        Penerima tidak ditemukan.
                                    </div>
                                </template>

                                <template x-for="option in filteredOptions" :key="option.id">
                                    <button
                                        type="button"
                                        @click="choose(option)"
                                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm hover:bg-blue-50"
                                        :class="String(option.id) === selectedId ? 'bg-blue-50 text-blue-700' : 'text-gray-700'"
                                    >
                                        <span x-text="option.label"></span>

                                        <i
                                            x-show="String(option.id) === selectedId"
                                            class="ri-check-line text-lg"
                                        ></i>
                                    </button>
                                </template>
                            </div>

                            <button
                                x-show="selectedId"
                                type="button"
                                @click="clear"
                                class="w-full border-t border-gray-100 px-3 py-2 text-left text-xs font-semibold text-red-600 hover:bg-red-50"
                            >
                                Hapus pilihan
                            </button>
                        </div>

                        @error('received_by')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sender_name" class="mb-1 block text-sm font-medium text-gray-700">
                            Nama Pengirim / Kurir <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="sender_name"
                            name="sender_name"
                            value="{{ old('sender_name') }}"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                            placeholder="Nama pengirim barang"
                            required
                        >
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="mb-1 block text-sm font-medium text-gray-700">
                            Keterangan Umum
                        </label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                            placeholder="Catatan umum penerimaan barang"
                        >{{ old('notes') }}</textarea>
                    </div>
                </div>
            </section>

            {{-- Step 2 --}}
            <section x-show="currentStep === 2" x-cloak>
                <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Dokumentasi Faktur
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Tambahkan data faktur, nominal, dan foto halaman faktur.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="addInvoice"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700"
                    >
                        + Tambah Faktur
                    </button>
                </div>

                {{-- Summary --}}
                <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Jumlah Faktur
                        </div>
                        <div class="mt-1 text-2xl font-bold text-gray-900" x-text="invoices.length"></div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Total Halaman Foto
                        </div>
                        <div class="mt-1 text-2xl font-bold text-gray-900" x-text="totalPages"></div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Total Nominal
                        </div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">
                            Rp <span x-text="formattedTotalNominal"></span>
                        </div>
                    </div>
                </div>

                <div
                    x-show="isCompressing"
                    x-cloak
                    class="mb-5 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800"
                >
                    <div class="font-semibold">
                        Sedang mengompres foto...
                    </div>
                    <div class="mt-1">
                        Mohon tunggu sampai proses selesai sebelum lanjut atau submit.
                    </div>
                </div>

                {{-- Invoice Cards --}}
                <div class="space-y-5">
                    <template x-for="(invoice, invoiceIndex) in invoices" :key="invoice.id">
                        <div
                            data-invoice-card
                            class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
                        >
                            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">
                                        Faktur <span x-text="invoiceIndex + 1"></span>
                                    </h3>
                                    <p class="mt-0.5 text-xs text-gray-500">
                                        Isi detail faktur dan upload seluruh halaman faktur.
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    @click="removeInvoice(invoiceIndex)"
                                    class="inline-flex items-center justify-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                >
                                    Hapus Faktur
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        5 Digit Terakhir No Faktur <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        :name="`invoices[${invoiceIndex}][invoice_number]`"
                                        x-model="invoice.invoice_number"
                                        @input="invoice.invoice_number = String(invoice.invoice_number || '').replace(/\D/g, '').slice(0, 5)"
                                        inputmode="numeric"
                                        maxlength="5"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                        placeholder="Contoh: 12345"
                                        required
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Nomor yang sama diperbolehkan.</p>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Tanggal Faktur <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        :name="`invoices[${invoiceIndex}][invoice_date]`"
                                        x-model="invoice.invoice_date"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Nominal Faktur <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500">
                                            Rp
                                        </span>
                                        <input
                                            type="text"
                                            :name="`invoices[${invoiceIndex}][nominal]`"
                                            x-model="invoice.nominal"
                                            @input="normalizeNominal(invoiceIndex)"
                                            class="block w-full rounded-lg border-gray-300 pl-9 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                            placeholder="0"
                                            required
                                        >
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Preview: Rp <span x-text="formatCurrency(invoice.nominal) || '0'"></span>
                                    </p>
                                </div>
                            </div>

                            {{-- Upload Pages --}}
                            <div class="mt-5">
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">
                                            Foto Halaman Faktur <span class="text-red-500">*</span>
                                        </label>
                                        <p class="mt-0.5 text-xs text-gray-500">
                                            Upload satu atau beberapa halaman faktur. Urutan mengikuti urutan upload.
                                        </p>
                                    </div>
                                </div>

                                <label class="flex min-h-24 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-[#318f8c]/40 bg-teal-50/60 px-4 py-4 text-center transition hover:border-[#318f8c] hover:bg-teal-50">
                                    <i class="ri-camera-line text-2xl text-[#318f8c]"></i>
                                    <span class="mt-1 text-sm font-semibold text-gray-800">Tambah Foto Faktur</span>
                                    <span class="mt-1 text-xs text-gray-500">Pilih kamera atau galeri dari perangkat. Galeri mendukung beberapa foto.</span>
                                    <input type="file" class="hidden" accept="image/*" multiple :name="`invoices[${invoiceIndex}][photos][]`" :data-invoice-file-input="invoice.id" @change="handleInvoicePages($event, invoiceIndex)">
                                </label>
                            </div>

                            {{-- Page Preview --}}
                            <div class="mt-5">
                                <template x-if="invoice.pages.length > 0">
                                    <div>
                                        <div class="mb-3 text-sm font-semibold text-gray-900">
                                            Preview Halaman
                                            <span class="text-gray-500">
                                                (<span x-text="invoice.pages.length"></span> halaman)
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                                            <template x-for="(page, pageIndex) in invoice.pages" :key="page.id">
                                                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                                                    <div class="aspect-[3/4] bg-gray-100">
                                                        <img
                                                            :src="page.preview"
                                                            :alt="`Halaman ${pageIndex + 1}`"
                                                            class="h-full w-full object-cover"
                                                        >
                                                    </div>

                                                    <div class="p-3">
                                                        <div class="text-xs font-semibold text-gray-900">
                                                            Halaman <span x-text="pageIndex + 1"></span>
                                                        </div>

                                                        <div
                                                            class="mt-0.5 truncate text-xs text-gray-500"
                                                            x-text="page.name"
                                                        ></div>

                                                        <button
                                                            type="button"
                                                            @click="removeInvoicePage(invoiceIndex, pageIndex)"
                                                            class="mt-2 w-full rounded-lg border border-red-200 px-2 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                                        >
                                                            Hapus Halaman
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="invoice.pages.length === 0">
                                    <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                                        Belum ada halaman faktur yang diupload untuk faktur ini.
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            {{-- Step 3 --}}
            <section x-show="currentStep === 3" x-cloak>
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Evaluasi & Persetujuan
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Tentukan hasil evaluasi faktur, isi catatan akhir, dan siapkan persetujuan.
                    </p>
                </div>

                {{-- Summary --}}
                <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Jumlah Faktur
                        </div>
                        <div class="mt-1 text-2xl font-bold text-gray-900" x-text="invoices.length"></div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Total Halaman
                        </div>
                        <div class="mt-1 text-2xl font-bold text-gray-900" x-text="totalPages"></div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Total Nominal
                        </div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">
                            Rp <span x-text="formattedTotalNominal"></span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Evaluasi Global
                        </div>
                        <div class="mt-2">
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="resolvedEvaluationStatusClass"
                                x-text="resolvedEvaluationStatusLabel"
                            ></span>
                        </div>
                    </div>
                </div>

                {{-- Evaluasi Faktur --}}
                <div class="space-y-4">
                    <template x-for="(invoice, invoiceIndex) in invoices" :key="invoice.id">
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">
                                        Faktur <span x-text="invoiceIndex + 1"></span>
                                        <template x-if="invoice.invoice_number">
                                            <span>
                                                — <span x-text="invoice.invoice_number"></span>
                                            </span>
                                        </template>
                                    </h3>

                                    <div class="mt-1 flex flex-wrap gap-2 text-xs text-gray-500">
                                        <span>
                                            Nominal:
                                            <strong class="text-gray-700">
                                                Rp <span x-text="formatCurrency(invoice.nominal) || '0'"></span>
                                            </strong>
                                        </span>

                                        <span>·</span>

                                        <span>
                                            Halaman:
                                            <strong class="text-gray-700" x-text="invoice.pages.length"></strong>
                                        </span>
                                    </div>
                                </div>

                                <span
                                    class="inline-flex w-fit rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="evaluationStatusClass(invoice.evaluation_status)"
                                    x-text="evaluationStatusLabel(invoice.evaluation_status)"
                                ></span>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Status Evaluasi <span class="text-red-500">*</span>
                                    </label>

                                    <select
                                        :name="`invoices[${invoiceIndex}][evaluation_status]`"
                                        x-model="invoice.evaluation_status"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                        required
                                    >
                                        <option value="unchecked">Belum dicek</option>
                                        <option value="matched">Sesuai</option>
                                        <option value="rejected">Ditolak</option>
                                        <option value="partially_rejected">Ditolak sebagian</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Catatan Evaluasi Faktur
                                    </label>

                                    <textarea
                                        :name="`invoices[${invoiceIndex}][evaluation_note]`"
                                        x-model="invoice.evaluation_note"
                                        rows="2"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                                        placeholder="Contoh: Sesuai SP offline / ED dekat / jumlah fisik tidak sesuai"
                                    ></textarea>
                                </div>
                            </div>

                            {{-- Mini Preview Pages --}}
                            <template x-if="invoice.pages.length > 0">
                                <div class="mt-4">
                                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Preview Halaman Faktur
                                    </div>

                                    <div class="flex gap-2 overflow-x-auto pb-1">
                                        <template x-for="(page, pageIndex) in invoice.pages" :key="page.id">
                                            <div class="w-20 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                                <div class="aspect-[3/4]">
                                                    <img
                                                        :src="page.preview"
                                                        :alt="`Halaman ${pageIndex + 1}`"
                                                        class="h-full w-full object-cover"
                                                    >
                                                </div>

                                                <div class="bg-white px-2 py-1 text-center text-[11px] font-medium text-gray-600">
                                                    H<span x-text="pageIndex + 1"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="invoice.pages.length === 0">
                                <div class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                                    Faktur ini belum memiliki foto halaman. Sebaiknya kembali ke Step 2 untuk upload bukti faktur.
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Catatan Global --}}
                <div class="mt-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <label for="evaluation_note" class="mb-1 block text-sm font-medium text-gray-700">
                        Catatan Evaluasi Umum
                    </label>

                    <textarea
                        id="evaluation_note"
                        name="evaluation_note"
                        rows="3"
                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
                        placeholder="Catatan umum hasil pengecekan semua faktur"
                    >{{ old('evaluation_note') }}</textarea>
                </div>

                {{-- Digital Signature --}}
                <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
                    {{-- Sender Signature --}}
                    <div
                        x-data="signaturePad('sender_signature')"
                        class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm"
                    >
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">
                                    Tanda Tangan Pengirim
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Tanda tangan digital pengirim atau kurir.
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="clear"
                                class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                            >
                                Hapus
                            </button>
                        </div>

                        <div class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-2">
                            <canvas
                                x-ref="canvas"
                                class="h-48 w-full touch-none rounded-lg bg-white"
                                @mousedown="startDrawing"
                                @mousemove="draw"
                                @mouseup="stopDrawing"
                                @mouseleave="stopDrawing"
                                @touchstart="startDrawing"
                                @touchmove="draw"
                                @touchend="stopDrawing"
                                @touchcancel="stopDrawing"
                            ></canvas>
                        </div>

                        <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                            <span>Gunakan mouse atau sentuhan layar.</span>
                            <span x-show="hasSignature" class="font-semibold text-green-600">
                                Tanda tangan terisi
                            </span>
                        </div>

                        <input type="hidden" name="sender_signature" value="">
                    </div>
                </div>

                {{-- Final Info --}}
                <div class="mt-5 rounded-xl border border-blue-200 bg-blue-50 p-4">
                    <div class="text-sm font-semibold text-blue-900">
                        Informasi Submit
                    </div>
                    <p class="mt-1 text-sm text-blue-800">
                        Simpan Draft akan menyimpan data sebagai draft. Ajukan Approval akan mengirim data ke status menunggu approval.
                    </p>
                </div>
            </section>
        </div>

        {{-- Navigation --}}
        <div class="fixed inset-x-0 bottom-0 z-40 grid grid-cols-2 gap-2 border-t border-gray-200 bg-white/95 p-3 shadow-[0_-8px_24px_rgba(15,23,42,0.08)] backdrop-blur sm:static sm:mt-5 sm:flex sm:items-center sm:justify-between sm:border-0 sm:bg-transparent sm:p-0 sm:shadow-none">
            <div class="contents sm:block">
                <button
                    type="button"
                    x-show="currentStep > 1"
                    @click="previousStep"
                    class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                >
                    Sebelumnya
                </button>
            </div>

            <div class="contents sm:flex sm:gap-3">
                <a href="{{ route('goods-receipts.index') }}"
                   class="hidden min-h-11 items-center justify-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:inline-flex">
                    Batal
                </a>

                <button
                    type="button"
                    x-show="currentStep < totalSteps"
                    @click="nextStep"
                    :class="currentStep === 1 && 'col-span-2'"
                    :disabled="isCompressing"
                    class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-[#318f8c] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#287c79] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span x-show="!isCompressing">Lanjut</span>
                    <span x-show="isCompressing">Compressing...</span>
                </button>

                <button
                    type="button"
                    x-show="currentStep === totalSteps"
                    @click="submitAs('draft')"
                    :disabled="isCompressing || isSubmitting"
                    class="inline-flex min-h-11 w-full items-center justify-center rounded-xl border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span x-show="!isSubmitting">Simpan Draft</span>
                    <span x-show="isSubmitting">Menyimpan...</span>
                </button>

                <button
                    type="button"
                    x-show="currentStep === totalSteps"
                    @click="submitAs('submit_approval')"
                    :disabled="isCompressing || isSubmitting"
                    class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span x-show="!isSubmitting">Ajukan Approval</span>
                    <span x-show="isSubmitting">Menyimpan...</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    window.appFlash = {
        success: @json(session('success')),
        error: @json(session('error')),
        validationErrors: @json($errors->all()),
        hasValidationErrors: @json($errors->any()),
    };

    console.log('APP FLASH:', window.appFlash);
</script>
@endsection
