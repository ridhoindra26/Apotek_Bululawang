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
            <div class="flex items-center justify-between"><div><h2 class="font-bold text-gray-900">Pilih Faktur Terverifikasi</h2><p class="text-sm text-gray-500">Hanya faktur dari cabang yang dipilih ditampilkan.</p></div><span class="text-sm font-bold text-[#318f8c]" x-text="`${selected.length} dipilih`"></span></div>
            <div class="mt-4 space-y-2">
                @forelse($invoices as $invoice)
                    <label x-show="branch === '{{ $invoice->branch_id }}'" class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition hover:border-[#318f8c]/40 hover:shadow-md"><input type="checkbox" name="invoice_ids[]" value="{{ $invoice->id }}" x-model="selected" class="mt-1 rounded border-gray-300 text-[#318f8c] focus:ring-[#318f8c]"><span class="min-w-0 flex-1"><span class="block break-words text-sm font-bold text-gray-900">{{ $invoice->invoice_number }} · {{ $invoice->supplier?->name }}</span><span class="mt-1 block text-xs text-gray-500">Jatuh tempo {{ $invoice->due_date?->format('d M Y') }}</span></span><span class="shrink-0 text-right text-xs"><span class="block text-gray-500">Bruto Rp {{ number_format((float)$invoice->gross_nominal,0,',','.') }}</span>@if((float)$invoice->deduction_total > 0)<span class="mt-1 block font-semibold text-red-600">Potongan - Rp {{ number_format((float)$invoice->deduction_total,0,',','.') }}</span>@endif<span class="mt-1 block text-sm font-bold text-[#287c79]">Net Rp {{ number_format((float)$invoice->net_nominal,0,',','.') }}</span></span></label>
                @empty<p class="py-8 text-center text-sm text-gray-500">Tidak ada faktur terverifikasi.</p>@endforelse
                <p x-show="!branch" class="py-8 text-center text-sm text-gray-500">Pilih cabang untuk menampilkan faktur.</p>
            </div>
        </section>
        <button class="min-h-11 w-full rounded-xl bg-[#318f8c] px-4 py-2 font-bold text-white sm:w-auto">{{ $isEdit ? 'Perbarui Batch' : 'Simpan Batch Draft' }}</button>
    </form>
</div>
@endsection
