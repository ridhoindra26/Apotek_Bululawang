@extends('layout.layout')

@section('title', 'Employee Payroll')
@section('page_title', 'Employee Payroll Settings')

@push('scripts')
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

<style>[x-cloak]{display:none!important}</style>

@section('content')
<div class="space-y-4">

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

    <div class="flex items-center justify-between">
        <div class="text-sm text-slate-600">
            Manage base salary and bank transfer data stored in <code>employees</code> table.
        </div>
        <a href="{{ route('payroll.periods.index') }}"
           class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700">
            Back to Periods
        </a>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Employee</th>
                        <th class="px-4 py-3 text-left font-medium">Branch</th>
                        <th class="px-4 py-3 text-right font-medium">Base Salary</th>
                        <th class="px-4 py-3 text-left font-medium">Rekening</th>
                        <th class="px-4 py-3 text-left font-medium">Email</th>
                        <th class="px-4 py-3 text-left font-medium">Active</th>
                        <th class="px-4 py-3 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($employees as $e)
                        <tr x-data="{ open:false }">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $e->name }}</div>
                                <div class="text-xs text-slate-500">Role: {{ $e->roles->name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $e->branches->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format((int)($e->base_salary ?? 0)) }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $e->bank_account_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $e->payroll_email ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs {{ $e->payroll_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $e->payroll_active ? 'YES' : 'NO' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button @click="open=true"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700">
                                    Edit
                                </button>

                                <div x-cloak x-show="open" class="fixed inset-0 z-50">
                                    <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
                                    <div class="absolute inset-0 flex items-center justify-center p-4">
                                        <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl border border-slate-200 overflow-hidden">
                                            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                                                <div class="font-semibold text-slate-900">Edit Payroll — {{ $e->name }}</div>
                                                <button class="text-slate-500 hover:text-slate-700" @click="open=false">✕</button>
                                            </div>

                                            <form method="POST" action="{{ route('payroll.employees.update', $e->id) }}" class="p-4 space-y-3">
                                                @csrf
                                                @method('PUT')

                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-600 mb-1">Base Salary</label>
                                                        <input name="base_salary" type="number" min="0"
                                                               value="{{ (int)($e->base_salary ?? 0) }}"
                                                               class="w-full rounded-lg border-slate-200 text-sm text-right" required>
                                                    </div>

                                                    <div class="flex items-end gap-2">
                                                        <input type="checkbox" name="payroll_active" value="1" class="rounded border-slate-300"
                                                               @checked($e->payroll_active)>
                                                        <label class="text-sm text-slate-700">Payroll Active</label>
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-600 mb-1">Bank Name</label>
                                                        <input name="bank_name" value="{{ $e->bank_name }}"
                                                               class="w-full rounded-lg border-slate-200 text-sm">
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-600 mb-1">Rekening</label>
                                                        <input name="bank_account_number" value="{{ $e->bank_account_number }}"
                                                               class="w-full rounded-lg border-slate-200 text-sm">
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-600 mb-1">Account Holder</label>
                                                        <input name="bank_account_holder" value="{{ $e->bank_account_holder }}"
                                                               class="w-full rounded-lg border-slate-200 text-sm">
                                                    </div>

                                                    <div>
                                                        <label class="block text-xs font-medium text-slate-600 mb-1">Payroll Email</label>
                                                        <input name="payroll_email" value="{{ $e->payroll_email }}"
                                                               class="w-full rounded-lg border-slate-200 text-sm">
                                                    </div>
                                                </div>

                                                <div class="flex justify-end gap-2 pt-2">
                                                    <button type="button"
                                                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700"
                                                            @click="open=false">
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
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $employees->links() }}
        </div>
    </div>

</div>
@endsection