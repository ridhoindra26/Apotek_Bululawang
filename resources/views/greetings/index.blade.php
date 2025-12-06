@extends('layout.layout')

@section('title', 'Greeting Management')
@section('page_title', 'Greeting Management')

@section('content')
{{-- Alpine + x-cloak helper (if not already in main layout) --}}
@push('scripts')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

<style>
    [x-cloak] { display: none !important; }
</style>

<div class="space-y-4">
    {{-- Flash messages --}}
    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-emerald-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-rose-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-3">
        {{-- Left: Greeting Types --}}
        <div class="lg:col-span-1">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-slate-800">Greeting Types</h2>

                {{-- Add Type modal trigger --}}
                <div x-data="{ open: false }" class="relative">
                    <button
                        type="button"
                        class="px-2 py-1.5 text-xs rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                        @click="open = true"
                    >
                        + Type
                    </button>

                    {{-- Add Type Modal --}}
                    <div
                        x-show="open"
                        x-cloak
                        class="fixed inset-0 z-40 flex items-center justify-center"
                    >
                        <div class="fixed inset-0 bg-slate-900/60" @click="open = false"></div>

                        <div class="relative z-50 w-full max-w-sm mx-4 rounded-2xl bg-white shadow-xl border border-slate-200">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                                <h3 class="text-sm font-semibold text-slate-800">Tambah Greeting Type</h3>
                                <button type="button"
                                        class="h-7 w-7 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                        @click="open = false">
                                    ✕
                                </button>
                            </div>

                            <form action="{{ route('greeting-types.store') }}" method="POST" class="px-4 py-4 space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">
                                        Nama Type (contoh: login, dashboard)
                                    </label>
                                    <input type="text" name="name"
                                           class="w-full rounded-lg border-slate-200 text-sm"
                                           required>
                                </div>

                                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                                    <button type="button"
                                            class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50"
                                            @click="open = false">
                                        Batal
                                    </button>
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                                        Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Nama</th>
                            <th class="px-3 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $type)
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2 text-slate-800">
                                    {{ $type->name }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{-- Edit Type modal per-row --}}
                                    <div x-data="{ open: false }" class="inline-block">
                                        <button
                                            type="button"
                                            class="px-2 py-1 text-[11px] rounded border border-slate-200 hover:bg-slate-50"
                                            @click="open = true"
                                        >
                                            Edit
                                        </button>

                                        <div
                                            x-show="open"
                                            x-cloak
                                            class="fixed inset-0 z-40 flex items-center justify-center"
                                        >
                                            <div class="fixed inset-0 bg-slate-900/60" @click="open = false"></div>

                                            <div class="relative z-50 w-full max-w-sm mx-4 rounded-2xl bg-white shadow-xl border border-slate-200">
                                                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                                                    <h3 class="text-sm font-semibold text-slate-800">Edit Greeting Type</h3>
                                                    <button type="button"
                                                            class="h-7 w-7 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                                            @click="open = false">
                                                        ✕
                                                    </button>
                                                </div>

                                                <form action="{{ route('greeting-types.update', $type) }}"
                                                      method="POST"
                                                      class="px-4 py-4 space-y-4">
                                                    @csrf
                                                    @method('POST')

                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-600 mb-1">
                                                            Nama Type
                                                        </label>
                                                        <input type="text" name="name"
                                                               value="{{ $type->name }}"
                                                               class="w-full rounded-lg border-slate-200 text-sm"
                                                               required>
                                                    </div>

                                                    <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                                                        <button type="button"
                                                                class="px-3 py-1.5 text-[11px] rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50"
                                                                onclick="if (confirm('Hapus type ini?')) { this.closest('form').nextElementSibling.submit(); }">
                                                            Hapus
                                                        </button>

                                                        <div class="flex gap-2">
                                                            <button type="button"
                                                                    class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50"
                                                                    @click="open = false">
                                                                Batal
                                                            </button>
                                                            <button type="submit"
                                                                    class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                                                                Simpan
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>

                                                {{-- Delete type form (hidden, triggered by button above) --}}
                                                <form action="{{ route('greeting-types.destroy', $type) }}"
                                                      method="POST" class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-3 py-4 text-center text-slate-500">
                                    Belum ada greeting type.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right: Greetings List --}}
        <div class="lg:col-span-2">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-slate-800">Greetings</h2>

                {{-- Add Greeting modal trigger --}}
                <div x-data="{ open: false }" class="relative">
                    <button
                        type="button"
                        class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 text-white hover:bg-emerald-700"
                        @click="open = true"
                    >
                        + Greeting
                    </button>

                    {{-- Add Greeting Modal --}}
                    <div
                        x-show="open"
                        x-cloak
                        class="fixed inset-0 z-40 flex items-center justify-center"
                    >
                        <div class="fixed inset-0 bg-slate-900/60" @click="open = false"></div>

                        <div class="relative z-50 w-full max-w-lg mx-4 rounded-2xl bg-white shadow-xl border border-slate-200">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                                <h3 class="text-sm font-semibold text-slate-800">Tambah Greeting</h3>
                                <button type="button"
                                        class="h-7 w-7 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                        @click="open = false">
                                    ✕
                                </button>
                            </div>

                            <form action="{{ route('greetings.store') }}"
                                  method="POST"
                                  class="px-4 py-4 space-y-4">
                                @csrf

                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">
                                        Teks Greeting
                                    </label>
                                    <textarea name="name" rows="3"
                                              class="w-full rounded-lg border-slate-200 text-sm"
                                              required>{{ old('name') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">
                                        Type
                                    </label>
                                    <select name="id_type" class="w-full rounded-lg border-slate-200 text-sm" required>
                                        <option value="">-- pilih type --</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                                    <button type="button"
                                            class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50"
                                            @click="open = false">
                                        Batal
                                    </button>
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                                        Simpan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Teks Greeting</th>
                            <th class="px-4 py-2 text-left">Type</th>
                            <th class="px-4 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($greetings as $greeting)
                            <tr class="border-t border-slate-100 align-top">
                                <td class="px-4 py-2 text-sm text-slate-800">
                                    {{ $greeting->name }}
                                </td>
                                <td class="px-4 py-2 text-xs text-slate-600">
                                    {{ $greeting->type->name ?? '-' }}
                                </td>
                                <td class="px-4 py-2 text-right text-xs">
                                    <div x-data="{ open: false }" class="inline-block">
                                        <button
                                            type="button"
                                            class="px-2 py-1 rounded border border-slate-200 hover:bg-slate-50"
                                            @click="open = true"
                                        >
                                            Edit
                                        </button>

                                        {{-- Edit Greeting Modal --}}
                                        <div
                                            x-show="open"
                                            x-cloak
                                            class="fixed inset-0 z-40 flex items-center justify-center"
                                        >
                                            <div class="fixed inset-0 bg-slate-900/60" @click="open = false"></div>

                                            <div class="relative z-50 w-full max-w-lg mx-4 rounded-2xl bg-white shadow-xl border border-slate-200">
                                                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                                                    <h3 class="text-sm font-semibold text-slate-800">Edit Greeting</h3>
                                                    <button type="button"
                                                            class="h-7 w-7 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                                            @click="open = false">
                                                        ✕
                                                    </button>
                                                </div>

                                                <form action="{{ route('greetings.update', $greeting) }}"
                                                      method="POST"
                                                      class="px-4 py-4 space-y-4">
                                                    @csrf
                                                    @method('POST')

                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-600 mb-1">
                                                            Teks Greeting
                                                        </label>
                                                        <textarea name="name" rows="3"
                                                                  class="w-full rounded-lg border-slate-200 text-sm"
                                                                  required>{{ $greeting->name }}</textarea>
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-600 mb-1">
                                                            Type
                                                        </label>
                                                        <select name="id_type" class="w-full rounded-lg border-slate-200 text-sm" required>
                                                            <option value="">-- pilih type --</option>
                                                            @foreach($types as $type)
                                                                <option value="{{ $type->id }}"
                                                                    @selected($greeting->id_type == $type->id)>
                                                                    {{ $type->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                                                        <button type="button"
                                                                class="px-3 py-1.5 text-[11px] rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50"
                                                                onclick="if (confirm('Hapus greeting ini?')) { this.closest('form').nextElementSibling.submit(); }">
                                                            Hapus
                                                        </button>

                                                        <div class="flex gap-2">
                                                            <button type="button"
                                                                    class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50"
                                                                    @click="open = false">
                                                                Batal
                                                            </button>
                                                            <button type="submit"
                                                                    class="px-3 py-1.5 text-xs rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                                                                Simpan
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>

                                                {{-- Delete greeting form (hidden) --}}
                                                <form action="{{ route('greetings.destroy', $greeting) }}"
                                                      method="POST" class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">
                                    Belum ada greeting.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $greetings->links() }}
            </div>
        </div>
    </div>
</div>
@endsection