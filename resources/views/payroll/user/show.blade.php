@extends('layout.layout')

@section('title', 'Payroll Summary')
@section('page_title', 'Payroll Summary')

@section('content')
<div class="max-w-3xl space-y-4">

    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-sm text-slate-500">Period</div>
                <div class="text-xl font-semibold text-slate-900">{{ $period->name }}</div>
                <div class="text-sm text-slate-600">
                    {{ $period->date_from->format('d M Y') }} – {{ $period->date_to->format('d M Y') }}
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('payroll.user.index') }}"
                   class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700">
                    Back
                </a>

                <a href="{{ route('payroll.user.slip', $period->id) }}"
                   class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                    View Slip
                </a>
            </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg bg-slate-50 p-3">
                <div class="text-xs text-slate-500">Rekening</div>
                <div class="font-medium text-slate-900">{{ $item->rekening_snapshot }}</div>
            </div>

            <div class="rounded-lg bg-slate-50 p-3">
                <div class="text-xs text-slate-500">Net Pay</div>
                <div class="text-lg font-semibold text-slate-900">{{ number_format($item->net_pay) }}</div>
            </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 p-3">
                <div class="text-xs text-slate-500">Base</div>
                <div class="font-medium text-slate-900">{{ number_format($item->base_salary_snapshot) }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 p-3">
                <div class="text-xs text-slate-500">Allowance</div>
                <div class="font-medium text-slate-900">{{ number_format($item->allowance_total) }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 p-3">
                <div class="text-xs text-slate-500">Deduction</div>
                <div class="font-medium text-slate-900">{{ number_format($item->deduction_total) }}</div>
            </div>
        </div>

    </div>

</div>
@endsection