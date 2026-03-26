<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-start justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">QR Code</h2>
            <p class="mt-1 text-sm text-slate-500">
                Gunakan QR ini untuk promosi cetak, banner, atau materi digital.
            </p>
        </div>

        @if ($shortUrl->qr_code_url)
            <a
                href="{{ $shortUrl->qr_code_url }}"
                download
                class="inline-flex items-center justify-center rounded-xl border border-sky-300 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:bg-sky-50"
            >
                Download QR
            </a>
        @endif
    </div>

    @if ($shortUrl->qr_code_url)
        <div class="flex flex-col items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-5">
            <img
                src="{{ $shortUrl->qr_code_url }}"
                alt="QR {{ $shortUrl->route_code }}"
                class="h-64 w-64 rounded-xl border border-slate-200 bg-white p-2 object-contain"
                data-qr-preview-image
            >

            <button
                type="button"
                data-open-qr-preview
                data-qr-url="{{ $shortUrl->qr_code_url }}"
                data-qr-title="{{ $shortUrl->title ?: $shortUrl->route_code }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            >
                Preview Lebih Besar
            </button>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-10 text-center text-sm text-slate-500">
            QR code belum tersedia.
        </div>
    @endif
</div>