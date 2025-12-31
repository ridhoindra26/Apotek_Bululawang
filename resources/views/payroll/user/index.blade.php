@extends('layout.layout')

@section('title', 'My Payroll')
@section('page_title', 'My Payroll')

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

    <div class="rounded-xl border border-slate-200 bg-white overflow-hidden">
        <div class="p-4 border-b border-slate-200">
            <div class="text-sm font-semibold text-slate-900">Payroll Periode</div>
            <div class="text-xs text-slate-500">Slip tersedia setelah status periode <span class="font-medium">PAID</span>.</div>
        </div>

        {{-- Mobile cards --}}
        <div class="p-4 space-y-3 md:hidden">
            @forelse($periods as $p)
                <a href="{{ route('payroll.user.slip', $p->id) }}"
                   class="block rounded-xl border border-slate-200 bg-white p-4 hover:bg-slate-50">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-slate-900 truncate">
                                {{ $p->name }}
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                {{ $p->code }}
                            </div>
                        </div>

                        <span class="shrink-0 inline-flex rounded-full px-2 py-0.5 text-xs
                            @if($p->status==='paid') bg-emerald-100 text-emerald-800
                            @elseif($p->status==='locked') bg-amber-100 text-amber-800
                            @else bg-slate-100 text-slate-700 @endif
                        ">
                            {{ strtoupper($p->status) }}
                        </span>
                    </div>

                    <div class="mt-3 rounded-lg bg-slate-50 p-3">
                        <div class="text-xs text-slate-500">Rentang Tanggal</div>
                        <div class="text-sm text-slate-700">
                            {{ \Illuminate\Support\Carbon::parse($p->date_from)->format('d M Y') }}
                            –
                            {{ \Illuminate\Support\Carbon::parse($p->date_to)->format('d M Y') }}
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between">
                        <div class="text-xs text-slate-500">Ketuk untuk membuka</div>
                        <span class="text-sm font-medium text-slate-900">Buka</span>
                    </div>
                </a>
            @empty
                <div class="py-8 text-center text-slate-500 text-sm">
                    Belum ada periode payroll yang tersedia.
                </div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Periode</th>
                        <th class="px-4 py-3 text-left font-medium">Rentang Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($periods as $p)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $p->name }}</div>
                                <div class="text-xs text-slate-500">{{ $p->code }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                {{ \Illuminate\Support\Carbon::parse($p->date_from)->format('d M Y') }}
                                –
                                {{ \Illuminate\Support\Carbon::parse($p->date_to)->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs
                                    @if($p->status==='paid') bg-emerald-100 text-emerald-800
                                    @elseif($p->status==='locked') bg-amber-100 text-amber-800
                                    @else bg-slate-100 text-slate-700 @endif
                                ">
                                    {{ strtoupper($p->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('payroll.user.show', $p->id) }}"
                                   class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                No payroll periods available yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $periods->links() }}
        </div>
    </div>

</div>
@endsection