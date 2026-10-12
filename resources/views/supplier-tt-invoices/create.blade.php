@php
    $isEdit = isset($supplierTtInvoice);
@endphp

@extends('layout.layout')

@section('title', $isEdit ? 'Edit Faktur TT' : 'Terima Faktur TT')
@section('page_title', 'Faktur TT')

@section('content')
@php
    $supplierOptions = $suppliers
        ->map(fn ($supplier) => [
            'id' => $supplier->id,
            'label' => $supplier->name,
        ])
        ->values();
    

    $selectedSupplierId = old('supplier_id', $supplierTtInvoice->supplier_id ?? '');
    $selectedSupplier = $suppliers->firstWhere('id', $selectedSupplierId) ?? ($supplierTtInvoice->supplier ?? null);
    $initialDeductions = old('deductions', $isEdit
        ? $supplierTtInvoice->deductions->map(fn ($row) => [
            'id' => $row->id,
            'type' => $row->type,
            'amount' => (string) (float) $row->amount,
            'reference_number' => $row->reference_number ?? '',
            'reference_date' => $row->reference_date?->format('Y-m-d') ?? '',
            'description' => $row->description ?? '',
        ])->values()->all()
        : []);
    $initialPhotos = $isEdit
        ? $supplierTtInvoice->photos->map(fn ($photo) => [
            'id' => 'existing-'.$photo->id,
            'existingId' => $photo->id,
            'file' => null,
            'preview' => $photo->url,
            'name' => $photo->original_name ?: $photo->page_label,
            'originalSize' => $photo->size ?? 0,
            'size' => $photo->size ?? 0,
        ])->values()->all()
        : [];
@endphp

<div
    x-data="supplierTtInvoiceWizard({
        suppliers: @js($supplierOptions),
        availableGoodsReceiptUrl: @js(route('supplier-tt-invoices.available-goods-receipts')),
        invoiceId: @js($supplierTtInvoice->id ?? null),
        initial: {
            supplierId: @js((string) $selectedSupplierId),
            branchId: @js((string) old('branch_id', $supplierTtInvoice->branch_id ?? '')),
            supplierLabel: @js($selectedSupplier?->name ?? ''),
            goodsReceiptId: @js((string) old('goods_receipt_id', $supplierTtInvoice->goods_receipt_id ?? '')),
            invoiceNumber: @js(old('invoice_number', $supplierTtInvoice->invoice_number ?? '')),
            invoiceDate: @js(old('invoice_date', isset($supplierTtInvoice) ? $supplierTtInvoice->invoice_date?->format('Y-m-d') : '')),
            receivedDate: @js(old('received_date', isset($supplierTtInvoice) ? $supplierTtInvoice->received_date?->format('Y-m-d') : now()->format('Y-m-d'))),
            dueDate: @js(old('due_date', isset($supplierTtInvoice) ? $supplierTtInvoice->due_date?->format('Y-m-d') : '')),
            grossNominal: @js(old('gross_nominal', $supplierTtInvoice->gross_nominal ?? '')),
            notes: @js(old('notes', $supplierTtInvoice->notes ?? '')),
            deductions: @js($initialDeductions),
            photos: @js($initialPhotos),
        },
    })"
    class="mx-auto max-w-7xl px-4 pb-28 sm:px-6 sm:pb-6 lg:px-8"
>
    @if ($errors->any())
        <div class="mb-3 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800" role="alert">
            <div class="flex items-start gap-3">
                <i class="ri-error-warning-line mt-0.5 text-xl"></i>
                <div>
                    <p class="text-sm font-bold">Data belum dapat disimpan</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-2 text-xs text-red-700">Jika sebelumnya memilih foto baru, silakan pilih ulang foto tersebut.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-3 rounded-xl border border-gray-200 bg-white p-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between shadow-sm">
        <div>
            <a href="{{ route('supplier-tt-invoices.index') }}"
               class="text-sm font-medium text-blue-600 hover:text-blue-700">
                ← Kembali ke Faktur TT
            </a>

            <h1 class="mt-3 text-2xl font-bold text-gray-900">
                {{ $isEdit ? 'Edit Faktur TT' : 'Terima Faktur TT' }}
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Catat faktur TT supplier, hubungkan ke barang datang, input potongan, dan cek nominal jatuh tempo.
            </p>
        </div>
    </div>

    <form
        method="POST"
        action="{{ $isEdit ? route('supplier-tt-invoices.update', $supplierTtInvoice) : route('supplier-tt-invoices.store') }}"
        enctype="multipart/form-data"
        data-supplier-tt-form
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <input type="hidden" name="supplier_id" :value="selectedSupplierId">
        <input type="hidden" name="goods_receipt_id" :value="selectedGoodsReceiptId">
        <input type="hidden" name="submit_action" value="draft">
        <template x-for="photoId in deletedPhotoIds" :key="`deleted-${photoId}`">
            <input type="hidden" name="deleted_photo_ids[]" :value="photoId">
        </template>

        {{-- Stepper --}}
        <div class="mb-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            {{-- Compact circular stepper --}}
            <div class="flex items-center gap-4">
                <div
                    class="h-16 w-16 shrink-0 rounded-full p-[6px] transition-all duration-500 ease-out"
                    :style="{ background: `conic-gradient(#318f8c ${stepProgress}, #e5e7eb 0)` }"
                    role="progressbar"
                    :aria-valuenow="currentStep"
                    aria-valuemin="1"
                    :aria-valuemax="totalSteps"
                >
                    <div class="flex h-full w-full items-center justify-center rounded-full bg-white text-xs font-bold text-gray-900">
                        <span x-text="currentStep"></span>
                        <span class="mx-0.5 font-medium text-gray-400">of</span>
                        <span x-text="totalSteps"></span>
                    </div>
                </div>

                <div class="min-w-0 flex-1">
                    <h2 class="truncate text-base font-bold text-gray-900 sm:text-lg" x-text="currentStepInfo.title"></h2>
                    <p
                        class="mt-1 truncate text-xs font-medium text-gray-400 sm:text-sm"
                        :class="!nextStepInfo && 'text-green-600'"
                        x-text="nextStepInfo ? `Next: ${nextStepInfo.title}` : 'Ready to save'"
                    ></p>
                </div>
            </div>

            {{-- Radial progress line --}}
            <div class="hidden relative px-1 pt-1 sm:px-6">
                <div class="absolute left-[16.666%] right-[16.666%] top-6 h-1 rounded-full bg-gray-200">
                    <div
                        class="h-full rounded-full bg-[#318f8c] transition-all duration-500 ease-out"
                        :style="`width: ${stepLineProgress}`"
                    ></div>
                </div>

                <div class="relative grid grid-cols-3">
                    <template x-for="step in steps" :key="`radial-${step.number}`">
                        <button
                            type="button"
                            @click="step.number <= currentStep && goToStep(step.number)"
                            class="group flex min-w-0 flex-col items-center px-1 text-center"
                            :class="step.number <= currentStep ? 'cursor-pointer' : 'cursor-default'"
                        >
                            <span
                                class="relative z-10 flex h-11 w-11 items-center justify-center rounded-full border-2 bg-white text-sm font-bold transition-all duration-300 sm:h-12 sm:w-12"
                                :class="{
                                    'border-[#318f8c] bg-[#318f8c] text-white shadow-md ring-4 ring-[#318f8c]/15': currentStep === step.number,
                                    'border-[#318f8c] bg-white text-[#318f8c]': currentStep > step.number,
                                    'border-gray-300 bg-white text-gray-400': currentStep < step.number,
                                }"
                            >
                                <i x-show="currentStep > step.number" class="ri-check-line text-xl"></i>
                                <span x-show="currentStep <= step.number" x-text="step.number"></span>
                            </span>

                            <span
                                class="mt-2 w-full truncate text-[10px] font-semibold leading-tight sm:text-xs"
                                :class="currentStep === step.number ? 'text-[#287c79]' : (currentStep > step.number ? 'text-gray-700' : 'text-gray-400')"
                                x-text="step.title"
                            ></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Current-step detail --}}
            <div class="hidden mt-4 flex items-start gap-3 rounded-xl bg-gray-50 p-3 sm:items-center">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#318f8c]/10 text-sm font-bold text-[#287c79]">
                    <span x-text="currentStep"></span>/<span x-text="totalSteps"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-[#318f8c]">Tahap saat ini</div>
                    <div class="font-bold text-gray-900" x-text="currentStepInfo.title"></div>
                    <div class="mt-0.5 text-xs text-gray-500" x-text="currentStepInfo.description"></div>
                </div>
            </div>

            <div class="hidden">
                <template x-for="step in steps" :key="step.number">
                    <button
                        type="button"
                        x-show="currentStep === step.number"
                        class="flex w-full cursor-default items-center gap-4 rounded-xl border border-[#318f8c]/25 bg-[#318f8c]/5 p-4 text-left"
                        :class="{
                            'border-blue-500 bg-blue-50': currentStep === step.number,
                            'border-green-500 bg-green-50': currentStep > step.number,
                            'border-gray-200 bg-white hover:bg-gray-50': currentStep < step.number,
                        }"
                        tabindex="-1"
                    >
                        <div
                            class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-full text-sm font-bold ring-4 ring-[#318f8c]/10"
                            :class="{
                                'bg-blue-600 text-white': currentStep === step.number,
                                'bg-green-600 text-white': currentStep > step.number,
                                'bg-gray-100 text-gray-600': currentStep < step.number,
                            }"
                        >
                            <span class="text-lg leading-none" x-text="step.number"></span>
                            <span class="mt-1 text-[10px] font-semibold leading-none">dari <span x-text="totalSteps"></span></span>
                            <span x-show="currentStep > step.number">✓</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="text-[11px] font-bold uppercase tracking-wider text-black">Tahap saat ini</div>
                            <div class="mt-0.5 text-base font-bold text-gray-900 sm:text-lg" x-text="step.title"></div>
                            <div class="mt-1 text-xs leading-5 text-gray-500 sm:text-sm" x-text="step.description"></div>
                        </div>
                    </button>
                </template>
            </div>

            <div class="hidden mt-4 h-2 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-[#318f8c] transition-all duration-300" :style="`width: ${stepProgress}`"></div>
            </div>

            <div class="hidden mt-3 flex items-center justify-between gap-3 text-xs">
                <span class="font-medium text-gray-500">
                    Tahap <span x-text="currentStep"></span> dari <span x-text="totalSteps"></span>
                </span>
                <div x-show="nextStepInfo" class="flex min-w-0 items-center gap-1 text-gray-500">
                    <span>Berikutnya:</span>
                    <strong class="truncate text-gray-800" x-text="nextStepInfo?.title"></strong>
                    <i class="ri-arrow-right-s-line shrink-0 text-base text-[#318f8c]"></i>
                </div>
                <div x-show="!nextStepInfo" class="flex items-center gap-1 font-semibold text-green-700">
                    <i class="ri-checkbox-circle-line text-base"></i>
                    Siap disimpan
                </div>
            </div>
        </div>

        {{-- Step Content --}}
        <div class="supplier-tt-fields rounded-xl border border-gray-200 bg-white p-4 shadow-sm
                    [&_label]:mb-2
                    [&_input]:min-h-11 [&_input]:rounded-xl [&_input]:border-gray-200 [&_input]:bg-gray-50/60 [&_input]:shadow-none [&_input]:transition
                    [&_input:hover]:border-gray-300 [&_input:focus]:border-[#318f8c] [&_input:focus]:bg-white [&_input:focus]:ring-4 [&_input:focus]:ring-[#318f8c]/10
                    [&_select]:min-h-11 [&_select]:rounded-xl [&_select]:border-gray-200 [&_select]:bg-gray-50/60 [&_select]:shadow-none [&_select]:transition
                    [&_select:hover]:border-gray-300 [&_select:focus]:border-[#318f8c] [&_select:focus]:bg-white [&_select:focus]:ring-4 [&_select:focus]:ring-[#318f8c]/10
                    [&_textarea]:min-h-24 [&_textarea]:rounded-xl [&_textarea]:border-gray-200 [&_textarea]:bg-gray-50/60 [&_textarea]:shadow-none [&_textarea]:transition
                    [&_textarea:hover]:border-gray-300 [&_textarea:focus]:border-[#318f8c] [&_textarea:focus]:bg-white [&_textarea:focus]:ring-4 [&_textarea:focus]:ring-[#318f8c]/10">

            {{-- Step 1 --}}
            <section x-show="currentStep === 1" x-cloak>
                <div class="mb-3">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Supplier & Goods Receipt
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Pilih supplier terlebih dahulu. Goods receipt dapat dipilih bila tersedia.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700">Cabang Asal <span class="text-red-500">*</span></label>
                        <select id="branch_id" name="branch_id" x-model="branchId" @change="selectedGoodsReceiptId = ''; goodsReceipts = []; fetchAvailableGoodsReceipts()" class="block w-full px-3 py-2">
                            <option value="">Pilih cabang</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('branch_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    {{-- Supplier Search --}}
                    <div class="relative" @click.outside="supplierDropdownOpen = false">
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Supplier <span class="text-red-500">*</span>
                        </label>

                        <button
                            type="button"
                            @click="supplierDropdownOpen = !supplierDropdownOpen"
                            class="flex min-h-11 w-full items-center justify-between rounded-xl border border-gray-200 bg-gray-50/60 px-3 py-2 text-left text-sm shadow-none transition hover:border-gray-300 focus:border-[#318f8c] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#318f8c]/10"
                        >
                            <span
                                class="truncate"
                                :class="selectedSupplierLabel ? 'text-gray-900' : 'text-gray-400'"
                                x-text="selectedSupplierLabel || 'Cari atau pilih supplier'"
                            ></span>

                            <i class="ri-arrow-down-s-line text-lg text-gray-500"></i>
                        </button>

                        <div
                            x-show="supplierDropdownOpen"
                            x-cloak
                            class="absolute z-50 mt-2 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl"
                        >
                            <div class="border-b border-gray-100 p-2">
                                <input
                                    x-model="supplierSearch"
                                    type="text"
                                    placeholder="Cari supplier..."
                                    class="block w-full rounded-lg border-gray-300 text-sm focus:border-[#318f8c] focus:ring-[#318f8c] px-3 py-2"
                                >
                            </div>

                            <div class="max-h-64 overflow-y-auto p-1">
                                <template x-if="filteredSuppliers.length === 0">
                                    <div class="px-3 py-2 text-sm text-gray-500">
                                        Supplier tidak ditemukan.
                                    </div>
                                </template>

                                <template x-for="supplier in filteredSuppliers" :key="supplier.id">
                                    <button
                                        type="button"
                                        @click="chooseSupplier(supplier)"
                                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm hover:bg-[#318f8c]/10"
                                        :class="String(supplier.id) === selectedSupplierId ? 'bg-[#318f8c]/10 text-[#287c79]' : 'text-gray-700'"
                                    >
                                        <span x-text="supplier.label"></span>
                                        <i
                                            x-show="String(supplier.id) === selectedSupplierId"
                                            class="ri-check-line text-lg"
                                        ></i>
                                    </button>
                                </template>
                            </div>
                        </div>

                        @error('supplier_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Goods Receipt --}}
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Goods Receipt Available <span class="text-xs font-normal text-gray-500">(Opsional)</span>
                        </label>

                        <select
                            x-model="selectedGoodsReceiptId"
                            @change="chooseGoodsReceipt"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] px-3 py-2"
                            :disabled="!selectedSupplierId || loadingGoodsReceipts"
                        >
                            <option value="">
                                <template x-if="!selectedSupplierId">
                                    <span>Pilih supplier terlebih dahulu</span>
                                </template>
                            </option>

                            <template x-if="selectedSupplierId && !loadingGoodsReceipts">
                                <option value="">Tanpa goods receipt</option>
                            </template>

                            <template x-for="receipt in goodsReceipts" :key="receipt.id">
                                <option
                                    :value="receipt.id"
                                    x-text="formatGoodsReceiptOption(receipt)"
                                ></option>
                            </template>
                        </select>

                        <p x-show="loadingGoodsReceipts" x-cloak class="mt-1 text-xs text-blue-600">
                            Memuat goods receipt available...
                        </p>

                        <p x-show="selectedSupplierId && !loadingGoodsReceipts && goodsReceipts.length === 0" x-cloak class="mt-1 text-xs text-amber-600">
                            Tidak ada goods receipt yang masih available untuk supplier ini.
                        </p>

                        @error('goods_receipt_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Selected Goods Receipt Summary --}}
                <div
                    x-show="selectedGoodsReceipt"
                    x-cloak
                    class="mt-6 rounded-xl border border-[#318f8c]/30 bg-[#318f8c]/5 p-4"
                >
                    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">
                                Ringkasan Goods Receipt
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Sistem akan membatasi nominal Faktur TT berdasarkan sisa nominal ini.
                            </p>
                        </div>

                        <span
                            class="inline-flex w-fit rounded-full px-2.5 py-1 text-xs font-semibold"
                            :class="selectedGoodsReceipt?.tt_count > 1 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700'"
                            x-text="selectedGoodsReceipt?.tt_count_label"
                        ></span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                        <div class="rounded-lg bg-white p-3">
                            <div class="text-xs text-gray-500">Goods Receipt</div>
                            <div class="mt-1 font-semibold text-gray-900">
                                #<span x-text="selectedGoodsReceipt?.id"></span>
                            </div>
                            <div class="text-xs text-gray-500" x-text="selectedGoodsReceipt?.received_date || '-'"></div>
                        </div>

                        <div class="rounded-lg bg-white p-3">
                            <div class="text-xs text-gray-500">Total GR</div>
                            <div class="mt-1 font-semibold text-gray-900">
                                Rp <span x-text="formatCurrency(selectedGoodsReceipt?.total_nominal)"></span>
                            </div>
                        </div>

                        <div class="rounded-lg bg-white p-3">
                            <div class="text-xs text-gray-500">Total TT Existing</div>
                            <div class="mt-1 font-semibold text-gray-900">
                                Rp <span x-text="formatCurrency(selectedGoodsReceipt?.active_tt_total)"></span>
                            </div>
                        </div>

                        <div class="rounded-lg bg-white p-3">
                            <div class="text-xs text-gray-500">Sisa Available</div>
                            <div class="mt-1 font-bold text-[#318f8c]">
                                Rp <span x-text="formatCurrency(selectedGoodsReceipt?.remaining_nominal)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Step 2 --}}
            <section x-show="currentStep === 2" x-cloak>
                <div class="mb-3">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Detail Faktur TT
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Masukkan identitas faktur, tanggal jatuh tempo, nominal bruto, dan foto dokumen.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            5 Digit Terakhir Nomor Faktur <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="invoice_number"
                            x-model="invoiceNumber"
                            @input="invoiceNumber = String(invoiceNumber || '').replace(/\D/g, '').slice(0, 5)"
                            inputmode="numeric"
                            maxlength="5"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] px-3 py-2"
                            placeholder="Contoh: 12345"
                        >
                        <p class="mt-1 text-xs text-gray-500">Masukkan 5 digit terakhir. Nomor yang sama tetap diperbolehkan.</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Tanggal Faktur <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            name="invoice_date"
                            x-model="invoiceDate"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] px-3 py-2"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Tanggal Terima Faktur TT <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            name="received_date"
                            x-model="receivedDate"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] px-3 py-2"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Due Date / Jatuh Tempo <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="date"
                            name="due_date"
                            x-model="dueDate"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] px-3 py-2"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Nominal Bruto <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500">
                                Rp
                            </span>
                            <input
                                type="text"
                                name="gross_nominal"
                                x-model="grossNominal"
                                @input="normalizeGrossNominal"
                                class="block w-full rounded-lg border-gray-300 pl-9 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                                placeholder="0"
                            >
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Catatan
                        </label>
                        <textarea
                            name="notes"
                            x-model="notes"
                            rows="3"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] px-3 py-2"
                            placeholder="Catatan opsional terkait faktur TT"
                        ></textarea>
                    </div>

                    {{-- <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Catatan Verifikasi
                        </label>
                        <textarea
                            name="verification_note"
                            x-model="verificationNote"
                            rows="3"
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                            placeholder="Wajib diisi jika status bermasalah"
                        ></textarea>
                    </div> --}}
                </div>

                {{-- Upload Photo --}}
                <div class="mt-6">
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Foto Faktur TT <span class="text-red-500">*</span>
                    </label>

                    <label class="flex min-h-28 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-[#318f8c]/40 bg-teal-50/60 px-4 py-5 text-center transition hover:border-[#318f8c] hover:bg-teal-50">
                        <i class="ri-camera-line text-3xl text-[#318f8c]"></i>
                        <span class="mt-1 text-sm font-semibold text-gray-800">Tambah Foto Faktur</span>
                        <span class="mt-1 text-xs text-gray-500">Pilih kamera atau galeri dari perangkat. Galeri mendukung beberapa foto.</span>
                        <input type="file" name="photos[]" class="hidden" accept="image/*" multiple @change="handlePhotos" data-supplier-tt-photo-input>
                    </label>

                    <div x-show="isCompressing" x-cloak class="mt-3 rounded-lg bg-blue-50 p-3 text-sm text-blue-700">
                        Sedang mengompres foto...
                    </div>

                    <template x-if="photos.length > 0">
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                            <template x-for="(photo, photoIndex) in photos" :key="photo.id">
                                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                                    <div class="aspect-[3/4] bg-gray-100">
                                        <img :src="photo.preview" class="h-full w-full object-cover">
                                    </div>

                                    <div class="p-3">
                                        <div class="text-xs font-semibold text-gray-900">
                                            Halaman <span x-text="photoIndex + 1"></span>
                                        </div>
                                        <div class="mt-1 truncate text-xs text-gray-500" x-text="photo.name"></div>
                                        {{-- <div class="mt-1 text-[11px] text-gray-500">
                                            <span x-text="formatFileSize(photo.originalSize)"></span>
                                            →
                                            <span class="font-semibold text-green-700" x-text="formatFileSize(photo.size)"></span>
                                        </div> --}}

                                        <button
                                            type="button"
                                            @click="removePhoto(photoIndex)"
                                            class="mt-2 w-full rounded-lg border border-red-200 px-2 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </section>

            {{-- Step 3 --}}
            <section x-show="currentStep === 3" x-cloak>
                <div class="mb-3">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Potongan & Review
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Input potongan jika ada, lalu review nominal akhir sebelum disimpan.
                    </p>
                </div>

                <div class="mb-3 rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">
                                Potongan Faktur
                            </div>
                            <div class="text-sm text-gray-500">
                                Tambahkan potongan seperti retur, diskon, koreksi harga, atau pembulatan.
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="addDeduction"
                            class="inline-flex items-center justify-center rounded-lg bg-[#318f8c] px-4 py-2 text-sm font-semibold text-white hover:bg-[#287c79]"
                        >
                            + Tambah Potongan
                        </button>
                    </div>
                </div>

                <div class="space-y-4">
                    <template x-for="(deduction, index) in deductions" :key="deduction.id">
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-900">
                                    Potongan <span x-text="index + 1"></span>
                                </h3>

                                <button
                                    type="button"
                                    @click="removeDeduction(index)"
                                    class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50"
                                >
                                    Hapus
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                                <div class="min-w-0">
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Jenis
                                    </label>
                                    <select
                                        :name="`deductions[${index}][type]`"
                                        x-model="deduction.type"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] px-3 py-2"
                                    >
                                        <option value="return">Retur</option>
                                        <option value="damaged_goods">Barang Rusak</option>
                                        <option value="price_correction">Koreksi Harga</option>
                                        <option value="discount">Diskon</option>
                                        <option value="promo">Promo</option>
                                        <option value="rounding">Pembulatan</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                </div>

                                <div class="min-w-0">
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Nominal
                                    </label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-gray-500">
                                            Rp
                                        </span>
                                        <input
                                            type="text"
                                            :name="`deductions[${index}][amount]`"
                                            x-model="deduction.amount"
                                            @input="normalizeDeduction(index)"
                                            class="block w-full rounded-lg border-gray-300 pl-9 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c]"
                                            placeholder="0"
                                        >
                                    </div>
                                </div>

                                <div class="min-w-0">
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        No Referensi
                                    </label>
                                    <input
                                        type="text"
                                        :name="`deductions[${index}][reference_number]`"
                                        x-model="deduction.reference_number"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] px-3 py-2"
                                    >
                                </div>

                                <div class="min-w-0">
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Tanggal Referensi
                                    </label>
                                    <input
                                        type="date"
                                        :name="`deductions[${index}][reference_date]`"
                                        x-model="deduction.reference_date"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] px-3 py-2"
                                    >
                                </div>

                                <div class="md:col-span-2 xl:col-span-4">
                                    <label class="mb-1 block text-sm font-medium text-gray-700">
                                        Keterangan
                                    </label>
                                    <textarea
                                        :name="`deductions[${index}][description]`"
                                        x-model="deduction.description"
                                        rows="2"
                                        class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-[#318f8c] focus:ring-[#318f8c] px-3 py-2"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="deductions.length === 0">
                        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-sm text-gray-500">
                            Tidak ada potongan.
                        </div>
                    </template>
                </div>

                {{-- Review --}}
                <div class="mt-3 grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm" :class="!selectedGoodsReceipt && 'border-dashed'">
                        <h3 class="text-base font-semibold text-gray-900">
                            Review Nominal
                        </h3>

                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between gap-3">
                                <span class="text-gray-500">Gross Nominal</span>
                                <span class="font-semibold text-gray-900">
                                    Rp <span x-text="formatCurrency(grossNominalNumber)"></span>
                                </span>
                            </div>

                            <div class="flex justify-between gap-3">
                                <span class="text-gray-500">Total Potongan</span>
                                <span class="font-semibold text-red-600">
                                    - Rp <span x-text="formatCurrency(deductionTotal)"></span>
                                </span>
                            </div>

                            <div class="border-t border-gray-100 pt-3 flex justify-between gap-3">
                                <span class="font-semibold text-gray-900">Net Nominal</span>
                                <span class="font-bold text-[#318f8c]">
                                    Rp <span x-text="formatCurrency(netNominal)"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <h3 class="text-base font-semibold text-gray-900">
                            Validasi Goods Receipt
                        </h3>

                        <div x-show="selectedGoodsReceipt" class="mt-4 space-y-3 text-sm">
                            <div class="flex justify-between gap-3">
                                <span class="text-gray-500">Sisa Available GR</span>
                                <span class="font-semibold text-gray-900">
                                    Rp <span x-text="formatCurrency(selectedGoodsReceipt?.remaining_nominal || 0)"></span>
                                </span>
                            </div>

                            <div class="flex justify-between gap-3">
                                <span class="text-gray-500">Net Faktur TT Ini</span>
                                <span class="font-semibold text-[#318f8c]">
                                    Rp <span x-text="formatCurrency(netNominal)"></span>
                                </span>
                            </div>

                            <div class="border-t border-gray-100 pt-3">
                                <template x-if="selectedGoodsReceipt && isNetNominalValid">
                                    <div class="rounded-lg bg-green-50 p-3 text-sm font-semibold text-green-700">
                                        Nominal Faktur TT masih dalam batas available Goods Receipt.
                                    </div>
                                </template>

                                <template x-if="selectedGoodsReceipt && !isNetNominalValid">
                                    <div class="rounded-lg bg-red-50 p-3 text-sm font-semibold text-red-700">
                                        Net nominal Faktur TT melebihi sisa available Goods Receipt.
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div x-show="!selectedGoodsReceipt" class="mt-4 rounded-xl bg-gray-50 p-4 text-sm text-gray-600">
                            <div class="flex items-start gap-3">
                                <i class="ri-link-unlink-m text-xl text-gray-400"></i>
                                <div>
                                    <div class="font-semibold text-gray-800">Tanpa Goods Receipt</div>
                                    <p class="mt-1 text-xs leading-5 text-gray-500">Faktur tetap dapat disimpan dan nominal tidak dibatasi oleh Goods Receipt.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- Navigation --}}
        <div class="fixed inset-x-0 bottom-0 z-40 flex flex-col-reverse gap-2 border-t border-gray-200 bg-white/95 p-3 shadow-[0_-8px_24px_rgba(15,23,42,0.08)] backdrop-blur sm:static sm:mt-5 sm:flex-row sm:items-center sm:justify-between sm:border-0 sm:bg-transparent sm:p-0 sm:shadow-none">
            <button
                type="button"
                x-show="currentStep > 1"
                @click="previousStep"
                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
            >
                Sebelumnya
            </button>

            <div class="hidden sm:block"></div>

            <div class="grid grid-cols-2 gap-2 sm:flex sm:gap-3">
                <a href="{{ route('supplier-tt-invoices.index') }}"
                   class="hidden min-h-11 items-center justify-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:inline-flex">
                    Batal
                </a>

                <button
                    type="button"
                    x-show="currentStep < totalSteps"
                    @click="nextStep"
                    :disabled="isCompressing"
                    class="col-span-2 inline-flex min-h-11 items-center justify-center rounded-xl bg-[#318f8c] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#287c79] disabled:cursor-not-allowed disabled:opacity-60 sm:col-auto"
                >
                    Lanjut
                </button>

                <button
                    type="button"
                    x-show="currentStep === totalSteps"
                    @click="submitAs('draft')"
                    :disabled="isCompressing || isSubmitting"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span x-show="!isSubmitting">{{ $isEdit ? 'Update Draft' : 'Simpan Draft' }}</span>
                    <span x-show="isSubmitting">Menyimpan...</span>
                </button>

                <button
                    type="button"
                    x-show="currentStep === totalSteps"
                    @click="submitAs('verify')"
                    :disabled="isCompressing || isSubmitting"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span x-show="!isSubmitting">{{ $isEdit ? 'Update & Verifikasi' : 'Simpan & Verifikasi' }}</span>
                    <span x-show="isSubmitting">Menyimpan...</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
