@extends('layout.layout')

@section('title', 'Transfer Templates')
@section('page_title', 'Payroll Transfer Templates')

@push('scripts')
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

<style>[x-cloak]{display:none!important}</style>

@section('content')
<div class="space-y-4" x-data="{ openCreate:false }">

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

    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <div class="text-sm font-semibold text-slate-900">Templates</div>
            <div class="text-xs text-slate-500">
                Allowed fields: {{ implode(', ', $allowedFields) }}
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('payroll.periods.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700">
                Back to Periods
            </a>

            <button @click="openCreate=true"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                Create Template
            </button>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">Name</th>
                    <th class="px-4 py-3 text-left font-medium">Delimiter</th>
                    <th class="px-4 py-3 text-left font-medium">Encoding</th>
                    <th class="px-4 py-3 text-left font-medium">Header</th>
                    <th class="px-4 py-3 text-right font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($templates as $t)
                    <tr x-data="{ openEdit:false }">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $t->name }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $t->delimiter }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $t->encoding }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $t->include_header ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3 text-right">
                            <button @click="openEdit=true"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700">
                                Edit
                            </button>

                            <form method="POST" action="{{ route('payroll.templates.destroy', $t->id) }}"
                                  class="inline"
                                  onsubmit="return confirm('Delete this template?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-sm font-medium text-rose-700">
                                    Delete
                                </button>
                            </form>

                            {{-- Edit modal --}}
                            <div x-cloak x-show="openEdit" class="fixed inset-0 z-50">
                                <div class="absolute inset-0 bg-black/40" @click="openEdit=false"></div>
                                <div class="absolute inset-0 flex items-center justify-center p-4">
                                    <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl border border-slate-200 overflow-hidden">
                                        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                                            <div class="font-semibold text-slate-900">Edit Template</div>
                                            <button class="text-slate-500 hover:text-slate-700" @click="openEdit=false">✕</button>
                                        </div>

                                        <form method="POST" action="{{ route('payroll.templates.update', $t->id) }}" class="p-4 space-y-3">
                                            @csrf
                                            @method('PUT')

                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-600 mb-1">Name</label>
                                                    <input name="name" value="{{ $t->name }}" class="w-full rounded-lg border-slate-200 text-sm" required>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-600 mb-1">Delimiter</label>
                                                    <input name="delimiter" value="{{ $t->delimiter }}" class="w-full rounded-lg border-slate-200 text-sm" required>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-slate-600 mb-1">Encoding</label>
                                                    <select name="encoding" class="w-full rounded-lg border-slate-200 text-sm" required>
                                                        <option value="utf8" @selected($t->encoding==='utf8')>utf8</option>
                                                        <option value="utf8_bom" @selected($t->encoding==='utf8_bom')>utf8_bom</option>
                                                    </select>
                                                </div>
                                                <div class="flex items-end gap-2">
                                                    <input type="checkbox" name="include_header" value="1" class="rounded border-slate-300"
                                                           @checked($t->include_header)>
                                                    <label class="text-sm text-slate-700">Include header row</label>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-slate-600 mb-1">columns_json (JSON array)</label>
                                                <textarea name="columns_json" rows="8"
                                                          class="w-full rounded-lg border-slate-200 font-mono text-xs"
                                                          required>{{ json_encode($t->columns_json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</textarea>
                                                <div class="text-xs text-slate-500 mt-1">
                                                    Format: [{"header":"REKENING","field":"rekening_snapshot"}, ...]
                                                </div>
                                            </div>

                                            <div class="flex justify-end gap-2 pt-2">
                                                <button type="button"
                                                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700"
                                                        @click="openEdit=false">
                                                    Cancel
                                                </button>
                                                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                                                    Save
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                            {{-- end edit modal --}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">No templates yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create modal --}}
    <div x-cloak x-show="openCreate" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/40" @click="openCreate=false"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <div class="font-semibold text-slate-900">Create Template</div>
                    <button class="text-slate-500 hover:text-slate-700" @click="openCreate=false">✕</button>
                </div>

                <form method="POST" action="{{ route('payroll.templates.store') }}" class="p-4 space-y-3">
                    @csrf

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Name</label>
                            <input name="name" class="w-full rounded-lg border-slate-200 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Delimiter</label>
                            <input name="delimiter" value="," class="w-full rounded-lg border-slate-200 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Encoding</label>
                            <select name="encoding" class="w-full rounded-lg border-slate-200 text-sm" required>
                                <option value="utf8" selected>utf8</option>
                                <option value="utf8_bom">utf8_bom</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <input type="checkbox" name="include_header" value="1" class="rounded border-slate-300" checked>
                            <label class="text-sm text-slate-700">Include header row</label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">columns_json (JSON array)</label>
                        <textarea name="columns_json" rows="8"
                                  class="w-full rounded-lg border-slate-200 font-mono text-xs"
                                  required>[{"header":"REKENING","field":"rekening_snapshot"},{"header":" NOMINAL ","field":"net_pay"},{"header":"EMAIL","field":"email_snapshot"}]</textarea>
                        <div class="text-xs text-slate-500 mt-1">
                            Tip: some banks require exact header spacing (e.g. " NOMINAL ").
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button"
                                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700"
                                @click="openCreate=false">
                            Cancel
                        </button>
                        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                            Create
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection