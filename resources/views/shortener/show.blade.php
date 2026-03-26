@extends('layout.layout')

@section('title', 'Detail Short Link')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        @if ($shortUrl->is_active)
                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                Active
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">
                                Inactive
                            </span>
                        @endif

                        @if ($shortUrl->is_expired)
                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                Expired
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl font-bold text-slate-800">
                        {{ $shortUrl->title ?: 'Detail Short Link' }}
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Informasi link publik, QR code, dan analytics kunjungan.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('shortener.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Daftar
                    </a>

                    <a
                        href="{{ route('shortener.edit', $shortUrl) }}"
                        class="inline-flex items-center justify-center rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-sm font-medium text-amber-700 transition hover:bg-amber-50"
                    >
                        Edit
                    </a>

                    <form method="POST" action="{{ route('shortener.destroy', $shortUrl) }}" data-delete-short-url>
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl border border-rose-300 bg-white px-4 py-2.5 text-sm font-medium text-rose-700 transition hover:bg-rose-50"
                        >
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @include('shortener.partials.stats-cards', ['stats' => $stats])

        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold text-slate-800">Informasi Link</h2>
                    </div>

                    <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 p-4 md:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Public URL</dt>
                            <dd class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <a
                                    href="{{ $shortUrl->public_url }}"
                                    target="_blank"
                                    class="break-all text-sm font-medium text-sky-700 underline"
                                >
                                    {{ $shortUrl->public_url }}
                                </a>

                                <button
                                    type="button"
                                    data-copy-text="{{ $shortUrl->public_url }}"
                                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                >
                                    Copy Link
                                </button>
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4 md:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Original URL</dt>
                            <dd class="mt-2 break-all text-sm text-slate-700">
                                <a href="{{ $shortUrl->original_url }}" target="_blank" class="underline">
                                    {{ $shortUrl->original_url }}
                                </a>
                            </dd>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Route Code</dt>
                            <dd class="mt-2 text-sm font-medium text-slate-700">{{ $shortUrl->route_code }}</dd>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Short Code Internal</dt>
                            <dd class="mt-2 text-sm font-medium text-slate-700">{{ $shortUrl->short_code }}</dd>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Custom Slug</dt>
                            <dd class="mt-2 text-sm font-medium text-slate-700">{{ $shortUrl->custom_slug ?: '-' }}</dd>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Expired At</dt>
                            <dd class="mt-2 text-sm font-medium text-slate-700">
                                {{ $shortUrl->expires_at ? $shortUrl->expires_at->format('d M Y H:i') : '-' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold text-slate-800">Recent Visits</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    <th class="px-4 py-3">Waktu</th>
                                    <th class="px-4 py-3">IP</th>
                                    <th class="px-4 py-3">Referer</th>
                                    <th class="px-4 py-3">Source</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($recentVisits as $visit)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-slate-700">{{ $visit->created_at?->format('d M Y H:i') }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-700">{{ $visit->ip_address ?: '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-slate-600">
                                            <div class="max-w-xs break-all">{{ $visit->referer ?: '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-700">{{ $visit->source_app ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">
                                            Belum ada data kunjungan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                @include('shortener.partials.qr-card', ['shortUrl' => $shortUrl])
            </div>
        </div>
    </div>
</div>
@endsection
@endpush