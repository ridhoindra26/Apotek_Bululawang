@extends('layout.layout')

@section('title', 'Short Link Manager')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Short Link Manager</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Kelola short link publik, QR code, dan akses ke halaman analytics tiap link.
                </p>
            </div>

            <a
                href="{{ route('shortener.create') }}"
                class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700"
            >
                + Buat Short Link
            </a>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('shortener.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="md:col-span-10">
                    <label for="search" class="mb-1 block text-sm font-medium text-slate-700">Cari</label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Cari judul, original URL, short code, atau custom slug"
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-700 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
                    >
                </div>

                <div class="flex items-end gap-2 md:col-span-2">
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-900"
                    >
                        Cari
                    </button>

                    @if ($search)
                        <a
                            href="{{ route('shortener.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="hidden overflow-x-auto lg:block">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100/80">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                            <th class="px-4 py-3">Info</th>
                            <th class="px-4 py-3">Public URL</th>
                            <th class="px-4 py-3">QR</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Klik</th>
                            <th class="px-4 py-3">Expired</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($shortUrls as $item)
                            <tr class="align-top">
                                <td class="px-4 py-4">
                                    <div class="space-y-1">
                                        <div class="font-semibold text-slate-800">
                                            {{ $item->title ?: '-' }}
                                        </div>

                                        <div class="max-w-md break-all text-sm text-slate-500">
                                            {{ $item->original_url }}
                                        </div>

                                        <div class="text-xs text-slate-400">
                                            Dibuat {{ $item->created_at?->format('d M Y H:i') }}
                                        </div>

                                        <div class="text-xs text-slate-400">
                                            Route code: <span class="font-medium text-slate-600">{{ $item->route_code }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="space-y-2">
                                        <a
                                            href="{{ $item->public_url }}"
                                            target="_blank"
                                            class="break-all text-sm font-medium text-sky-700 underline"
                                        >
                                            {{ $item->public_url }}
                                        </a>

                                        <button
                                            type="button"
                                            data-copy-text="{{ $item->public_url }}"
                                            class="inline-flex rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50"
                                        >
                                            Copy Link
                                        </button>
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    @if ($item->qr_code_url)
                                        <div class="flex flex-col gap-2">
                                            <img
                                                src="{{ $item->qr_code_url }}"
                                                alt="QR {{ $item->route_code }}"
                                                class="h-20 w-20 rounded-lg border border-slate-200 bg-white p-1 object-contain"
                                            >

                                            <a
                                                href="{{ $item->qr_code_url }}"
                                                download
                                                class="text-xs font-medium text-sky-700 underline"
                                            >
                                                Download QR
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-400">Belum ada QR</span>
                                    @endif
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex flex-col gap-2">
                                        @if ($item->is_active)
                                            <span class="inline-flex w-fit rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex w-fit rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">
                                                Inactive
                                            </span>
                                        @endif

                                        @if ($item->is_expired)
                                            <span class="inline-flex w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                                Expired
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="text-sm font-semibold text-slate-700">
                                        {{ number_format($item->click_count) }}
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="text-sm text-slate-600">
                                        {{ $item->expires_at ? $item->expires_at->format('d M Y H:i') : '-' }}
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('shortener.show', $item) }}"
                                            class="rounded-xl border border-sky-300 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-50"
                                        >
                                            Detail
                                        </a>

                                        <a
                                            href="{{ route('shortener.edit', $item) }}"
                                            class="rounded-xl border border-amber-300 px-3 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-50"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('shortener.destroy', $item) }}"
                                            data-delete-short-url
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="rounded-xl border border-rose-300 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">
                                    Belum ada data short link.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 p-4 lg:hidden">
                @forelse ($shortUrls as $item)
                    <div class="rounded-2xl border border-slate-200 p-4 shadow-sm">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div>
                                <div class="text-base font-semibold text-slate-800">
                                    {{ $item->title ?: '-' }}
                                </div>
                                <div class="mt-1 text-xs text-slate-400">
                                    Dibuat {{ $item->created_at?->format('d M Y H:i') }}
                                </div>
                            </div>

                            <div class="flex flex-col gap-1">
                                @if ($item->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Active</span>
                                @else
                                    <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Inactive</span>
                                @endif

                                @if ($item->is_expired)
                                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Expired</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3 break-all text-sm text-slate-500">
                            {{ $item->original_url }}
                        </div>

                        <div class="mb-3">
                            <a
                                href="{{ $item->public_url }}"
                                target="_blank"
                                class="break-all text-sm font-medium text-sky-700 underline"
                            >
                                {{ $item->public_url }}
                            </a>
                        </div>

                        <div class="mb-3 flex items-center gap-3">
                            @if ($item->qr_code_url)
                                <img
                                    src="{{ $item->qr_code_url }}"
                                    alt="QR {{ $item->route_code }}"
                                    class="h-20 w-20 rounded-lg border border-slate-200 bg-white p-1 object-contain"
                                >
                            @endif

                            <div class="space-y-1 text-sm text-slate-600">
                                <div><span class="font-semibold">Code:</span> {{ $item->route_code }}</div>
                                <div><span class="font-semibold">Klik:</span> {{ number_format($item->click_count) }}</div>
                                <div><span class="font-semibold">Expired:</span> {{ $item->expires_at ? $item->expires_at->format('d M Y H:i') : '-' }}</div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                data-copy-text="{{ $item->public_url }}"
                                class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700"
                            >
                                Copy Link
                            </button>

                            <a
                                href="{{ route('shortener.show', $item) }}"
                                class="rounded-xl border border-sky-300 px-3 py-2 text-xs font-semibold text-sky-700"
                            >
                                Detail
                            </a>

                            <a
                                href="{{ route('shortener.edit', $item) }}"
                                class="rounded-xl border border-amber-300 px-3 py-2 text-xs font-semibold text-amber-700"
                            >
                                Edit
                            </a>

                            <form
                                method="POST"
                                action="{{ route('shortener.destroy', $item) }}"
                                data-delete-short-url
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="rounded-xl border border-rose-300 px-3 py-2 text-xs font-semibold text-rose-700"
                                >
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-10 text-center text-sm text-slate-500">
                        Belum ada data short link.
                    </div>
                @endforelse
            </div>

            @if ($shortUrls->hasPages())
                <div class="border-t border-slate-200 px-4 py-4">
                    {{ $shortUrls->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/shortener/index.js')
@endpush