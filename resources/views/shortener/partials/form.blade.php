@php
    $isEdit = isset($shortUrl);
    $publicDomain = rtrim(config('shortener.public_domain'), '/');
@endphp

<div class="grid grid-cols-1 gap-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-slate-800">Informasi Link</h2>
            <p class="mt-1 text-sm text-slate-500">
                Isi URL tujuan dan atur slug custom bila diperlukan.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-5">
            <div>
                <label for="title" class="mb-1.5 block text-sm font-medium text-slate-700">
                    Judul
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title', $shortUrl->title ?? '') }}"
                    placeholder="Contoh: Promo Vitamin Mei"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                >
                @error('title')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="original_url" class="mb-1.5 block text-sm font-medium text-slate-700">
                    Original URL <span class="text-rose-500">*</span>
                </label>
                <input
                    type="url"
                    id="original_url"
                    name="original_url"
                    value="{{ old('original_url', $shortUrl->original_url ?? '') }}"
                    placeholder="https://example.com/promo"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                >
                @error('original_url')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="custom_slug" class="mb-1.5 block text-sm font-medium text-slate-700">
                    Custom Slug
                </label>

                <div class="overflow-hidden rounded-xl border border-slate-300 focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-100">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-xs text-slate-500">
                        {{ $publicDomain }}/s/
                    </div>
                    <input
                        type="text"
                        id="custom_slug"
                        name="custom_slug"
                        value="{{ old('custom_slug', $shortUrl->custom_slug ?? '') }}"
                        placeholder="promo-vitamin"
                        data-shortener-slug
                        class="w-full border-0 px-4 py-2.5 text-sm text-slate-700 outline-none focus:ring-0"
                    >
                </div>

                <div class="mt-2 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    <span class="font-medium text-slate-700">Preview:</span>
                    <span
                        data-shortener-preview
                        data-base-url="{{ $publicDomain }}"
                        class="ml-1 break-all text-sky-700"
                    >
                        {{ $publicDomain }}/s/{{ old('custom_slug', $shortUrl->custom_slug ?? '{auto-generated}') ?: '{auto-generated}' }}
                    </span>
                </div>

                <p class="mt-1 text-xs text-slate-400">
                    Kosongkan jika ingin slug dibuat otomatis dari sistem.
                </p>

                @error('custom_slug')
                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="expires_at" class="mb-1.5 block text-sm font-medium text-slate-700">
                        Expired At
                    </label>
                    <input
                        type="datetime-local"
                        id="expires_at"
                        name="expires_at"
                        value="{{ old('expires_at', isset($shortUrl) && $shortUrl->expires_at ? $shortUrl->expires_at->format('Y-m-d\TH:i') : '') }}"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                    >
                    @error('expires_at')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-end">
                    <label class="flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked(old('is_active', isset($shortUrl) ? $shortUrl->is_active : true))
                            class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                        >
                        <span class="text-sm font-medium text-slate-700">Link aktif</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    @if($isEdit)
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-800">Info Saat Ini</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Ringkasan singkat short link yang sedang diedit.
                </p>
            </div>

            <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Public URL</dt>
                    <dd class="mt-2 break-all text-sm text-sky-700">
                        <a href="{{ $shortUrl->public_url }}" target="_blank" class="underline">
                            {{ $shortUrl->public_url }}
                        </a>
                    </dd>
                </div>

                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Route Code</dt>
                    <dd class="mt-2 text-sm font-medium text-slate-700">
                        {{ $shortUrl->route_code }}
                    </dd>
                </div>

                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Clicks</dt>
                    <dd class="mt-2 text-sm font-medium text-slate-700">
                        {{ number_format($shortUrl->click_count) }}
                    </dd>
                </div>

                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</dt>
                    <dd class="mt-2">
                        @if($shortUrl->is_active)
                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                Active
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">
                                Inactive
                            </span>
                        @endif

                        @if($shortUrl->is_expired)
                            <span class="ml-2 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                Expired
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    @endif

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ $isEdit ? route('shortener.show', $shortUrl) : route('shortener.index') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
        >
            Batal
        </a>

        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-700"
        >
            {{ $isEdit ? 'Update Short Link' : 'Simpan Short Link' }}
        </button>
    </div>
</div>