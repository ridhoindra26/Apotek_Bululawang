@extends('layout.layout')

@section('title', 'Detail Faktur TT')
@section('page_title', 'Faktur TT')

@section('content')
@php
    $statusClass = match ($supplierTtInvoice->status) {
        'verified' => 'bg-green-50 text-green-700 ring-green-600/20',
        'scheduled_payment' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'paid' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'cancelled' => 'bg-red-50 text-red-700 ring-red-600/20',
        default => 'bg-gray-50 text-gray-700 ring-gray-600/20',
    };
    $dueClass = match ($supplierTtInvoice->due_status) {
        'overdue' => 'bg-red-50 text-red-700 ring-red-600/20',
        'due_today' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
        'due_soon' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'paid' => 'bg-green-50 text-green-700 ring-green-600/20',
        default => 'bg-gray-50 text-gray-600 ring-gray-500/20',
    };
    $activeBatch = $supplierTtInvoice->paymentBatch->first();
    $isBatchLocked = $activeBatch?->status === 'locked';
@endphp

<div class="mx-auto max-w-7xl space-y-4 px-4 pb-10 sm:px-6 lg:px-8">
    <header class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
        <a href="{{ route('supplier-tt-invoices.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-[#318f8c] hover:text-[#287c79]">
            <i class="ri-arrow-left-line"></i> Kembali ke Faktur TT
        </a>

        <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <p class="text-sm text-gray-500">Nomor faktur</p>
                <h1 class="mt-1 break-words text-2xl font-bold text-gray-900 sm:text-3xl">{{ $supplierTtInvoice->invoice_number }}</h1>
                <p class="mt-2 text-sm font-medium text-gray-600">{{ $supplierTtInvoice->supplier?->name ?: '-' }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClass }}">{{ $supplierTtInvoice->status_label }}</span>
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $dueClass }}">{{ $supplierTtInvoice->due_status_label }}</span>
                </div>
                @if($activeBatch)
                    <a href="{{ route('supplier-tt-payment-batches.show', $activeBatch) }}" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-blue-700">
                        <i class="ri-secure-payment-line"></i> {{ $activeBatch->batch_number }}{{ $isBatchLocked ? ' (Dikunci)' : '' }}
                    </a>
                @endif
            </div>

            <div class="grid w-full grid-cols-2 gap-2 lg:w-auto lg:flex lg:flex-wrap lg:justify-end">
                @if ($supplierTtInvoice->isEditable())
                    <a href="{{ route('supplier-tt-invoices.edit', $supplierTtInvoice) }}" class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-xl border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <i class="ri-edit-line"></i> Edit
                    </a>
                @elseif ($supplierTtInvoice->status === 'scheduled_payment' && (!$isBatchLocked || auth()->user()?->hasRole('superadmin')))
                    <form method="POST" action="{{ route('supplier-tt-invoices.prepare-edit', $supplierTtInvoice) }}" data-supplier-tt-action-form data-confirm-title="Keluarkan faktur dari batch?" data-confirm-text="Faktur akan dikeluarkan dari batch, dikembalikan ke status Terverifikasi, lalu dibuka pada form edit." data-confirm-button="Ya, keluarkan & edit">
                        @csrf
                        <button class="inline-flex min-h-10 w-full items-center justify-center gap-1.5 rounded-xl border border-blue-200 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50"><i class="ri-edit-line"></i> Keluarkan & Edit</button>
                    </form>
                @endif

                @if ($supplierTtInvoice->status === 'draft')
                    <form method="POST" action="{{ route('supplier-tt-invoices.verify', $supplierTtInvoice) }}" data-supplier-tt-action-form data-confirm-title="Verifikasi faktur?" data-confirm-text="Faktur akan siap dipilih ke batch pembayaran.">
                        @csrf
                        <button class="inline-flex min-h-10 w-full items-center justify-center gap-1.5 rounded-xl bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700"><i class="ri-checkbox-circle-line"></i> Verifikasi</button>
                    </form>
                @endif

                @if ($supplierTtInvoice->status === 'verified')
                    <form method="POST" action="{{ route('supplier-tt-invoices.draft', $supplierTtInvoice) }}" data-supplier-tt-action-form data-confirm-title="Kembalikan ke Draft?" data-confirm-text="Faktur harus diverifikasi kembali sebelum masuk batch.">
                        @csrf
                        <button class="inline-flex min-h-10 w-full items-center justify-center gap-1.5 rounded-xl border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"><i class="ri-draft-line"></i> Ke Draft</button>
                    </form>
                @endif

                @if (!in_array($supplierTtInvoice->status, ['paid', 'cancelled']) && (!$isBatchLocked || auth()->user()?->hasRole('superadmin')))
                    <form method="POST" action="{{ route('supplier-tt-invoices.cancel', $supplierTtInvoice) }}" data-supplier-tt-action-form data-confirm-title="Batalkan Faktur TT?" data-confirm-text="Faktur yang dibatalkan tidak dapat diedit dan tidak dihitung sebagai tagihan aktif." data-confirm-button="Ya, batalkan" data-confirm-icon="warning" data-confirm-color="#dc2626">
                        @csrf
                        <button class="inline-flex min-h-10 w-full items-center justify-center gap-1.5 rounded-xl border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"><i class="ri-close-circle-line"></i> Batalkan</button>
                    </form>
                @endif
            </div>
        </div>
    </header>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nominal Bruto</p>
            <p class="mt-2 text-xl font-bold text-gray-900">Rp {{ number_format((float) $supplierTtInvoice->gross_nominal, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl border border-red-100 bg-red-50/50 p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-600">Total Potongan</p>
            <p class="mt-2 text-xl font-bold text-red-700">- Rp {{ number_format((float) $supplierTtInvoice->deduction_total, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl border border-teal-100 bg-teal-50/60 p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-[#287c79]">Net Dibayar</p>
            <p class="mt-2 text-xl font-bold text-[#287c79]">Rp {{ number_format((float) $supplierTtInvoice->net_nominal, 0, ',', '.') }}</p>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
                <h2 class="text-base font-bold text-gray-900">Informasi Faktur</h2>
                <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div><dt class="text-xs font-medium text-gray-500">Supplier</dt><dd class="mt-1 break-words text-sm font-semibold text-gray-900">{{ $supplierTtInvoice->supplier?->name ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Cabang Asal</dt><dd class="mt-1 break-words text-sm font-semibold text-gray-900">{{ $supplierTtInvoice->branch?->name ?: 'Belum dipilih' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Goods Receipt</dt><dd class="mt-1 text-sm font-semibold text-gray-900">{{ $supplierTtInvoice->goods_receipt_id ? 'GR #'.$supplierTtInvoice->goods_receipt_id : 'Tidak terhubung' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Referensi SP</dt><dd class="mt-1 break-words text-sm font-semibold text-gray-900">{{ $supplierTtInvoice->goodsReceipt?->sp_reference_number ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Tanggal Faktur</dt><dd class="mt-1 text-sm font-semibold text-gray-900">{{ $supplierTtInvoice->invoice_date?->format('d M Y') ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Tanggal Diterima</dt><dd class="mt-1 text-sm font-semibold text-gray-900">{{ $supplierTtInvoice->received_date?->format('d M Y') ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Jatuh Tempo</dt><dd class="mt-1 text-sm font-semibold text-gray-900">{{ $supplierTtInvoice->due_date?->format('d M Y') ?: '-' }}</dd></div>
                </dl>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-3"><h2 class="text-base font-bold text-gray-900">Potongan</h2><span class="text-xs font-medium text-gray-500">{{ $supplierTtInvoice->deductions->count() }} item</span></div>
                @forelse ($supplierTtInvoice->deductions as $deduction)
                    <div class="mt-3 rounded-xl border border-gray-100 bg-gray-50 p-3 sm:flex sm:items-start sm:justify-between sm:gap-4">
                        <div class="min-w-0"><p class="text-sm font-semibold text-gray-900">{{ $deduction->type_label }}</p><p class="mt-1 break-words text-xs text-gray-500">{{ $deduction->description ?: 'Tanpa keterangan' }}{{ $deduction->reference_number ? ' · Ref: '.$deduction->reference_number : '' }}</p></div>
                        <p class="mt-2 shrink-0 text-sm font-bold text-red-600 sm:mt-0">- Rp {{ number_format((float) $deduction->amount, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <div class="mt-4 rounded-xl border border-dashed border-gray-200 p-5 text-center text-sm text-gray-500">Tidak ada potongan.</div>
                @endforelse
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-3"><h2 class="text-base font-bold text-gray-900">Foto Faktur</h2><span class="text-xs font-medium text-gray-500">{{ $supplierTtInvoice->photos->count() }} foto</span></div>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @forelse ($supplierTtInvoice->photos as $photo)
                        <a href="{{ $photo->url }}" target="_blank" rel="noopener" class="group overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                            <img src="{{ $photo->url }}" alt="{{ $photo->page_label }}" class="aspect-[4/3] w-full object-cover transition group-hover:scale-105">
                            <div class="truncate px-3 py-2 text-xs font-medium text-gray-600">{{ $photo->original_name ?: $photo->page_label }}</div>
                        </a>
                    @empty
                        <p class="col-span-full rounded-xl border border-dashed border-gray-200 p-5 text-center text-sm text-gray-500">Tidak ada foto.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="space-y-4">
            <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="text-base font-bold text-gray-900">Catatan</h2>
                <div class="mt-4 space-y-4 text-sm">
                    <div><p class="text-xs font-medium text-gray-500">Catatan Faktur</p><p class="mt-1 whitespace-pre-line break-words text-gray-800">{{ $supplierTtInvoice->notes ?: '-' }}</p></div>
                    <div><p class="text-xs font-medium text-gray-500">Catatan Verifikasi</p><p class="mt-1 whitespace-pre-line break-words text-gray-800">{{ $supplierTtInvoice->verification_note ?: '-' }}</p></div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <h2 class="text-base font-bold text-gray-900">Riwayat Proses</h2>
                <dl class="mt-4 space-y-4">
                    <div><dt class="text-xs font-medium text-gray-500">Dibuat</dt><dd class="mt-1 text-sm font-semibold text-gray-800">{{ $supplierTtInvoice->created_at?->format('d M Y, H:i') ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Diverifikasi</dt><dd class="mt-1 text-sm font-semibold text-gray-800">{{ $supplierTtInvoice->verified_at?->format('d M Y, H:i') ?: '-' }}</dd><dd class="text-xs text-gray-500">{{ $supplierTtInvoice->verifier?->name ?: '' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500">Dibayar</dt><dd class="mt-1 text-sm font-semibold text-gray-800">{{ $supplierTtInvoice->paid_at?->format('d M Y, H:i') ?: '-' }}</dd><dd class="text-xs text-gray-500">{{ $supplierTtInvoice->payer?->name ?: '' }}</dd></div>
                </dl>
            </section>

            @if (auth()->user()?->hasRole('superadmin') && $supplierTtInvoice->isDeletable())
                <form method="POST" action="{{ route('supplier-tt-invoices.destroy', $supplierTtInvoice) }}" data-supplier-tt-delete-form>
                    @csrf
                    @method('DELETE')
                    <button class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"><i class="ri-delete-bin-line"></i> Hapus Faktur TT</button>
                </form>
            @endif
        </aside>
    </div>
</div>
@endsection
