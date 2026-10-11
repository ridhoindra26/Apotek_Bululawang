@extends('layout.layout')
@section('title', 'Batch Pembayaran Faktur TT')
@section('page_title', 'Batch Pembayaran TT')
@section('content')
@php
    $statusStyle = fn ($status) => match ($status) {
        'locked' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        default => 'bg-slate-100 text-slate-700 ring-slate-600/20',
    };
@endphp
<div class="mx-auto max-w-7xl space-y-5 px-4 pb-10 sm:px-6 lg:px-8">
    <header class="overflow-hidden rounded-2xl border border-teal-100 bg-gradient-to-br from-white via-white to-teal-50 p-5 shadow-lg shadow-teal-900/5 sm:flex sm:items-center sm:justify-between sm:p-6">
        <div><h1 class="text-xl font-bold sm:text-2xl">Batch Pembayaran</h1><p class="mt-1 max-w-2xl text-sm text-black">Pantau batch yang masih diproses, terkunci, dan sudah dibayar.</p></div>
        <a href="{{ route('supplier-tt-payment-batches.create') }}" class="mt-4 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-[#318f8c] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#287c79] sm:mt-0 sm:w-auto"><i class="ri-add-circle-line text-lg"></i>Buat Batch</a>
    </header>

    <div class="flex flex-wrap items-center justify-between gap-2">
        <div><h2 class="text-sm font-bold text-gray-900">Ringkasan · {{ $summaryContext }}</h2><p class="mt-0.5 text-xs text-gray-500">{{ $hasActiveFilters ? 'Nilai berikut mengikuti seluruh filter batch yang aktif.' : 'Nilai berikut mencakup seluruh batch pembayaran.' }}</p></div>
        @if($hasActiveFilters)<span class="inline-flex items-center gap-1 rounded-full bg-[#318f8c]/10 px-2.5 py-1 text-xs font-semibold text-[#287c79]"><i class="ri-filter-3-line"></i>Filter aktif</span>@endif
    </div>
    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50 p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-black">Belum Dibayar</p><p class="mt-2 text-2xl font-bold text-black">{{ number_format($summary['unpaid']) }} Batch</p><p class="mt-1 text-xs text-black">Batch Draft dan terkunci</p></div>
        <div class="rounded-2xl border border-red-100 bg-gradient-to-br from-white to-red-50 p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-black">Nominal Belum Dibayar</p><p class="mt-2 text-2xl font-bold text-black">Rp {{ number_format($summary['unpaid_total'],0,',','.') }}</p></div>
        <div class="rounded-2xl border border-green-100 bg-gradient-to-br from-white to-green-50 p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-black">Sudah Dibayar</p><p class="mt-2 text-2xl font-bold text-black">{{ number_format($summary['paid']) }} Batch</p></div>
    </section>

    <details x-data="{ expanded: @js($hasActiveFilters) }" @toggle="expanded = $el.open" class="rounded-2xl border border-gray-200 bg-white shadow-sm" @if($hasActiveFilters) open @endif>
        <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between px-4 py-3 text-sm font-bold text-gray-800 [&::-webkit-details-marker]:hidden"><span class="flex items-center gap-2"><i class="ri-equalizer-line text-lg text-[#318f8c]"></i>Filter Batch</span><i class="text-xl text-gray-400 transition" :class="expanded ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'"></i></summary>
        <form method="GET" class="grid grid-cols-1 gap-3 border-t border-gray-100 p-4 sm:grid-cols-4">
            <input name="search" value="{{ request('search') }}" placeholder="Cari nomor batch..." class="min-h-11 rounded-xl border-gray-300 px-3 text-sm shadow-sm">
            <select name="status" class="min-h-11 rounded-xl border-gray-300 px-3 text-sm shadow-sm"><option value="">Semua status</option><option value="unpaid" @selected(request('status')==='unpaid')>Belum Dibayar</option><option value="draft" @selected(request('status')==='draft')>Draft</option><option value="locked" @selected(request('status')==='locked')>Dikunci</option><option value="paid" @selected(request('status')==='paid')>Dibayar</option></select>
            <select name="branch_id" class="min-h-11 rounded-xl border-gray-300 px-3 text-sm shadow-sm"><option value="">Semua cabang</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(request('branch_id')==$branch->id)>{{ $branch->name }}</option>@endforeach</select>
            <div class="grid grid-cols-2 gap-2"><a href="{{ route('supplier-tt-payment-batches.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-gray-300 text-sm font-bold text-gray-600">Reset</a><button class="min-h-11 rounded-xl bg-[#318f8c] text-sm font-bold text-white">Terapkan</button></div>
        </form>
    </details>

    <div class="space-y-3">
        @forelse($batches as $batch)
            <a href="{{ route('supplier-tt-payment-batches.show', $batch) }}" class="group block overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-[#318f8c]/40 hover:shadow-md">
                <div class="h-1 {{ $batch->status === 'paid' ? 'bg-emerald-500' : ($batch->status === 'locked' ? 'bg-amber-500' : 'bg-slate-400') }}"></div>
                <div class="p-4 sm:p-5"><div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><p class="break-words font-bold text-gray-900">{{ $batch->batch_number }}</p><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $statusStyle($batch->status) }}">{{ $batch->status_label }}</span></div><div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500"><span><i class="ri-store-2-line mr-1"></i>{{ $batch->branch?->name }}</span><span><i class="ri-calendar-line mr-1"></i>{{ $batch->payment_date?->format('d M Y') }}</span><span><i class="ri-file-list-3-line mr-1"></i>{{ $batch->invoices_count }} faktur</span></div></div><div class="shrink-0 sm:text-right"><p class="text-xs font-medium text-gray-500">Total pembayaran</p><p class="mt-1 text-xl font-bold text-[#287c79]">Rp {{ number_format((float)$batch->total_nominal,0,',','.') }}</p></div></div></div>
            </a>
        @empty<div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">Batch tidak ditemukan.</div>@endforelse
    </div>
    {{ $batches->links() }}
</div>
@endsection
