<div class="w-full max-w-lg rounded-2xl border border-white/70 bg-white/90 p-6 text-center shadow-sm backdrop-blur">

    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-50 text-red-600">
        <i class="ri-error-warning-line text-3xl"></i>
    </div>

    <h1 class="text-3xl font-bold text-slate-900">
        {{ $status ?? 'Oops' }}
    </h1>

    <h2 class="mt-2 text-lg font-semibold text-slate-800">
        {{ $title ?? 'Terjadi Kesalahan' }}
    </h2>

    <p class="mt-2 text-sm leading-6 text-slate-600">
        {{ $message ?? 'Sistem tidak dapat memproses permintaan Anda saat ini.' }}
    </p>

    <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
        <button type="button"
                onclick="window.history.back()"
                class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Kembali
        </button>

        @auth
            <a href="{{ route('dashboard') }}"
               class="rounded-lg bg-[#318f8c] px-4 py-2 text-sm font-medium text-white hover:bg-[#267976]">
                Ke Dashboard
            </a>
        @else
            <a href="{{ route('login') }}"
               class="rounded-lg bg-[#318f8c] px-4 py-2 text-sm font-medium text-white hover:bg-[#267976]">
                Ke Login
            </a>
        @endauth
    </div>
</div>