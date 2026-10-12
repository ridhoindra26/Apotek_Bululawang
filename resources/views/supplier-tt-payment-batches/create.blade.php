@extends('layout.layout')
@section('title', 'Buat Batch Pembayaran')
@section('page_title', 'Batch Pembayaran TT')
@section('content')
@php $isEdit = isset($batch); @endphp
<div class="mx-auto max-w-6xl px-4 pb-10 sm:px-6 lg:px-8" x-data="{ branch: @js((string)old('branch_id', $batch->branch_id ?? '')), selected: @js(array_map('strval', old('invoice_ids', $isEdit ? $batch->invoices->pluck('id')->all() : []))) }">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"><a href="{{ route('supplier-tt-payment-batches.index') }}" class="text-sm font-semibold text-[#318f8c]">← Kembali</a><h1 class="mt-3 text-2xl font-bold text-gray-900">Buat Batch Pembayaran</h1></div>
    @if($errors->any())<div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ $isEdit ? route('supplier-tt-payment-batches.update', $batch) : route('supplier-tt-payment-batches.store') }}" class="mt-4 space-y-4">@csrf @if($isEdit) @method('PUT') @endif
        <section class="grid grid-cols-1 gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-2">
            <div><label class="mb-2 block text-sm font-semibold text-gray-700">Cabang <span class="text-red-500">*</span></label><select name="branch_id" x-model="branch" @change="selected = []" class="min-h-11 w-full rounded-xl border-gray-300 bg-white shadow-md shadow-gray-200/70 focus:border-[#318f8c] focus:ring-[#318f8c]"><option value="">Pilih cabang</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
            <div><label class="mb-2 block text-sm font-semibold text-gray-700">Tanggal Pembayaran <span class="text-red-500">*</span></label><input type="date" name="payment_date" value="{{ old('payment_date', $isEdit ? $batch->payment_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" class="min-h-11 w-full rounded-xl border-gray-300 bg-white shadow-md shadow-gray-200/70 focus:border-[#318f8c] focus:ring-[#318f8c]"></div>
            <div class="sm:col-span-2"><label class="mb-2 block text-sm font-semibold text-gray-700">Catatan Keseluruhan</label><textarea name="notes" rows="3" class="w-full rounded-xl border-gray-300 bg-white shadow-md shadow-gray-200/70 focus:border-[#318f8c] focus:ring-[#318f8c]">{{ old('notes', $batch->notes ?? '') }}</textarea></div>
        </section>
        <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"><div><h2 class="font-bold text-gray-900">Pilih Faktur Terverifikasi</h2><p class="text-sm text-gray-500">Diurutkan dari faktur yang sudah lewat atau paling dekat jatuh tempo.</p></div><span class="w-fit rounded-full bg-teal-50 px-3 py-1 text-sm font-bold text-[#318f8c]" x-text="`${selected.length} dipilih`"></span></div>
            <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-semibold"><span class="rounded-full bg-red-100 px-2.5 py-1 text-red-700">Lewat jatuh tempo</span><span class="rounded-full bg-amber-100 px-2.5 py-1 text-amber-700">Jatuh tempo hari ini</span><span class="rounded-full bg-blue-100 px-2.5 py-1 text-blue-700">Jatuh tempo ≤ 7 hari</span><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-emerald-700">Masih aman</span></div>
            <div class="mt-4 flex flex-col gap-3">
                @forelse($invoices as $invoice)
                    @php
                        $dueCardClass = match ($invoice->due_status) {
                            'overdue' => 'border-red-200 bg-gradient-to-r from-red-50 via-white to-rose-50 hover:border-red-300',
                            'due_today' => 'border-amber-200 bg-gradient-to-r from-amber-50 via-white to-yellow-50 hover:border-amber-300',
                            'due_soon' => 'border-blue-200 bg-gradient-to-r from-blue-50 via-white to-sky-50 hover:border-blue-300',
                            default => 'border-emerald-200 bg-gradient-to-r from-emerald-50/70 via-white to-teal-50/70 hover:border-emerald-300',
                        };
                        $dueBadgeClass = match ($invoice->due_status) {
                            'overdue' => 'bg-red-100 text-red-700 ring-red-600/20',
                            'due_today' => 'bg-amber-100 text-amber-700 ring-amber-600/20',
                            'due_soon' => 'bg-blue-100 text-blue-700 ring-blue-600/20',
                            default => 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
                        };
                    @endphp
                    <label x-show="branch === '{{ $invoice->branch_id }}'" x-transition.opacity class="group flex w-full cursor-pointer items-start gap-3 rounded-2xl border p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $dueCardClass }}" :class="selected.includes('{{ $invoice->id }}') ? 'ring-2 ring-[#318f8c] ring-offset-1' : ''">
                        <input type="checkbox" name="invoice_ids[]" value="{{ $invoice->id }}" x-model="selected" class="mt-1 rounded border-gray-300 text-[#318f8c] focus:ring-[#318f8c]">
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <span class="break-words text-sm font-bold text-gray-900">{{ $invoice->invoice_number }} · {{ $invoice->supplier?->name }}</span>
                                <span class="inline-flex w-fit rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 ring-inset {{ $dueBadgeClass }}">{{ $invoice->due_status_label }}</span>
                            </span>
                            <span class="mt-2 block text-xs font-medium text-gray-600"><i class="ri-calendar-event-line mr-1"></i>Jatuh tempo {{ $invoice->due_date?->format('d M Y') ?? '-' }}</span>
                            <span class="mt-3 grid grid-cols-1 gap-1.5 border-t border-black/5 pt-3 text-xs sm:grid-cols-3 sm:gap-3">
                                <span class="text-gray-600">Bruto <strong class="block text-gray-900 sm:inline">Rp {{ number_format((float)$invoice->gross_nominal,0,',','.') }}</strong></span>
                                <span class="text-red-600">Potongan <strong class="block sm:inline">{{ (float)$invoice->deduction_total > 0 ? '- Rp '.number_format((float)$invoice->deduction_total,0,',','.') : 'Rp 0' }}</strong></span>
                                <span class="text-[#287c79] sm:text-right">Net <strong class="block text-sm sm:inline">Rp {{ number_format((float)$invoice->net_nominal,0,',','.') }}</strong></span>
                            </span>
                        </span>
                    </label>
                @empty<p class="py-8 text-center text-sm text-gray-500">Tidak ada faktur terverifikasi.</p>@endforelse
                <p x-show="!branch" class="py-8 text-center text-sm text-gray-500">Pilih cabang untuk menampilkan faktur.</p>
            </div>
        </section>
        <button class="min-h-11 w-full rounded-xl bg-[#318f8c] px-4 py-2 font-bold text-white sm:w-auto">{{ $isEdit ? 'Perbarui Batch' : 'Simpan Batch Draft' }}</button>
    </form>
</div>
@endsection
