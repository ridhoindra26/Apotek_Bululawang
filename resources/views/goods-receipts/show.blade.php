@php
    $isGuest = auth()->guest();
    $layout = $isGuest ? 'layout.guest' : 'layout.layout';
    $canReview = auth()->check() && in_array(auth()->user()->role ?? '', ['admin', 'superadmin'], true);
    $canDelete = auth()->check() && (auth()->user()->role ?? '') === 'superadmin';
@endphp

@extends($layout)

@section('title', 'Detail Penerimaan Barang')
@section('page_title', 'Detail Penerimaan Barang')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('goods-receipts.index') }}"
               class="text-sm font-medium text-blue-600 hover:text-blue-700">
                ← Kembali ke Penerimaan Barang
            </a>

            <h1 class="mt-3 text-2xl font-bold text-gray-900">
                Detail Penerimaan Barang
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Arsip penerimaan barang, faktur, evaluasi, dan persetujuan.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if (in_array($goodsReceipt->status, ['draft']))
                <a href="{{ route('goods-receipts.edit', $goodsReceipt) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600">
                    Edit
                </a>
            @endif

            @if ($goodsReceipt->status === 'draft')
                <form method="POST" action="{{ route('goods-receipts.submit-approval', $goodsReceipt) }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-600"
                    >
                        Ajukan Approval
                    </button>
                </form>
            @endif

            @if ($goodsReceipt->status === 'waiting_approval')
                <form method="POST" action="{{ route('goods-receipts.draft', $goodsReceipt) }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700"
                    >
                        Draft
                    </button>
                </form>

                @if ($canReview)
                <form method="POST" action="{{ route('goods-receipts.approve', $goodsReceipt) }}" data-approve-form>
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700"
                    >
                        Approve
                    </button>
                </form>

                <form method="POST" action="{{ route('goods-receipts.reject', $goodsReceipt) }}" data-reject-form>
                    @csrf
                    <input type="hidden" name="evaluation_note" value="">

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700"
                    >
                        Reject
                    </button>
                </form>
                @endif
            @endif

            @if ($canReview && in_array($goodsReceipt->status, ['waiting_approval', 'approved', 'rejected'], true))
                <form method="POST" action="{{ route('goods-receipts.cancel', $goodsReceipt) }}" data-cancel-form>
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                </form>
            @endif

            @if ($canDelete)
                <form method="POST" action="{{ route('goods-receipts.destroy', $goodsReceipt) }}" data-goods-receipt-delete-form>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
                        Hapus
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Status Cards --}}
    <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Status
            </div>
            <div class="mt-2">
                @include('goods-receipts.partials.status-badge', [
                    'status' => $goodsReceipt->status,
                    'label' => $goodsReceipt->status_label,
                ])
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Evaluasi
            </div>
            <div class="mt-2">
                @include('goods-receipts.partials.evaluation-badge', [
                    'status' => $goodsReceipt->evaluation_status,
                    'label' => $goodsReceipt->evaluation_status_label,
                ])
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Jumlah Faktur
            </div>
            <div class="mt-1 text-2xl font-bold text-gray-900">
                {{ $goodsReceipt->invoices->count() }}
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                Total Nominal
            </div>
            <div class="mt-1 text-2xl font-bold text-gray-900">
                Rp {{ number_format($goodsReceipt->total_nominal, 0, ',', '.') }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

        {{-- Main --}}
        <div class="space-y-5 lg:col-span-2">

            {{-- Informasi Penerimaan --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">
                    Informasi Penerimaan
                </h2>

                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Tanggal Datang
                        </dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $goodsReceipt->received_date?->format('d M Y') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Supplier / PBF
                        </dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">
                            {{ $goodsReceipt->supplier?->name ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            No Referensi SP
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $goodsReceipt->sp_reference_number ?: '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Penerima
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $goodsReceipt->receiver?->name ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Pengirim / Kurir
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $goodsReceipt->sender_name ?: '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Dibuat
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $goodsReceipt->created_at?->format('d M Y H:i') }}
                        </dd>
                    </div>

                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Keterangan Umum
                        </dt>
                        <dd class="mt-1 whitespace-pre-line text-sm text-gray-900">
                            {{ $goodsReceipt->notes ?: '-' }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Daftar Faktur --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            Daftar Faktur
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Faktur dan halaman foto yang sudah diupload.
                        </p>
                    </div>
                </div>

                <div class="space-y-5">
                    @forelse ($goodsReceipt->invoices as $invoice)
                        <div class="rounded-xl border border-gray-200 p-4">
                            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">
                                        {{ $invoice->invoice_number ?: 'Faktur Tanpa Nomor' }}
                                    </h3>

                                    <div class="mt-1 flex flex-wrap gap-2 text-xs text-gray-500">
                                        <span>
                                            Tanggal:
                                            <strong class="text-gray-700">
                                                {{ $invoice->invoice_date?->format('d M Y') ?: '-' }}
                                            </strong>
                                        </span>

                                        <span>·</span>

                                        <span>
                                            Nominal:
                                            <strong class="text-gray-700">
                                                Rp {{ number_format($invoice->nominal, 0, ',', '.') }}
                                            </strong>
                                        </span>

                                        <span>·</span>

                                        <span>
                                            Halaman:
                                            <strong class="text-gray-700">
                                                {{ $invoice->pages->count() }}
                                            </strong>
                                        </span>
                                    </div>
                                </div>

                                @include('goods-receipts.partials.evaluation-badge', [
                                    'status' => $invoice->evaluation_status,
                                    'label' => $invoice->evaluation_status_label,
                                ])
                            </div>

                            @if ($invoice->evaluation_note)
                                <div class="mb-4 rounded-lg bg-gray-50 p-3">
                                    <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
                                        Catatan Evaluasi Faktur
                                    </div>
                                    <div class="mt-1 whitespace-pre-line text-sm text-gray-800">
                                        {{ $invoice->evaluation_note }}
                                    </div>
                                </div>
                            @endif

                            @if ($invoice->pages->count())
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                                    @foreach ($invoice->pages as $page)
                                        <a href="{{ $page->url }}"
                                           target="_blank"
                                           class="group overflow-hidden rounded-xl border border-gray-200 bg-white hover:border-blue-300">
                                            <div class="aspect-[3/4] bg-gray-100">
                                                <img
                                                    src="{{ $page->url }}"
                                                    alt="{{ $page->page_label }}"
                                                    class="h-full w-full object-cover transition group-hover:scale-105"
                                                >
                                            </div>

                                            <div class="p-3">
                                                <div class="text-xs font-semibold text-gray-900">
                                                    {{ $page->page_label }}
                                                </div>

                                                <div class="mt-0.5 truncate text-xs text-gray-500">
                                                    {{ $page->original_name ?: '-' }}
                                                </div>

                                                @if ($page->size)
                                                    <div class="mt-0.5 text-[11px] text-gray-400">
                                                        {{ number_format($page->size / 1024, 1) }} KB
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-lg bg-amber-50 p-3 text-sm text-amber-700">
                                    Faktur ini belum memiliki foto halaman.
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-lg bg-gray-50 p-5 text-center text-sm text-gray-500">
                            Belum ada faktur.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">

            {{-- Evaluasi Umum --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">
                    Catatan Evaluasi
                </h2>

                <div class="mt-4 whitespace-pre-line text-sm text-gray-800">
                    {{ $goodsReceipt->evaluation_note ?: 'Tidak ada catatan evaluasi umum.' }}
                </div>
            </div>

            {{-- Signatures --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">
                    Digital Signature
                </h2>

                <div class="mt-4 space-y-4">
                    @php
                        $senderSignature = $goodsReceipt->signatures
                    @endphp

                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="mb-2 text-sm font-semibold text-gray-900">
                            Pengirim
                        </div>

                        @if ($senderSignature?->signature_url)
                            <div class="rounded-lg bg-gray-50 p-3">
                                <img
                                    src="{{ $senderSignature->signature_url }}"
                                    alt="Tanda tangan pengirim"
                                    class="mx-auto max-h-28"
                                >
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                {{ $senderSignature->name ?: $goodsReceipt->sender_name ?: '-' }}
                                @if ($senderSignature->signed_at)
                                    · {{ $senderSignature->signed_at->format('d M Y H:i') }}
                                @endif
                            </div>
                        @else
                            <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500">
                                Belum ada tanda tangan pengirim.
                            </div>
                        @endif
                    </div>

                    {{-- <div class="rounded-xl border border-gray-200 p-4">
                        <div class="mb-2 text-sm font-semibold text-gray-900">
                            Penerima
                        </div>

                        @if ($receiverSignature?->signature_url)
                            <div class="rounded-lg bg-gray-50 p-3">
                                <img
                                    src="{{ $receiverSignature->signature_url }}"
                                    alt="Tanda tangan penerima"
                                    class="mx-auto max-h-28"
                                >
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                {{ $receiverSignature->name ?: $goodsReceipt->receiver?->name ?: '-' }}
                                @if ($receiverSignature->signed_at)
                                    · {{ $receiverSignature->signed_at->format('d M Y H:i') }}
                                @endif
                            </div>
                        @else
                            <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-500">
                                Belum ada tanda tangan penerima.
                            </div>
                        @endif
                    </div> --}}
                </div>
            </div>

            {{-- Approval Info --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">
                    Approval
                </h2>

                <dl class="mt-4 space-y-3">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Oleh
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $goodsReceipt->checker?->name ?? '-' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">
                            Waktu Approval / Rejection
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $goodsReceipt->checked_at?->format('d M Y H:i') ?? '-' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>

<script>
    window.goodsReceiptFlash = {
        success: @json(session('success')),
        error: @json(session('error')),
    }
</script>
@endsection
