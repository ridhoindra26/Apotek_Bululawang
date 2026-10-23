@extends('layout.layout')
@section('title', $batch->batch_number)
@section('page_title', 'Batch Pembayaran TT')
@section('content')
@php
    $statusClass = match ($batch->status) {
        'locked' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        default => 'bg-slate-100 text-slate-700 ring-slate-600/20',
    };
    $grossTotal = $batch->invoices->sum(fn ($invoice) => (float) $invoice->gross_nominal);
    $deductionTotal = $batch->invoices->sum(fn ($invoice) => (float) $invoice->deduction_total);
@endphp
<div class="mx-auto max-w-7xl space-y-5 px-4 pb-10 sm:px-6 lg:px-8">
    <header class="overflow-hidden rounded-2xl border border-teal-100 bg-gradient-to-br from-white via-white to-teal-50 p-5 shadow-lg shadow-teal-900/5 sm:p-6">
        <a href="{{ route('supplier-tt-payment-batches.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-[#318f8c]"><i class="ri-arrow-left-line"></i>Kembali ke Batch</a>
        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"><div><h1 class="break-words text-2xl font-bold text-gray-900">{{ $batch->batch_number }}</h1><div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500"><span><i class="ri-store-2-line mr-1"></i>{{ $batch->branch?->name }}</span><span><i class="ri-calendar-line mr-1"></i>{{ $batch->payment_date?->format('d M Y') }}</span><span><i class="ri-file-list-3-line mr-1"></i>{{ $batch->invoices->count() }} faktur</span></div></div><span class="inline-flex w-fit rounded-full px-3 py-1.5 text-xs font-bold ring-1 ring-inset {{ $statusClass }}">{{ $batch->status_label }}</span></div>
    </header>

    @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-gray-500">Total Bruto</p><p class="mt-2 text-xl font-bold text-gray-900">Rp {{ number_format($grossTotal,0,',','.') }}</p></div>
        <div class="rounded-2xl border border-red-100 bg-red-50/60 p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-red-600">Total Potongan</p><p class="mt-2 text-xl font-bold text-red-700">- Rp {{ number_format($deductionTotal,0,',','.') }}</p></div>
        <div class="rounded-2xl border border-teal-100 bg-teal-50/60 p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-[#287c79]">Total Dibayar</p><p class="mt-2 text-xl font-bold text-[#287c79]">Rp {{ number_format((float)$batch->total_nominal,0,',','.') }}</p></div>
    </section>

    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
        @if($batch->status === 'draft')
            <a href="{{ route('supplier-tt-payment-batches.edit',$batch) }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-sm font-bold text-gray-700"><i class="ri-edit-line"></i>Edit Batch</a>
            <form method="POST" action="{{ route('supplier-tt-payment-batches.lock-pdf',$batch) }}" data-supplier-tt-action-form data-download-reload data-confirm-title="Kunci batch dan buat PDF?" data-confirm-text="Setelah dikunci, user biasa tidak dapat mengubah atau membatalkan faktur dalam batch." data-confirm-button="Ya, kunci & PDF">@csrf<button class="min-h-11 w-full rounded-xl bg-[#318f8c] px-4 py-2 text-sm font-bold text-white"><i class="ri-lock-line mr-1"></i>Kunci & Download PDF</button></form>
            @if(auth()->user()?->hasRole('superadmin'))<form method="POST" action="{{ route('supplier-tt-payment-batches.destroy',$batch) }}" data-supplier-tt-action-form data-confirm-title="Hapus batch Draft?" data-confirm-text="Faktur di dalamnya akan kembali menjadi Terverifikasi." data-confirm-button="Ya, hapus" data-confirm-color="#dc2626">@csrf @method('DELETE')<button class="min-h-11 w-full rounded-xl border border-red-200 px-4 py-2 text-sm font-bold text-red-700">Hapus Batch</button></form>@endif
        @endif
        @if(in_array($batch->status,['locked','paid']))<a href="{{ route('supplier-tt-payment-batches.pdf',$batch) }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-sm font-bold text-gray-700"><i class="ri-file-pdf-2-line"></i>Download PDF</a>@endif
        @if($batch->status === 'locked')
            <form method="POST" action="{{ route('supplier-tt-payment-batches.paid',$batch) }}" data-supplier-tt-action-form data-confirm-title="Tandai seluruh batch dibayar?" data-confirm-text="Semua faktur dalam batch akan menjadi Dibayar dan tidak dapat dikembalikan." data-confirm-button="Ya, sudah dibayar" data-confirm-color="#16a34a">@csrf<button class="min-h-11 w-full rounded-xl bg-green-600 px-4 py-2 text-sm font-bold text-white">Tandai Batch Dibayar</button></form>
            @if(auth()->user()?->hasRole('superadmin'))<form method="POST" action="{{ route('supplier-tt-payment-batches.unlock',$batch) }}" data-supplier-tt-action-form data-confirm-title="Buka lock batch?" data-confirm-text="Batch kembali ke Draft dan dapat disesuaikan." data-confirm-button="Ya, unlock" data-confirm-color="#dc2626">@csrf<button class="min-h-11 w-full rounded-xl border border-red-200 px-4 py-2 text-sm font-bold text-red-700">Unlock Batch</button></form>@endif
        @endif
    </div>

    <section class="space-y-4">
        <div><h2 class="text-lg font-bold text-gray-900">Rincian per Supplier</h2><p class="mt-1 text-sm text-gray-500">Faktur dikelompokkan agar subtotal bruto, potongan, dan pembayaran mudah diperiksa.</p></div>
        @foreach($groups as $supplierInvoices)
            @php
                $supplierGross = $supplierInvoices->sum(fn ($invoice) => (float) $invoice->gross_nominal);
                $supplierDeduction = $supplierInvoices->sum(fn ($invoice) => (float) $invoice->deduction_total);
                $supplierNet = $supplierInvoices->sum(fn ($invoice) => (float) $invoice->pivot->nominal_snapshot);
                $account = $accountSnapshots->get($supplierInvoices->first()->supplier_id);
            @endphp
            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-gray-100 bg-gray-50 px-4 py-3 sm:flex-row sm:items-start sm:justify-between"><div><p class="font-bold text-gray-900">{{ $supplierInvoices->first()->supplier?->name ?: 'Supplier tidak diketahui' }}</p><p class="mt-0.5 text-xs text-gray-500">{{ $supplierInvoices->count() }} faktur</p><div class="mt-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs text-gray-600"><span class="font-semibold text-gray-700">Rekening:</span> @if($account?->bank_account_number){{ $account->bank_name ?: 'Bank tidak dicatat' }} · {{ $account->bank_account_number }}@if($account->bank_account_holder) · a.n. {{ $account->bank_account_holder }}@endif @else - @endif</div></div><div class="text-sm sm:text-right"><span class="text-gray-500">Subtotal:</span> <strong class="text-[#287c79]">Rp {{ number_format($supplierNet,0,',','.') }}</strong></div></div>
                <div class="divide-y divide-gray-100">
                    @foreach($supplierInvoices as $invoice)
                        <a href="{{ route('supplier-tt-invoices.show',$invoice) }}" class="block p-4 hover:bg-gray-50"><div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><p class="break-words text-sm font-bold text-gray-900">{{ $invoice->invoice_number }}</p><p class="mt-1 text-xs text-gray-500">Jatuh tempo {{ $invoice->due_date?->format('d M Y') }}</p></div><div class="grid grid-cols-3 gap-3 text-xs sm:min-w-[390px] sm:text-right"><div><p class="text-gray-500">Bruto</p><p class="mt-1 font-semibold text-gray-800">Rp {{ number_format((float)$invoice->gross_nominal,0,',','.') }}</p></div><div><p class="text-red-500">Potongan</p><p class="mt-1 font-semibold text-red-600">{{ (float)$invoice->deduction_total > 0 ? '- Rp '.number_format((float)$invoice->deduction_total,0,',','.') : 'Rp 0' }}</p></div><div><p class="text-[#287c79]">Net</p><p class="mt-1 font-bold text-[#287c79]">Rp {{ number_format((float)$invoice->pivot->nominal_snapshot,0,',','.') }}</p></div></div></div></a>
                    @endforeach
                </div>
                <div class="grid grid-cols-3 gap-2 border-t border-gray-100 bg-teal-50/50 px-4 py-3 text-xs sm:text-right"><div><p class="text-gray-500">Bruto Supplier</p><p class="font-bold text-gray-800">Rp {{ number_format($supplierGross,0,',','.') }}</p></div><div><p class="text-red-500">Potongan Supplier</p><p class="font-bold text-red-600">- Rp {{ number_format($supplierDeduction,0,',','.') }}</p></div><div><p class="text-[#287c79]">Net Supplier</p><p class="font-bold text-[#287c79]">Rp {{ number_format($supplierNet,0,',','.') }}</p></div></div>
            </article>
        @endforeach
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm"><h2 class="font-bold text-gray-900">Catatan Keseluruhan</h2><p class="mt-2 whitespace-pre-line text-sm text-gray-600">{{ $batch->notes ?: '-' }}</p>@if($batch->locked_at)<p class="mt-4 rounded-xl bg-amber-50 p-3 text-xs text-amber-700"><i class="ri-lock-line mr-1"></i>Dikunci {{ $batch->locked_at->format('d M Y H:i') }} oleh {{ $batch->locker?->name ?: '-' }}</p>@endif</section>
</div>
@endsection
