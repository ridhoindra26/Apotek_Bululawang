@extends('layout.layout')

@section('title', 'Payroll Invoice')
@section('page_title', 'Payroll Invoice')

@section('content')
<div class="max-w-4xl space-y-4">

    <div class="rounded-xl border border-slate-200 bg-white p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-sm text-slate-500">Slip Gaji</div>
                <div class="text-xl font-semibold text-slate-900">
                    {{ $item->period->name ?? $item->period->code }}
                </div>
                <div class="text-sm text-slate-600">
                    Terbayar pada : {{ $item->period->paid_at }}
                    <span class="ml-2 inline-flex rounded-full px-2 py-0.5 text-xs
                        @if($item->period->status==='draft') bg-slate-100 text-slate-700
                        @elseif($item->period->status==='locked') bg-amber-100 text-amber-800
                        @elseif($item->period->status==='paid') bg-emerald-100 text-emerald-800
                        @else bg-rose-100 text-rose-800 @endif
                    ">
                        {{ strtoupper($item->period->status) }}
                    </span>
                </div>
            </div>

            <div class="flex gap-2">
                <button onclick="window.print()"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                    Cetak
                </button>
            </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg bg-slate-50 p-3">
                <div class="text-xs text-slate-500">Nama</div>
                <div class="font-medium text-slate-900">{{ $item->employee->name ?? ('Employee #'.$item->id_employee) }}</div>
                <div class="text-xs text-slate-500 mt-1">No. Telepon: {{ $item->employee->phone }}</div>
            </div>

            <div class="rounded-lg bg-slate-50 p-3">
                <div class="text-xs text-slate-500">No. Rekening</div>
                <div class="font-medium text-slate-900">{{ $item->rekening_snapshot }}</div>
                @if($item->email_snapshot)
                    <div class="text-xs text-slate-500 mt-1">Email : {{ $item->email_snapshot }}</div>
                @endif
            </div>
        </div>

        <div class="mt-6 space-y-4">

            <div class="flex justify-between text-sm">
                <span class="text-slate-600">Gaji Pokok</span>
                <span class="font-medium text-slate-900">{{ number_format($item->base_salary_snapshot) }}</span>
            </div>

            <div class="border-t border-slate-200 pt-3">
                <div class="text-sm font-semibold text-slate-900 mb-2">Tunjangan</div>
                @php($allowances = $item->lines->where('type', 'allowance'))
                @if($allowances->count() === 0)
                    <div class="text-sm text-slate-500">Tidak ada tunjangan.</div>
                @else
                    <div class="space-y-2">
                        @foreach($allowances as $l)
                            <div class="flex justify-between text-sm">
                                <div class="min-w-0">
                                    <div class="text-slate-800">{{ $l->name }}</div>
                                    @if($l->description)
                                        <div class="text-xs text-slate-500 break-words">{{ $l->description }}</div>
                                    @endif
                                </div>
                                <div class="font-medium text-slate-900">{{ number_format($l->amount) }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-between text-sm mt-2">
                    <span class="text-slate-600">Total Tunjangan</span>
                    <span class="font-medium text-slate-900">{{ number_format($item->allowance_total) }}</span>
                </div>
            </div>

            <div class="border-t border-slate-200 pt-3">
                <div class="text-sm font-semibold text-slate-900 mb-2">Potongan</div>
                @php($deductions = $item->lines->where('type', 'deduction'))
                @if($deductions->count() === 0)
                    <div class="text-sm text-slate-500">Tidak ada potongan.</div>
                @else
                    <div class="space-y-2">
                        @foreach($deductions as $l)
                            <div class="flex justify-between text-sm">
                                <div class="min-w-0">
                                    <div class="text-slate-800">{{ $l->name }}</div>
                                    @if($l->description)
                                        <div class="text-xs text-slate-500 break-words">{{ $l->description }}</div>
                                    @endif
                                </div>
                                <div class="font-medium text-slate-900">{{ number_format($l->amount) }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-between text-sm mt-2">
                    <span class="text-slate-600">Total Potongan</span>
                    <span class="font-medium text-slate-900">{{ number_format($item->deduction_total) }}</span>
                </div>
            </div>

            <div class="border-t border-slate-200 pt-4 flex justify-between">
                <span class="text-base font-semibold text-slate-900">Gaji Bersih</span>
                <span class="text-base font-semibold text-slate-900">{{ number_format($item->net_pay) }}</span>
            </div>

            @if($item->notes)
                <div class="border-t border-slate-200 pt-3">
                    <div class="text-sm font-semibold text-slate-900 mb-1">Keterangan</div>
                    <div class="text-sm text-slate-700 whitespace-pre-line">{{ $item->notes }}</div>
                </div>
            @endif

        </div>
    </div>

</div>
@endsection
