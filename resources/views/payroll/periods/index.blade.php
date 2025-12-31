@extends('layout.layout')

@section('title', 'Payroll Periods')
@section('page_title', 'Payroll Periods')

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
        <div class="text-sm text-slate-600">
            Manage payroll periods, generate items, lock, and export CSV.
        </div>

        <div class="flex gap-2">
            <a href="{{ route('payroll.templates.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700">
                Transfer Templates
            </a>

            <a href="{{ route('payroll.employees.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700">
                Employee Payroll
            </a>

            <button @click="openCreate=true"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                Create Period
            </button>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">Code</th>
                    <th class="px-4 py-3 text-left font-medium">Name</th>
                    <th class="px-4 py-3 text-left font-medium">Range</th>
                    <th class="px-4 py-3 text-left font-medium">Status</th>
                    <th class="px-4 py-3 text-right font-medium">Items</th>
                    <th class="px-4 py-3 text-right font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($periods as $p)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $p->code }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $p->name }}</td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ \Illuminate\Support\Carbon::parse($p->date_from)->format('d M Y') }}
                            –
                            {{ \Illuminate\Support\Carbon::parse($p->date_to)->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs
                                @if($p->status==='draft') bg-slate-100 text-slate-700
                                @elseif($p->status==='locked') bg-amber-100 text-amber-800
                                @elseif($p->status==='paid') bg-emerald-100 text-emerald-800
                                @else bg-rose-100 text-rose-800 @endif
                            ">
                                {{ strtoupper($p->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">{{ $p->items_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('payroll.periods.show', $p->id) }}"
                               class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700">
                                Open
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                            No payroll periods yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t border-slate-200">
            {{ $periods->links() }}
        </div>
    </div>

    {{-- Create Period Modal --}}
    <div x-cloak x-show="openCreate" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/40" @click="openCreate=false"></div>

        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-lg rounded-xl bg-white shadow-xl border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <div class="font-semibold text-slate-900">Create Payroll Period</div>
                    <button class="text-slate-500 hover:text-slate-700" @click="openCreate=false">✕</button>
                </div>

                <form method="POST" action="{{ route('payroll.periods.store') }}" class="p-4 space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Code</label>
                        <input name="code" class="w-full rounded-lg border-slate-200 text-sm" placeholder="e.g. 2025-10" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Name</label>
                        <input name="name" class="w-full rounded-lg border-slate-200 text-sm" placeholder="e.g. Gaji Oktober 2025" required>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Date From</label>
                            <input type="date" name="date_from" class="w-full rounded-lg border-slate-200 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Date To</label>
                            <input type="date" name="date_to" class="w-full rounded-lg border-slate-200 text-sm" required>
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
