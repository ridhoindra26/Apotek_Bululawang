@extends('layout.layout')

@section('title', 'Payroll Period')
@section('page_title', 'Payroll Period Detail')

<style>
    [x-cloak] { display: none !important; }
</style>

@section('content')
{{-- 1) Define helper BEFORE Alpine loads (so x-data can find it) --}}
<script>
window.payrollPeriodPage = function (employees) {
    return {
        genOpen: false,
        q: '',
        employees: employees || [],
        selected: [],

        get filtered() {
            const q = (this.q || '').toLowerCase().trim();
            if (!q) return this.employees;
            return this.employees.filter(e => (e.name || '').toLowerCase().includes(q));
        },

        get selectedCount() {
            return this.selected.length;
        },

        selectAllFiltered() {
            const ids = this.filtered.map(e => String(e.id));
            const current = new Set(this.selected.map(String));
            ids.forEach(id => current.add(id));
            this.selected = Array.from(current);
        },

        clearAll() {
            this.selected = [];
        },
    }
};
</script>

{{-- 2) Load Alpine directly here (no @push). If already loaded in layout, it’s fine. --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<div
    x-data="payrollPeriodPage({{ $employees->toJson() }})"
    class="space-y-4"
>

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

    {{-- Header --}}
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <div class="text-sm text-slate-500">Code</div>
                <div class="text-lg font-semibold text-slate-900">
                    {{ $period->code }} — {{ $period->name }}
                </div>

                <div class="mt-1 text-sm text-slate-600 flex flex-wrap items-center gap-2">
                    <span>
                        {{ $period->date_from->format('d M Y') }} – {{ $period->date_to->format('d M Y') }}
                    </span>

                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs
                        @if($period->status==='draft') bg-slate-100 text-slate-700
                        @elseif($period->status==='locked') bg-amber-100 text-amber-800
                        @elseif($period->status==='paid') bg-emerald-100 text-emerald-800
                        @else bg-rose-100 text-rose-800 @endif
                    ">
                        {{ strtoupper($period->status) }}
                    </span>

                    @if($period->status === 'paid')
                        <span class="text-xs text-slate-500">
                            Terbayar pada:
                            <span class="font-medium text-slate-900">
                                {{ $period->paid_at ? $period->paid_at : '-' }}
                            </span>
                        </span>

                        @if($period->paid_note)
                            <span class="text-xs text-slate-500">
                                Note: <span class="text-slate-700">{{ $period->paid_note }}</span>
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col gap-2 lg:items-end">
                <div class="flex flex-wrap gap-2">

                    @if($period->status === 'draft')
                        <button
                            type="button"
                            @click="genOpen = true"
                            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                        >
                            Generate Items
                        </button>

                        <form method="POST" action="{{ route('payroll.periods.lock', $period->id) }}"
                              onsubmit="return confirm('Lock this payroll period? This will prevent edits.')">
                            @csrf
                            <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700">
                                Lock Period
                            </button>
                        </form>
                    @endif

                    @if(in_array($period->status, ['locked','paid']))
                        <form method="POST" action="{{ route('payroll.periods.export-csv', $period->id) }}"
                              class="flex flex-wrap items-center gap-2">
                            @csrf
                            <select name="template_id" class="rounded-lg border-slate-200 text-sm" required>
                                <option value="">Select template</option>
                                @foreach($templates as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                                Export CSV
                            </button>
                        </form>
                    @endif

                    @if($period->status === 'locked')
                        <form method="POST" action="{{ route('payroll.periods.mark-paid', $period->id) }}"
                              class="flex flex-wrap items-center gap-2"
                              onsubmit="return confirm('Mark this payroll period as PAID?')">
                            @csrf
                            <input type="text" name="paid_note"
                                   class="rounded-lg border-slate-200 text-sm"
                                   placeholder="Note (optional)">
                            <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white">
                                Mark Paid
                            </button>
                        </form>
                    @endif

                </div>

                <div class="text-xs text-slate-500">
                    Items: <span class="font-medium text-slate-900">{{ $period->items->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Generate Items Modal (separate file) --}}
    @include('payroll.periods.partials.generate-items-modal', ['period' => $period])

    {{-- Items (mobile cards) --}}
    <div class="md:hidden space-y-3">
        @forelse($period->items as $item)
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-slate-900 truncate">
                            {{ $item->employee->name ?? ('Employee #'.$item->id_employee) }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Rekening: {{ $item->rekening_snapshot ?: '-' }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500">Net Pay</div>
                        <div class="text-sm font-semibold text-slate-900">{{ number_format($item->net_pay) }}</div>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('payroll.items.invoice', $item->id) }}" target="_blank"
                       class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700">
                        View Invoice
                    </a>

                    <div x-data="{ open:false }">
                        <button @click="open=true"
                                class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white">
                            Adjust
                        </button>

                        @include('payroll.periods.partials.adjust-modal', ['period' => $period, 'item' => $item])
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-6 text-center text-slate-500">
                No payroll items yet.
            </div>
        @endforelse
    </div>

    {{-- Items (desktop table) --}}
    <div class="hidden md:block rounded-xl border border-slate-200 bg-white overflow-hidden">
        <div class="p-4 border-b border-slate-200">
            <div class="text-sm font-semibold text-slate-900">Payroll Items</div>
            <div class="text-xs text-slate-500">Adjust allowances/deductions per employee (only in DRAFT).</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Employee</th>
                        <th class="px-4 py-3 text-left font-medium">Rekening</th>
                        <th class="px-4 py-3 text-right font-medium">Net Pay</th>
                        <th class="px-4 py-3 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($period->items as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">
                                    {{ $item->employee->name ?? ('Employee #'.$item->id_employee) }}
                                </div>
                                <div class="text-xs text-slate-500">ID: {{ $item->id_employee }}</div>
                            </td>

                            <td class="px-4 py-3 text-slate-700">
                                {{ $item->rekening_snapshot ?: '-' }}
                            </td>

                            <td class="px-4 py-3 text-right font-semibold text-slate-900">
                                {{ number_format($item->net_pay) }}
                            </td>

                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end flex-wrap gap-2">
                                    <a href="{{ route('payroll.items.invoice', $item->id) }}" target="_blank"
                                       class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700">
                                        View Invoice
                                    </a>

                                    <div x-data="{ open:false }" class="inline-block">
                                        <button @click="open=true"
                                                class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white">
                                            Adjust
                                        </button>

                                        @include('payroll.periods.partials.adjust-modal', ['period' => $period, 'item' => $item])
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                                No payroll items yet. Click “Generate Items”.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection