@extends('layout.layout')

@section('title', 'Payroll Period')
@section('page_title', 'Payroll Period Detail')

@push('scripts')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

<style>[x-cloak]{display:none!important}</style>

@section('content')
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

    {{-- Header --}}
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-sm text-slate-500">Code</div>
                <div class="text-lg font-semibold text-slate-900">{{ $period->code }} — {{ $period->name }}</div>
                <div class="text-sm text-slate-600">
                    {{ $period->date_from->format('d M Y') }} – {{ $period->date_to->format('d M Y') }}
                    <span class="ml-2 inline-flex rounded-full px-2 py-0.5 text-xs
                        @if($period->status==='draft') bg-slate-100 text-slate-700
                        @elseif($period->status==='locked') bg-amber-100 text-amber-800
                        @elseif($period->status==='paid') bg-emerald-100 text-emerald-800
                        @else bg-rose-100 text-rose-800 @endif
                    ">
                        {{ strtoupper($period->status) }}
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @if($period->status === 'draft')
                    <form method="POST" action="{{ route('payroll.periods.generate-items', $period->id) }}">
                        @csrf
                        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                            Generate Items
                        </button>
                    </form>

                    <form method="POST" action="{{ route('payroll.periods.lock', $period->id) }}"
                          onsubmit="return confirm('Lock this payroll period? This will prevent edits.')">
                        @csrf
                        <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700">
                            Lock Period
                        </button>
                    </form>
                @endif
                @if(in_array($period->status, ['locked','paid']))
                    <form method="POST" action="{{ route('payroll.periods.export-csv', $period->id) }}" class="flex items-center gap-2">
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
                @else
                    <div class="text-xs text-slate-500">Lock period to enable export.</div>
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
        </div>
    </div>

    {{-- Items table --}}
    <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
        <div class="p-4 border-b border-slate-200">
            <div class="text-sm font-semibold text-slate-900">Payroll Items</div>
            <div class="text-xs text-slate-500">Adjust allowances/deductions per employee.</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Employee</th>
                        <th class="px-4 py-3 text-left font-medium">Rekening</th>
                        <th class="px-4 py-3 text-right font-medium">Base</th>
                        <th class="px-4 py-3 text-right font-medium">Allowance</th>
                        <th class="px-4 py-3 text-right font-medium">Deduction</th>
                        <th class="px-4 py-3 text-right font-medium">Net Pay</th>
                        <th class="px-4 py-3 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                @forelse($period->items as $item)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $item->employee->name ?? ('Employee #'.$item->id_employee) }}</div>
                            <div class="text-xs text-slate-500">ID: {{ $item->id_employee }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $item->rekening_snapshot }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item->base_salary_snapshot) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item->allowance_total) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item->deduction_total) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format($item->net_pay) }}</td>
                        <td class="px-4 py-3 text-right ">
                            <a href="{{ route('payroll.items.invoice', $item->id) }}" target="_blank"
                                class="rounded border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700">
                                    View Invoice
                            </a>

                            <div x-data="{ open:false }" class="inline-block">
                                <button @click="open=true"
                                        class="rounded border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700">
                                    Adjust
                                </button>

                                {{-- Modal --}}
                                <div x-cloak x-show="open" class="fixed inset-0 z-50">
                                    <div class="absolute inset-0 bg-black/40" @click="open=false"></div>

                                    <div class="absolute inset-0 flex items-center justify-center p-4">
                                        <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl border border-slate-200 overflow-hidden">
                                            <div class="flex items-center justify-between p-4 border-b border-slate-200">
                                                <div>
                                                    <div class="text-sm text-slate-500">Adjust Lines</div>
                                                    <div class="font-semibold text-slate-900">
                                                        {{ $item->employee->name ?? ('Employee #'.$item->id_employee) }}
                                                    </div>
                                                </div>
                                                <button class="text-slate-500 hover:text-slate-700" @click="open=false">✕</button>
                                            </div>

                                            <div class="p-4 space-y-4">

                                                {{-- Existing lines --}}
                                                <div class="space-y-2">
                                                    <div class="text-sm font-semibold text-slate-900">Current Lines</div>

                                                    @if($item->lines->count() === 0)
                                                        <div class="text-sm text-slate-500">No lines yet.</div>
                                                    @else
                                                        <div class="space-y-2">
                                                            @foreach($item->lines as $line)
                                                                <div class="rounded-lg border border-slate-200 p-3">
                                                                    <div class="flex items-start justify-between gap-3">
                                                                        <div class="min-w-0">
                                                                            <div class="text-xs text-slate-500">
                                                                                {{ strtoupper($line->type) }} • {{ $line->name }}
                                                                            </div>
                                                                            @if($line->description)
                                                                                <div class="text-sm text-slate-700 break-words">{{ $line->description }}</div>
                                                                            @endif
                                                                        </div>
                                                                        <div class="text-right">
                                                                            <div class="font-semibold text-slate-900">{{ number_format($line->amount) }}</div>
                                                                        </div>
                                                                    </div>

                                                                    @if($period->status === 'draft')
                                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                                            {{-- Update (simple inline form) --}}
                                                                            <form method="POST" action="{{ route('payroll.item-lines.update', $line->id) }}" class="flex flex-wrap gap-2 items-center">
                                                                                @csrf
                                                                                @method('PUT')

                                                                                <select name="type" class="rounded-lg border-slate-200 text-sm">
                                                                                    <option value="allowance" @selected($line->type==='allowance')>Allowance</option>
                                                                                    <option value="deduction" @selected($line->type==='deduction')>Deduction</option>
                                                                                </select>

                                                                                <input name="name" value="{{ $line->name }}" class="rounded-lg border-slate-200 text-sm" placeholder="Name" required>
                                                                                <input name="amount" type="number" min="0" value="{{ $line->amount }}" class="rounded-lg border-slate-200 text-sm w-36 text-right" required>
                                                                                <input name="description" value="{{ $line->description }}" class="rounded-lg border-slate-200 text-sm flex-1" placeholder="Description (optional)">

                                                                                <button class="rounded-lg bg-slate-900 px-3 py-1.5 text-sm font-medium text-white">
                                                                                    Save
                                                                                </button>
                                                                            </form>

                                                                            {{-- Delete --}}
                                                                            <form method="POST" action="{{ route('payroll.item-lines.destroy', $line->id) }}"
                                                                                  onsubmit="return confirm('Delete this line?')">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-sm font-medium text-rose-700">
                                                                                    Delete
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Add new line --}}
                                                @if($period->status === 'draft')
                                                    <div class="rounded-lg border border-slate-200 p-3">
                                                        <div class="text-sm font-semibold text-slate-900 mb-2">Add New Line</div>

                                                        <form method="POST" action="{{ route('payroll.item-lines.store') }}" class="grid gap-2 sm:grid-cols-2">
                                                            @csrf
                                                            <input type="hidden" name="payroll_item_id" value="{{ $item->id }}">

                                                            <div>
                                                                <label class="block text-xs font-medium text-slate-600 mb-1">Type</label>
                                                                <select name="type" class="w-full rounded-lg border-slate-200 text-sm" required>
                                                                    <option value="allowance">Allowance</option>
                                                                    <option value="deduction">Deduction</option>
                                                                </select>
                                                            </div>

                                                            <div>
                                                                <label class="block text-xs font-medium text-slate-600 mb-1">Amount</label>
                                                                <input name="amount" type="number" min="0"
                                                                       class="w-full rounded-lg border-slate-200 text-sm text-right"
                                                                       placeholder="0" required>
                                                            </div>

                                                            <div class="sm:col-span-2">
                                                                <label class="block text-xs font-medium text-slate-600 mb-1">Name</label>
                                                                <input name="name"
                                                                       class="w-full rounded-lg border-slate-200 text-sm"
                                                                       placeholder="e.g. Overtime, Bonus, BPJS, Penalty"
                                                                       required>
                                                            </div>

                                                            <div class="sm:col-span-2">
                                                                <label class="block text-xs font-medium text-slate-600 mb-1">Description</label>
                                                                <input name="description"
                                                                       class="w-full rounded-lg border-slate-200 text-sm"
                                                                       placeholder="Optional details">
                                                            </div>

                                                            <div class="sm:col-span-2 flex justify-end">
                                                                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                                                                    Add Line
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                @else
                                                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                                                        Period is locked; lines cannot be modified.
                                                    </div>
                                                @endif

                                                {{-- Totals --}}
                                                <div class="rounded-lg bg-slate-50 p-3 text-sm">
                                                    <div class="flex justify-between">
                                                        <span class="text-slate-600">Base</span>
                                                        <span class="font-medium text-slate-900">{{ number_format($item->base_salary_snapshot) }}</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-slate-600">Allowance Total</span>
                                                        <span class="font-medium text-slate-900">{{ number_format($item->allowance_total) }}</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span class="text-slate-600">Deduction Total</span>
                                                        <span class="font-medium text-slate-900">{{ number_format($item->deduction_total) }}</span>
                                                    </div>
                                                    <div class="flex justify-between border-t border-slate-200 mt-2 pt-2">
                                                        <span class="text-slate-600">Net Pay</span>
                                                        <span class="font-semibold text-slate-900">{{ number_format($item->net_pay) }}</span>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="p-4 border-t border-slate-200 flex justify-end">
                                                <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700"
                                                        @click="open=false">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- End modal --}}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-500">
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